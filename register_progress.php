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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "INSERT INTO user (username, email, password) VALUES ('$username', '$email',  '$password')";
    $error_message = "Email already exists. Use a different one";
    if ($conn->query($sql) === TRUE) {
        session_start();
        $_SESSION['logged_in'] = true;
        header("Location: home.php");
    } else {
        header("Location: register.php?error=" . urlencode($error_message));
    }
}

$conn->close();

