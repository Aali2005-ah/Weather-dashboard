<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
$user_id = $_SESSION['user_id'];
// Handle API requests for favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $user_id = $_POST['userId'];
    $city = $_POST['city'];
    if ($action === 'add_favorite') {
        if (empty($city)) {
            http_response_code(400);
            echo json_encode(['error' => 'City is required']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, city) VALUES (?,?)");
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
    } elseif ($action === 'remove_favorite') {
        if (empty($city)) {
            http_response_code(400);
            echo json_encode(['error' => 'City is required']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND city= ?");
            $stmt->execute([$user_id, $city]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Favorite not found']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    exit; // Stop further execution for API calls
}
// Get favorites from database
$stmt = $pdo->prepare("SELECT city FROM favorites WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites - Weather App</title>
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
            background: linear-gradient(rgba(255, 255, 255, 0.3),rgba(255, 255, 255, 0.4)),url("https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060 &q=80");
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar h2 i {
            color: #dbc4ff;
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

        .subtitle {
            font-size: 1.2rem;
            color: #56729a;
            margin-bottom: 30px;
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
            background: linear-gradient(rgba(0, 31, 77, 0.7), rgba(0, 51, 102,
                        0.7));
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
            background: rgba(0, 31, 77, 0.9);
        }

        .btn:active {
            transform: translateY(0);
        }

        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid #e6f0ff;
            padding: 30px;
            margin: 20px 0;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 31, 77, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background-image: url('https://cdn-icons-png.flaticon.com/512/1146/1146869.png');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.1;
            z-index: 0;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 31, 77, 0.15);
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: 700;
            color: #001f4d;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .card p {
            margin: 8px 0;
            color: #56729a;
            position: relative;
            z-index: 1;
            font-size: 1.1rem;
        }

        .card .temp {
            font-size: 2.2rem;
            font-weight: 700;
            color: #001f4d;
            margin: 15px 0;
        }

        .card-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .list {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            max-width: 100%;
        }

        .list li {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            margin: 15px 0;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 15px rgba(0, 31, 77, 0.1);
            transition: all 0.3s ease;
            cursor: default;
            border-left: 5px solid #001f4d;
        }

        .list li:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0, 31, 77, 0.15);
        }

        .list li span {
            font-weight: 600;
            color: #001f4d;
            font-size: 1.1rem;
        }

        .list-actions {
            display: flex;
            gap: 10px;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.5rem;
            color: #001f4d;
            margin: 40px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #001f4d;
            display: inline-block;
        }

        @media (max-width: 900px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .nav-links {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-links a {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .main {
                padding: 20px;
                margin-left: 0;
            }

            h1 {
                font-size: 2.2rem;
            }

            .search-box {
                flex-direction: column;
            }

            .card-actions,
            .list-actions {
                flex-direction: column;
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

        .card,
        .list li {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar/Navbar -->
        <aside class="sidebar">
            <h2><i class="fas fa-cloud-sun"></i> Menu</h2>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>
                    Dashboard</a>
                <a href="favorites.php" class="active"><i class="fas fa-star"></i>
                    Favorites</a>
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
                <h1>Favorites</h1>
                <p class="subtitle">Your saved weather locations</p>
            </header>
            <form id="favoritesForm" class="search-box">
                <input type="text" id="favoriteInput" placeholder="Enter city to add" required>
                <button type="submit" class="btn"><i class="fas fa-plus"></i>
                    Add</button>
            </form>
            <div id="favoritesContainer"></div>
            <h3 class="section-title">All Favorite Cities</h3>
            <ul id="favoritesList" class="list"></ul>
        </main>
    </div>
    <script>
        const userId = <?php echo $user_id; ?>;
        let favorites = <?php echo json_encode($favorites); ?>;
        async function addFavorite(city) {
            try {
                const formData = new URLSearchParams();
                formData.append('action', 'add_favorite');
                formData.append('userId', userId);
                formData.append('city', city);
                const response = await fetch('favorites.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (response.ok) {
                    favorites.push(city);
                    renderFavorites();
                    renderFavoritesList();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error adding favorite:', error);
                alert('Error adding favorite');
            }
        }
        async function removeFavorite(city) {
            try {
                const formData = new URLSearchParams();
                formData.append('action', 'remove_favorite');
                formData.append('userId', userId);
                formData.append('city', city);
                const response = await fetch('favorites.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (response.ok) {
                    favorites = favorites.filter(f => f !== city);
                    renderFavorites();
                    renderFavoritesList();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error removing favorite:', error);
                alert('Error removing favorite');
            }
        }
        async function fetchWeather(city) {
            const apiKey = "d151b0671b8b60f140e2a3c83b8dd931";
            try {
                const res = await
                fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`);
                if (!res.ok) throw new Error("Weather not found");
                return await res.json();
            } catch (e) {
                console.error(e);
                return null;
            }
        }
        async function renderFavorites() {
            const container = document.getElementById("favoritesContainer");
            container.innerHTML = "";
            if (favorites.length === 0) {
                container.innerHTML = "<p>No favorites added yet. Add cities to see their weather here. < /p>";
                return;
            }
            for (const city of favorites) {
                const data = await fetchWeather(city);
                if (!data) continue;
                container.innerHTML += `
<div class="card">
<h3>${data.name}, ${data.sys.country}</h3>
<p>${data.weather[0].description}</p>
<div class="temp">${Math.round(data.main.temp)}°C</div>
<p>Feels like ${Math.round(data.main.feels_like)}°C •
Humidity: ${data.main.humidity}%</p>
<div class="card-actions">
<button class="btn" onclick='viewWeather("${city}")'><i
class="fas fa-eye"></i> View</button>
<button class="btn"
onclick="removeFavorite('${city}')"><i class="fas fa-trash"></i> Remove</button>
</div>
</div>`;
            }
        }

        function renderFavoritesList() {
            const list = document.getElementById("favoritesList");
            if (favorites.length === 0) {
                list.innerHTML = "<li>No favorite cities yet</li>";
                return;
            }
            list.innerHTML = favorites.map(city => `
<li>
<span>${city}</span>
<div class="list-actions">
<button class="btn" onclick='viewWeather("${city}")'><i
class="fas fa-eye"></i> View</button>
<button class="btn" onclick="removeFavorite('${city}')"><i
class="fas fa-trash"></i> Remove</button>
</div>
</li>
`).join("");
        }

        function viewWeather(city) {
            window.location.href =
                `dashboard.php?city=${encodeURIComponent(city)}`;
        }
        document.getElementById("favoritesForm").addEventListener("submit", e => {
            e.preventDefault();
            const city = document.getElementById("favoriteInput").value.trim();
            if (city) {
                addFavorite(city);
                document.getElementById("favoriteInput").value = "";
            }
        });
        // Load on page open
        renderFavorites();
        renderFavoritesList();
    </script>
</body>

</html>