<?php
$config = require 'config/app.php';

$servername = $config['servername'];
$username = $config['usernamelocalhost'];
$password = $config['passwordlocalhost'];
$database = $config['database'];

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generate_user_secret($length = 16) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 characters
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $secret;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $password_repeat = $_POST["password_repeat"];

    // Check if passwords match
    if ($password !== $password_repeat) {
        $error_message = "Passwords do not match.";
        header("Location: register.php?error=" . urlencode($error_message));
        exit; // Stop execution
    }

    $user_secret = generate_user_secret();
    $sql = "INSERT INTO user (username, email, password, secret) VALUES ('$username', '$email',  '$password', '$user_secret')";
    $error_message = "Email already exists. Use a different one";
    if ($conn->query($sql) === TRUE) {
        session_start();
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        header("Location: auth_redirect.php");
    } else {
        header("Location: register.php?error=" . urlencode($error_message));
    }
}

$conn->close();

