<?php
// Enable exception reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$serverName = "sql211.infinityfree.com";
$userName = "if0_39013410";
$password = "coHnlk9rffE";

try {
    // Establish connection
    $conn = mysqli_connect($serverName, $userName, $password);

    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());  // Custom exception for connection failure
    }

    // Create database if it does not exist
    $createDatabase = "CREATE DATABASE IF NOT EXISTS  if0_39013410_prototype3";
    if (!mysqli_query($conn, $createDatabase)) {
        throw new Exception("Failed to create database: " . mysqli_error($conn));  // Exception for database creation error
    }

    // Select the created database
    if (!mysqli_select_db($conn, 'if0_39013410_prototype3')) {
        throw new Exception("Failed to select database: " . mysqli_error($conn));  // Exception for database selection error
    }

    // Create table if it does not exist
    $createTable = "CREATE TABLE IF NOT EXISTS weather (
        city VARCHAR(100) NOT NULL,
        weather_condition VARCHAR(50),
        temperature FLOAT,
        humidity FLOAT,
        wind FLOAT,
        pressure FLOAT,
        direction FLOAT,
        icon VARCHAR(50),
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";

    if (!mysqli_query($conn, $createTable)) {
        throw new Exception("Failed to create table: " . mysqli_error($conn));  // Exception for CREATE TABLE error
    }

    if (isset($_GET['cityName'])) {
        $cityName = $_GET['cityName'];
    } else {
        $cityName = "cardiff";
    }

    $selectAllData = "SELECT * FROM weather WHERE city = '$cityName' AND timestamp > NOW() - INTERVAL 2 HOUR ORDER BY timestamp DESC LIMIT 1";
    $result = mysqli_query($conn, $selectAllData);

    if (!$result) {
        throw new Exception("Failed to execute SELECT query: " . mysqli_error($conn));  // Exception for SELECT query error
    }

    if (mysqli_num_rows($result) == 0) {
        // If no data found, fetch from API
        $url = "https://api.openweathermap.org/data/2.5/weather?q=$cityName&appid=449d496cd673d2e3eb80b52bac9588ea&units=metric";
        $response = file_get_contents($url);

        // Handle API error
        if ($response === FALSE) {
            throw new Exception("Failed to fetch data from API");  // Exception for API fetch error
        }

        $data = json_decode($response, true);

        $weather_condition = $data['weather'][0]['description'];
        $humidity = $data['main']['humidity'];
        $wind = $data['wind']['speed'];
        $pressure = $data['main']['pressure'];
        $temperature = $data['main']['temp'];
        $direction = $data['wind']['deg'];
        $icon = $data['weather'][0]['icon'];

        // Insert data into the table
        $insertData = "INSERT INTO weather (city, weather_condition, temperature, humidity, wind, pressure, direction, icon)
        VALUES ('$cityName', '$weather_condition', '$temperature', '$humidity', '$wind', '$pressure', '$direction', '$icon')";

        if (!mysqli_query($conn, $insertData)) {
            throw new Exception("Failed to insert data: " . mysqli_error($conn));  // Exception for INSERT query error
        }
    }

    // Fetch updated data after insertion
    $result = mysqli_query($conn, $selectAllData);

    if (!$result) {
        throw new Exception("Failed to execute SELECT query: " . mysqli_error($conn));  // Exception for SELECT query error
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    $json_data = json_encode($rows);
    header('Content-Type: application/json');
    echo $json_data;

} catch (Exception $e) {
    // Handle all exceptions and display error message
    http_response_code(500); // optional, sets proper HTTP status
header('Content-Type: application/json');
echo json_encode(["error" => $e->getMessage()]);
exit;
}
?>
