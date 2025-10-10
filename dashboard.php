<?php
require_once 'config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}
$user_id = $_SESSION['user_id'];
// Handle API requests for favorites and search history
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $user_id = $_POST['userId'];
  $city = $_POST['city'];
  if ($action === 'save_history') {
    if (empty($city)) {
      http_response_code(400);
      echo json_encode(['error' => 'City is required']);
      exit;
    }

    try {
      $stmt = $pdo->prepare("INSERT INTO search_history (user_id, city)
VALUES (?, ?)");
      $stmt->execute([$user_id, $city]);
      echo json_encode(['success' => true]);
    } catch (PDOException $e) {
      http_response_code(500);
      echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
  } elseif ($action === 'add_favorite') {
    if (empty($city)) {
      http_response_code(400);
      echo json_encode(['error' => 'City is required']);
      exit;
    }
    try {
      $stmt = $pdo->prepare("INSERT INTO favorites (user_id, city) VALUES (?,
?)");
      $stmt->execute([$user_id, $city]);
      echo json_encode(['success' => true]);
    } catch (PDOException $e) {
      if ($e->getCode() == 23000) { // duplicate entry
        http_response_code(409);
        echo json_encode(['error' => 'City already in favorites']);
      } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' .
          $e->getMessage()]);
      }
    }
  }
  exit; // Stop further execution for API calls
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weather Dashboard</title>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;

      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.4)), url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060 &q=80');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: #001f4d;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .container {
      display: flex;
      flex: 1;
    }

    .sidebar {
      width: 250px;
      background: linear-gradient(rgba(0, 31, 77, 0.4), rgba(0, 51, 102, 0.5)), url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060 &q=80');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 30px 20px;
      box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
      z-index: 100;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 40px;
      font-weight: 700;
      font-size: 1.8rem;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;

      gap: 10px;
    }

    .sidebar h2 i {
      color: #5d9bff;
    }

    .nav-links {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .nav-links a {
      color: #dbe4ff;
      padding: 14px 16px;
      text-decoration: none;
      border-radius: 10px;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .nav-links a:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #ffffff;
      transform: translateX(5px);
    }

    .nav-links a.active {
      background: rgba(255, 255, 255, 0.2);
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .nav-links a i {
      font-size: 1.2rem;
      width: 24px;
      text-align: center;
    }

    .main {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(3px);

      margin-left: 250px;
    }

    header {
      margin-bottom: 30px;
    }

    h1 {
      font-weight: 800;
      font-size: 2.8rem;
      background: linear-gradient(135deg, #001f4d 0%, #003366 100%);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin-bottom: 10px;
    }

    .search-box {
      display: flex;
      gap: 15px;
      margin-bottom: 30px;
      max-width: 100%;
      background: white;
      padding: 10px;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0, 31, 77, 0.1);
    }

    .search-box input {
      flex: 1;
      padding: 14px 20px;
      border: 2px solid #e6f0ff;
      border-radius: 10px;
      font-size: 1rem;
      outline: none;
      background: #f8fbff;
      transition: all 0.3s ease;
      width: 100%;
    }

    .search-box input:focus {
      border-color: #5d9bff;
      box-shadow: 0 0 0 3px rgba(93, 155, 255, 0.2);
    }

    .btn {
      padding: 14px 28px;
      border: none;
      border-radius: 10px;
      cursor: pointer;

      background: linear-gradient(rgba(0, 31, 77, 0.7), rgba(0, 51, 102, 0.7));
      color: #ffffff;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 31, 77, 0.2);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 31, 77, 0.3);
    }

    .btn:active {
      transform: translateY(0);
    }

    .weather-display {
      display: none;
      margin: 30px 0;
      padding: 50px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: none;
      box-shadow: 0 5px 25px rgba(0, 31, 77, 0.1);
      position: relative;
      overflow: hidden;
      background-size: cover;
      background-position: center;
      transition: background-image 0.5s ease-in-out;
    }

    .weather-display::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: inherit;
      background-size: cover;
      background-position: center;
      filter: blur(2px);
      z-index: -1;
    }

    .weather-display::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      color: white;
      z-index: 0;
      border-radius: 20px;
    }

    .weather-display h2 {
      margin-top: 0;
      font-size: 1.8rem;
      color: #001f4d;
      font-weight: 700;
      position: relative;
      z-index: 1;
    }

    .weather-display .current-temp {
      font-size: 3.5rem;
      margin: 15px 0;
      color: #001f4d;
      font-weight: 700;
      position: relative;
      z-index: 1;
    }

    .weather-display p {
      font-size: 1.1rem;
      margin: 8px 0;
      color: #001f4d;
      position: relative;
      z-index: 1;
    }

    .weather-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
      position: relative;
      z-index: 1;
    }

    .weather-info-item {
      background: white;

      padding: 15px;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      position: relative;
      z-index: 1;
    }

    .weather-info-item i {
      font-size: 2rem;
      color: #001f4d;
      margin-bottom: 10px;
    }

    .weather-info-item span {
      font-weight: 600;
      color: #001f4d;
      font-size: 1.2rem;
    }

    .forecast-section h3 {
      font-weight: 700;
      font-size: 1.5rem;
      color: #001f4d;
      margin: 30px 0 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #001f4d;
      display: inline-block;
    }

    .forecast-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .forecast-item {
      background: white;
      padding: 20px;
      border-radius: 15px;
      text-align: center;
      transition: all 0.3s ease;
      box-shadow: 0 4px 20px rgba(0, 31, 77, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;

      position: relative;
      overflow: hidden;
    }

    .forecast-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(135deg, #001f4d 0%, #003366 100%);
    }

    .forecast-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0, 31, 77, 0.15);
    }

    .forecast-item strong {
      color: #001f4d;
      font-size: 1.1rem;
      margin-bottom: 10px;
    }

    .forecast-item .temp {
      font-size: 1.8rem;
      font-weight: 700;
      color: #001f4d;
      margin: 10px 0;
    }

    .forecast-item .desc {
      color: #56729a;
      font-size: 0.9rem;
    }

    .forecast-item i {
      font-size: 2.5rem;
      color: #001f4d;
      margin: 10px 0;
    }

    .weather-display .btn {
      position: relative;
      z-index: 1;
      background: linear-gradient(rgba(0, 31, 77, 0.7), rgba(0, 51, 102, 0.7));
    }

    /* Responsive Design */
    @media (max-width: 900px) {
      .container {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 15px;
      }

      .sidebar h2 {
        font-size: 1.5rem;
        margin-bottom: 20px;
      }

      .nav-links {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
      }

      .nav-links a {
        padding: 10px 12px;
        font-size: 0.85rem;
        gap: 8px;
      }

      .nav-links a i {
        font-size: 1rem;
        width: 20px;
      }

      .main {
        margin-left: 0;
        padding: 20px 15px;
      }

      header {
        margin-bottom: 20px;
      }

      h1 {
        font-size: 2rem;
        text-align: center;
      }

      .search-box {
        flex-direction: column;
        gap: 10px;
        padding: 15px;
      }

      .search-box input {
        padding: 12px 15px;
        font-size: 16px;
        /* Prevents zoom on iOS */
      }

      .btn {
        padding: 12px 20px;
        font-size: 0.9rem;
        justify-content: center;
      }

      .weather-display {
        padding: 30px 20px;
        margin: 20px 0;
      }

      .weather-display h2 {
        font-size: 1.5rem;
        text-align: center;
      }

      .current-temp {
        font-size: 2.5rem;
        text-align: center;
      }

      .weather-info-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .weather-info-item {
        padding: 12px;
      }

      .weather-info-item i {
        font-size: 1.5rem;
      }

      .weather-info-item span {
        font-size: 1rem;
      }

      .forecast-section h3 {
        font-size: 1.3rem;
        text-align: center;
        display: block;
        margin: 20px 0 15px;
      }

      .forecast-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .forecast-item {
        padding: 15px;
      }

      .forecast-item strong {
        font-size: 1rem;
      }

      .forecast-item .temp {
        font-size: 1.5rem;
      }

      .forecast-item i {
        font-size: 2rem;
      }
    }

    @media (max-width: 480px) {
      .sidebar {
        padding: 10px;
      }

      .sidebar h2 {
        font-size: 1.3rem;
      }

      .nav-links {
        gap: 5px;
      }

      .nav-links a {
        padding: 8px 10px;
        font-size: 0.8rem;
      }

      .main {
        padding: 15px 10px;
      }

      h1 {
        font-size: 1.8rem;
      }

      .search-box {
        padding: 12px;
      }

      .weather-display {
        padding: 20px 15px;
      }

      .weather-info-grid {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .forecast-grid {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .current-temp {
        font-size: 2.2rem;
      }

      .weather-display .btn {
        width: 100%;
        justify-content: center;
      }
    }

    /* Fix for mobile viewport */
    @media (max-width: 900px) {
      body {
        background-attachment: scroll;
        /* Prevents background image issues on mobile */
      }

      .sidebar {
        position: relative;
        /* Remove fixed positioning on mobile */
        height: auto;
      }
    }

    /* Prevent zoom on input focus for iOS */
    @media (max-width: 900px) {
      input[type="text"] {

        font-size: 16px;
      }
    }

    /* Improve touch targets for mobile */
    @media (max-width: 900px) {

      .btn,
      .nav-links a,
      .forecast-item,
      .weather-info-item,
      .delete-btn {
        min-height: 44px;
        /* Minimum touch target size */
      }

      .delete-btn {
        min-width: 44px;
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .weather-display,
    .forecast-item {
      animation: fadeIn 0.5s ease forwards;
    }

    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255, 255, 255, .3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
      margin-left: 10px;
    }

    @keyframes spin {

      to {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Sidebar/Navbar -->
    <aside class="sidebar">
      <h2><i class="fas fa-cloud-sun"></i> Menu</h2>
      <div class="nav-links">
        <a href="dashboard.php" class="active"><i class="fas
fa-tachometer-alt"></i> Dashboard</a>
        <a href="favorites.php"><i class="fas fa-star"></i> Favorites</a>
        <a href="history.php"><i class="fas fa-history"></i> History</a>
        <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
        <a href="help.php"><i class="fas fa-question-circle"></i> Help</a>
        <a href="feedback.php"><i class="fas fa-comment"></i> Feedback</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </aside>
    <!-- Main Content -->
    <main class="main">
      <header>
        <h1>Weather Dashboard</h1>
        <p>Welcome <?php echo $_SESSION['username']; ?>!</p>
      </header>
      <form id="weatherForm" class="search-box">
        <input type="text" id="cityInput" placeholder="Enter city (e.g.,London)" required>
        <button type="submit" class="btn"><i class="fas fa-search"></i>
          Search</button>
      </form>
      <div id="weatherDisplay" class="weather-display">
        <h2 id="weatherLocation"></h2>
        <p id="weatherDescription"></p>
        <div class="current-temp"><span id="currentTemp"></span></div>
        <div class="weather-info-grid">
          <div class="weather-info-item">
            <i class="fas fa-temperature-low"></i>
            <span>Feels like</span>
            <span id="feelsLike"></span>
          </div>
          <div class="weather-info-item">

            <i class="fas fa-tint"></i>
            <span>Humidity</span>
            <span id="humidity"></span>%
          </div>
          <div class="weather-info-item">
            <i class="fas fa-wind"></i>
            <span>Wind</span>
            <span id="windSpeed"></span> km/h
          </div>
          <div class="weather-info-item">
            <i class="fas fa-tachometer-alt"></i>
            <span>Pressure</span>
            <span id="pressure"></span> hPa
          </div>
        </div>
        <button onclick="addFavoriteFromDashboard()" class="btn"
          style="margin-top: 20px;">
          <i class="fas fa-plus"></i> Add to Favorites
        </button>
      </div>
      <div class="forecast-section">
        <h3>5-Day Forecast</h3>
        <div id="forecastContainer" class="forecast-grid"></div>
      </div>
    </main>
  </div>
  <script>
    let lastSearchedCity = null;
    const userId = <?php echo $user_id; ?>;
    async function fetchWeather(city) {
      const apiKey = "d151b0671b8b60f140e2a3c83b8d0311";
      // Show loading state
      const searchBtn = document.querySelector('#weatherForm .btn');
      const originalText = searchBtn.innerHTML;
      searchBtn.innerHTML = '<i class="fas fa-spinner loading"></i>Searching...';
      searchBtn.disabled = true;
      try {
        const res = await
        fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`);
        if (!res.ok) throw new Error("Weather not found");
        const data = await res.json();

        // Save search to history
        await saveSearchHistory(city);
        // Update UI
        const cityImage ="https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80";
        document.getElementById("weatherDisplay").style.backgroundImage =
          `url(${cityImage})`;
        document.getElementById("weatherLocation").textContent =
          `${data.name}, ${data.sys.country}`;
        document.getElementById("weatherDescription").textContent =
          data.weather[0].description;
        document.getElementById("currentTemp").textContent =
          `${Math.round(data.main.temp)}°C`;
        document.getElementById("feelsLike").textContent =
          `${Math.round(data.main.feels_like)}°C`;
        document.getElementById("humidity").textContent =
          data.main.humidity;
        document.getElementById("windSpeed").textContent =
          Math.round(data.wind.speed * 3.6);
        document.getElementById("pressure").textContent =
          data.main.pressure;
        document.getElementById("weatherDisplay").style.display = "block";
        lastSearchedCity = data.name;
        fetchForecast(city, apiKey);
      } catch (e) {
        alert(e.message);
      } finally {
        searchBtn.innerHTML = originalText;
        searchBtn.disabled = false;
      }
    }
    async function saveSearchHistory(city) {
      try {
        const formData = new URLSearchParams();
        formData.append('action', 'save_history');
        formData.append('userId', userId);
        formData.append('city', city);
        const response = await fetch('dashboard.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();
        if (!response.ok) {
          console.error('Failed to save search history');
        }
      } catch (error) {
        console.error('Error saving search history:', error);
      }
    }
    async function addFavoriteFromDashboard() {
      if (!lastSearchedCity) {
        alert("Please search for a city first!");
        return;
      }
      try {
        const formData = new URLSearchParams();
        formData.append('action', 'add_favorite');
        formData.append('userId', userId);
        formData.append('city', lastSearchedCity);
        const response = await fetch('dashboard.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (response.ok) {
          alert(`${lastSearchedCity} added to Favorites!`);
        } else {
          alert('Error: ' + result.error);
        }
      } catch (error) {
        console.error('Error adding favorite:', error);
        alert('Error adding favorite');
      }
    }
    async function fetchForecast(city, apiKey) {
      try {
        const res = await fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`);
        if (!res.ok) throw new Error("Forecast not found");
        const data = await res.json();
        const forecastContainer =
          document.getElementById("forecastContainer");
        forecastContainer.innerHTML = "";
        const daily = {};

        data.list.forEach(item => {
          if (item.dt_txt.includes("12:00:00")) {
            const date = new Date(item.dt *
              1000).toLocaleDateString("en-US", {
              weekday: "short",
              month: "short",
              day: "numeric"
            });
            daily[date] = {
              temp: Math.round(item.main.temp),
              desc: item.weather[0].description,
              icon: item.weather[0].main
            };
          }
        });
        Object.keys(daily).slice(0, 5).forEach(date => {
          const f = daily[date];
          let iconClass = "fas fa-cloud";
          if (f.icon === "Clear") iconClass = "fas fa-sun";
          else if (f.icon === "Rain") iconClass = "fas fa-cloud-rain";
          else if (f.icon === "Snow") iconClass = "fas fa-snowflake";
          else if (f.icon === "Clouds") iconClass = "fas fa-cloud";
          else if (f.icon === "Thunderstorm") iconClass = "fas fa-bolt";
          forecastContainer.innerHTML += `
<div class="forecast-item">
<strong>${date}</strong>
<i class="${iconClass}"></i>
<div class="temp">${f.temp}°C</div>
<div class="desc">${f.desc}</div>
</div>`;
        });
      } catch (e) {
        console.error(e);
      }
    }
    document.getElementById("weatherForm").addEventListener("submit", e => {
      e.preventDefault();
      const city = document.getElementById("cityInput").value.trim();
      if (city) fetchWeather(city);
    });
    // Auto-load selected city if redirected from History/Favorites
    window.addEventListener("DOMContentLoaded", () => {
      const urlParams = new URLSearchParams(window.location.search);
      const selectedCity = urlParams.get('city');
      if (selectedCity) {
        document.getElementById("cityInput").value = selectedCity;
        fetchWeather(selectedCity);

      }
    });
  </script>
</body>

</html>