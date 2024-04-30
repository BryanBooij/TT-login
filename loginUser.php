<?php
// login user to check if user exists
global $conn;
require_once "connect.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = $_POST["username_or_email"];
    $password = $_POST["password"];

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