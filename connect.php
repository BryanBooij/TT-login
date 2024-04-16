<?php
$config = require 'config/app.php';

$servername = $config['servername'];
$Gusername = $config['usernamelocalhost'];
$Gpassword = $config['passwordlocalhost'];
$database = $config['database'];

$conn = new mysqli($servername, $Gusername, $Gpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Fetch the hashed password from the database based on the provided username
    $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify the password
    if ($hashedPassword !== null && password_verify($password, $hashedPassword)) {
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        $_SESSION['password'] = $password;
        header("Location: auth_redirect.php");
        exit();
    } else {
        session_start();
        $_SESSION['error_message'] = 'Incorrect username or password';
        header('Location: login.php');
        exit();
    }
}
