<?php
require_once 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
try {
// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
$error = "Email already exists";
} else {
// Insert new user
$stmt = $pdo->prepare("INSERT INTO users (username, email, password)
VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $password]);
// Set session and redirect
session_start();
$_SESSION['user_id'] = $pdo->lastInsertId();
$_SESSION['username'] = $username;
$_SESSION['email'] = $email;
header("Location: dashboard.php");

exit();
}
} catch (PDOException $e) {
$error = "Database error: " . $e->getMessage();
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup - Weather Dashboard</title>
<style>
/* Your existing CSS styles */
body {
font-family: Arial, sans-serif;
background:url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
background-size: cover;
display: flex;
justify-content: center;
align-items: center;
height: 100vh;
margin: 0;
}
.signup-container {
background: rgba(255, 255, 255, 0.95);
color: #000;
padding: 2.5em;
border-radius: 15px;
width: 350px;
box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
text-align: center;
animation: fadeIn 1s ease-in-out;
border: 1px solid rgba(0, 0, 0, 0.1);
}
@keyframes fadeIn {
from {
opacity: 0;
transform: translateY(-20px);
}
to {
opacity: 1;
transform: translateY(0);
}

}
h2 {
margin-bottom: 1.5em;
font-size: 2em;
font-weight: bold;
}
input[type="text"],
input[type="email"],
input[type="password"] {
width: 100%;
padding: 10px;
margin: 0.5em 0 1em;
border: 1px solid #ccc;
border-radius: 5px;
font-size: 1em;
color: #000;
}
input::placeholder {
color: #555;
}
input:focus {
outline: none;
border-color: #0066cc;
box-shadow: 0 0 5px rgba(0, 102, 204, 0.5);
}
button {
padding: 14px 28px;
border: none;
border-radius: 10px;
cursor: pointer;
background: linear-gradient(135deg, #0099ff, #0066cc);
color: #ffffff;
font-weight: 600;
font-size: 1rem;
margin: 10px auto;
transition: all 0.3s ease;
text-decoration: none;
display: inline-flex;
align-items: center;
gap: 8px;
}
button:hover {
background: linear-gradient(135deg, #0066cc, #003366);
transform: translateY(-2px);

box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}
.login-link {
margin-top: 1.2em;
font-size: 0.9em;
color: black;
}
.login-link a {
color: #0066cc;
text-decoration: none;
font-weight: bold;
}
.login-link a:hover {
text-decoration: underline;
}
.message {
text-align: center;
margin-top: 10px;
font-weight: bold;
color: red;
}
</style>
</head>
<body>
<div class="signup-container">
<h2>Signup</h2>
<?php if (isset($error)): ?>
<div class="message"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<form method="POST" action="signup.php" autocomplete="new-password">
<input type="text" name="username" placeholder="Username" required
autocomplete="new-password">
<input type="email" name="email" placeholder="Email" required
autocomplete="new-password">
<input type="password" name="password" placeholder="Password" required
autocomplete="new-password">
<button type="submit">Signup</button>
</form>
<div class="login-link">
Already have an account? <a href="login.php">Login</a>
</div>
</div>
</body>
</html>