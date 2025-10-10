<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
header('Location: login.html');
exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Help - Weather Dashboard</title>
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
background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255,255,0.4)),url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060&q=80');
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
/* Sidebar/Navbar */
.sidebar {
width: 250px;
background: linear-gradient(rgba(0, 31, 77, 0.4), rgba(0, 51, 102,0.5)),url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060&q=80');
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
/* Main Content */
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
/* Help Content */
.help-content {
/* background: rgba(255, 255, 255, 0.9); */
border-radius: 20px;
padding: 40px;
box-shadow: 0 5px 25px rgba(0, 31, 77, 0.1);
margin-bottom: 30px;
}
.help-section {
margin-bottom: 30px;
}
.help-section h2 {
color: #001f4d;
font-size: 1.8rem;
margin-bottom: 15px;
padding-bottom: 10px;
border-bottom: 2px solid #001f4d;
display: flex;
align-items: center;
gap: 10px;
}
.help-section h2 i {
color: #104ba8;
}
.help-section p {
color: #001f4d;
line-height: 1.6;
margin-bottom: 15px;
}
.help-section ul {
color: #001f4d;
padding-left: 20px;
margin-bottom: 15px;
}
.help-section li {
margin-bottom: 8px;
line-height: 1.5;
}
.faq-item {
background: white;
border-radius: 10px;
padding: 20px;
margin-bottom: 15px;
box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
border-left: 4px solid #001f4d;
;
}
.faq-question {
font-weight: 600;
color: #001f4d;
margin-bottom: 10px;
display: flex;
align-items: center;
gap: 10px;
}
.faq-question i {
color: #104ba8;
}
.faq-answer {
color: #56729a;
line-height: 1.5;
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
padding: 20px;
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
margin-left: 0;
padding: 20px;
}
h1 {
font-size: 2.2rem;
}
.help-content {
padding: 20px;
}
.help-section h2 {
font-size: 1.5rem;
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
<a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>
Dashboard</a>
<a href="favorites.php"><i class="fas fa-star"></i> Favorites</a>
<a href="history.php"><i class="fas fa-history"></i> History</a>
<a href="about.php"><i class="fas fa-info-circle"></i> About</a>
<a href="help.php" class="active"><i class="fas
fa-question-circle"></i> Help</a>
<a href="feedback.php"><i class="fas fa-comment"></i> Feedback</a>
<a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</aside>
<!-- Main Content -->
<main class="main">
<header>
<h1>Help Center</h1>
<p>Find answers to common questions and get support for the Weather
Dashboard</p>
</header>
<div class="help-content">
<div class="help-section">
<h2><i class="fas fa-search"></i> How to Use the Dashboard</h2>
<p>The Weather Dashboard is designed to be simple and
intuitive. Here's how to get started:</p>
<ul>
<li>Enter a city name in the search box on the
dashboard</li>
<li>Click the search button or press Enter</li>
<li>View current weather conditions and 5-day forecast</li>
<li>Add cities to your favorites for quick access</li>
<li>Check your search history to revisit previous
searches</li>
</ul>
</div>
<div class="help-section">
<h2><i class="fas fa-question-circle"></i> Frequently Asked
Questions</h2>
<div class="faq-item">
<div class="faq-question">
<i class="fas fa-question"></i> Why can't I find
weather for a specific city?
</div>
<div class="faq-answer">
Make sure you're entering the correct city name. Try
using the English name if the city is
in a non-English speaking country. Some smaller towns
might not be available in our
database.
</div>
</div>
<div class="faq-item">
<div class="faq-question">
<i class="fas fa-question"></i> How do I add a city to
my favorites?
</div>
<div class="faq-answer">
After searching for a city, click the "Add to
Favorites" button that appears below the
weather information. You can then access your favorite
cities from the Favorites page.
</div>
</div>
<div class="faq-item">
<div class="faq-question">
<i class="fas fa-question"></i> Why is the temperature
displayed in Celsius?
</div>
<div class="faq-answer">
The dashboard currently displays temperatures in
Celsius by default. We're working on adding
temperature unit preferences in a future update.
</div>
</div>
<div class="faq-item">
<div class="faq-question">
<i class="fas fa-question"></i> How accurate is the
weather data?
</div>
<div class="faq-answer">
Our weather data comes from reliable sources and is
updated regularly. However, weather
forecasting is inherently uncertain, especially for
longer-term forecasts.
</div>
</div>
<div class="faq-item">
<div class="faq-question">
<i class="fas fa-question"></i> Is there a mobile app
available?
</div>
<div class="faq-answer">
Currently, we only offer this web-based dashboard. The
website is fully responsive and works
well on mobile devices.
</div>
</div>
<div class="faq-item">
<div class="faq-question">
<i class="fas fa-question"></i> Can I share weather
information with others?
</div>
<div class="faq-answer">
Yes, you can share the current weather view by copying
the URL from your browser. The page
will show the same city when opened.
</div>
</div>