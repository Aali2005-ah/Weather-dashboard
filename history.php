<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
header('Location: login.html');
exit();
}
$user_id = $_SESSION['user_id'];
// Handle API requests for history actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
$action = $_POST['action'];
$user_id = $_POST['userId'];
if ($action === 'delete_history') {
$city = $_POST['city'];

$searched_at = $_POST['searched_at'];
try {
$stmt = $pdo->prepare("DELETE FROM search_history WHERE user_id = ? AND
city = ? AND searched_at = ?");
$stmt->execute([$user_id, $city, $searched_at]);
if ($stmt->rowCount() > 0) {
echo json_encode(['success' => true]);
} else {
http_response_code(404);
echo json_encode(['error' => 'History item not found']);
}
} catch (PDOException $e) {
http_response_code(500);
echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
} elseif ($action === 'clear_all_history') {
try {
$stmt = $pdo->prepare("DELETE FROM search_history WHERE user_id = ?");
$stmt->execute([$user_id]);
echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
} catch (PDOException $e) {
http_response_code(500);
echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
}
exit; // Stop further execution for API calls
}
// Get search history from database
$stmt = $pdo->prepare("SELECT city, searched_at FROM search_history WHERE user_id =
? ORDER BY searched_at DESC");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>History - Weather App</title>
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
background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255,
255, 0.4)),url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060&q=80');
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
background: linear-gradient(rgba(0, 31, 77, 0.4), rgba(0, 51, 102,
0.5)),url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060&q=80');
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
.list {
list-style: none;
padding: 0;
margin: 30px 0;
max-width: 100%;
}
.list li {
background: rgba(255, 255, 255, 0.85);
padding: 20px 25px;
margin: 15px 0;
border-radius: 15px;
display: flex;
justify-content: space-between;
align-items: center;
box-shadow: 0 3px 15px rgba(0, 31, 77, 0.1);
transition: all 0.3s ease;
cursor: pointer;
border-left: 5px solid #001f4d;
}
.list li:hover {
transform: translateX(5px);
box-shadow: 0 5px 20px rgba(0, 31, 77, 0.15);
background: rgba(255, 255, 255, 0.95);
}

.list li span {
font-weight: 600;
color: #001f4d;
font-size: 1.1rem;
flex: 1;
}
.history-time {
color: #56729a;
font-size: 0.9rem;
margin-right: 15px;
}
.delete-btn {
background: none;
border: none;
color: #001f4d;
font-size: 1.3rem;
cursor: pointer;
padding: 8px;
border-radius: 50%;
transition: all 0.3s ease;
width: 40px;
height: 40px;
display: flex;
align-items: center;
justify-content: center;
}
.delete-btn:hover {
background: rgba(255, 107, 107, 0.1);
color: #ff4757;
transform: scale(1.1);
}
.clear-all-btn {
background: linear-gradient(rgba(0, 31, 77, 0.7), rgba(0, 51, 102,
0.7));
color: white;
border: none;
padding: 12px 24px;
border-radius: 10px;
cursor: pointer;
font-weight: 600;
margin-bottom: 20px;
transition: all 0.3s ease;
}
.clear-all-btn:hover {
transform: translateY(-2px);

box-shadow: 0 6px 20px rgba(0, 31, 77, 0.3);
}
.empty-message {
text-align: center;
color: #56729a;
font-size: 1.1rem;
margin-top: 50px;
padding: 30px;
background: rgba(255, 255, 255, 0.7);
border-radius: 15px;
max-width: 600px;
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
.list li {
flex-direction: column;
align-items: flex-start;
gap: 15px;
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
<a href="favorites.php"><i class="fas fa-star"></i> Favorites</a>
<a href="history.php" class="active"><i class="fas fa-history"></i>
History</a>
<a href="about.php"><i class="fas fa-info-circle"></i> About</a>
<a href="help.php"><i class="fas fa-question-circle"></i> Help</a>
<a href="feedback.php"><i class="fas fa-comment"></i> Feedback</a>
<a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</aside>
<!-- Main Content -->
<main class="main">
<header>
<h1>Search History</h1>
<p class="subtitle">Your recent weather searches</p>
</header>
<?php if (!empty($history)): ?>
<button class="clear-all-btn" onclick="clearAllHistory()">
<i class="fas fa-trash"></i> Clear All History
</button>
<?php endif; ?>

<ul id="historyList" class="list"></ul>
</main>
</div>
<script>
const userId = <?php echo $user_id; ?>;
let history = <?php echo json_encode($history); ?>;
function renderHistory() {
const list = document.getElementById("historyList");
if (history.length === 0) {
list.innerHTML = `
<div class="empty-message">
<i class="fas fa-search" style="font-size: 3rem;
margin-bottom: 15px; color: #5d9bff;"></i>
<p>No search history yet.<br>Start searching for cities to
see your history here.</p>
</div>`;
return;
}
list.innerHTML = history.map((item, index) => `
<li>
<span onclick="viewWeather('${item.city}')">${item.city}</span>
<div style="display: flex; align-items: center; gap: 15px;">
<span
class="history-time">${formatDate(item.searched_at)}</span>
<button class="delete-btn"
onclick="deleteHistory(${index})" title="Delete from history">
<i class="fas fa-times"></i>
</button>
</div>
</li>
`).join("");
}
function formatDate(dateString) {
const date = new Date(dateString);
return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
hour: '2-digit', minute: '2-digit' });
}
function viewWeather(city) {
window.location.href =
`dashboard.php?city=${encodeURIComponent(city)}`;
}
async function deleteHistory(index) {
const city = history[index].city;

const searched_at = history[index].searched_at;
if (confirm(`Are you sure you want to remove "${city}" from your
history?`)) {
try {
const formData = new URLSearchParams();
formData.append('action', 'delete_history');
formData.append('userId', userId);
formData.append('city', city);
formData.append('searched_at', searched_at);
const response = await fetch('history.php', {
method: 'POST',
body: formData
});
const result = await response.json();
if (response.ok) {
history.splice(index, 1);
renderHistory();
} else {
alert('Error: ' + result.error);
}
} catch (error) {
console.error('Error deleting history:', error);
alert('Error deleting history');
}
}
}
async function clearAllHistory() {
if (confirm("Are you sure you want to clear all search history?")) {
try {
const formData = new URLSearchParams();
formData.append('action', 'clear_all_history');
formData.append('userId', userId);
const response = await fetch('history.php', {
method: 'POST',
body: formData
});
const result = await response.json();
if (response.ok) {
history = [];
renderHistory();
} else {
alert('Error: ' + result.error);
}
} catch (error) {

console.error('Error clearing history:', error);
alert('Error clearing history');
}
}
}
// Load on page open
renderHistory();
</script>
</body>
</html>