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
    $password = $_POST["password"];

    $sql = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error executing the query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        session_start();
        $_SESSION["username"] = $username;
        $_SESSION['logged_in'] = true;
        header("Location: home.php");
        exit();
    } else {
        session_start();
        $_SESSION['error_message'] = 'Incorrect username or password';
        header('Location: login.php');
        exit();
    }
}
