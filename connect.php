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

    $sql = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error executing the query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        session_start();
        $_SESSION['username'] = $username;
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
