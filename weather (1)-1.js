const apikey = "449d496cd673d2e3eb80b52bac9588ea";

const searchbox = document.querySelector(".search-box input");
const button = document.querySelector("#searchbtn");
const weathericon = document.querySelector(".weathericon");
const condition = document.querySelector(".condition");

function updateDateTime() {
    const now = new Date(); 
    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    document.querySelector(".day").textContent = days[now.getDay()];
    document.querySelector(".date").textContent = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
}

// Updated checkWeather function with cache-then-network strategy
async function checkWeather(city = "Cardiff") {
    const apiurl = `https://raiweatherapp.free.nf/prototype3/connection.php?cityName=${city}`;
    let weather;

    if (navigator.onLine) {
        console.log("Onine")
        try {
            const response = await fetch(apiurl);
            if (!response.ok) throw new Error("City not found text Error:");

            const data = await response.json();
            weather = data[0]; // Only first item from array

            // Save to local storage
            localStorage.setItem(city.toLowerCase(), JSON.stringify(weather));
        } catch (error) {
            alert("Error fetching online data: " + error.message);
            return;
        }
    } else {
        // Offline mode: load from cache
        const cachedData = localStorage.getItem(city.toLowerCase());
        if (cachedData) {
            weather = JSON.parse(cachedData);
        } else {
            alert("No cached data available for this city.");
            return;
        }
    }

    // Display weather data
    document.querySelector(".city").textContent = weather.city;
    condition.textContent = weather.weather_condition;
    document.querySelector(".temperature").textContent = Math.round(weather.temperature) + "°C";
    document.querySelector("#humidity").textContent = weather.humidity + "%";
    document.querySelector("#wind").textContent = weather.wind + " m/s";
    document.querySelector("#pressure").textContent = weather.pressure + " hPa";
    document.querySelector("#direction").textContent = weather.direction + "°";

    weathericon.src = `https://openweathermap.org/img/wn/${weather.icon}@2x.png`;
    weathericon.alt = weather.weather_condition;
}

// 🔍 Search button event
button.addEventListener("click", () => {
    if (searchbox.value.trim()) {
        checkWeather(searchbox.value.trim());
    } else {
        alert("Please enter a city name.");
    }
});

// 🕒 Date and time updates
updateDateTime();
setInterval(updateDateTime, 60000); // updates time every minute

// 🌤️ Initial weather data for default city
checkWeather("Cardiff");

