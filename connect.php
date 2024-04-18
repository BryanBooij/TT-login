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
    $usernameOrEmail = $_POST["username_or_email"];
    $password = $_POST["password"];

    // Fetch the hashed password from the database based on the provided username or email
    $stmt = $conn->prepare("SELECT username, password FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $stmt->bind_result($username, $hashedPassword);
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
?>
