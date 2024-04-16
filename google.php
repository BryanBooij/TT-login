<?php

global $conn;
require __DIR__ . '/vendor/autoload.php';
include_once 'send_email.php';
include_once 'connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connection successful!";
}

session_start();

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/secret/client_secret.json');
$client->setRedirectUri('http://localhost/login/google.php');
$client->addScope(['openid', 'profile', 'email']);


if (isset($_GET['code'])) {

    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($accessToken['error'])) {
        unset($_GET['code']);
        header('Location: index.php');
        exit();
    }
    $accessToken = $client->getAccessToken();

    $googleOAuthService = new Google_Service_Oauth2($client);
    $userInfo = $googleOAuthService->userinfo->get();
    $email = $userInfo->getEmail();
    $name = strtolower($userInfo->getGivenName());

    function generateRandomPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_';

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    function generate_user_secret($length = 16) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 characters
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $secret;
    }

    $randomPassword = generateRandomPassword();
    $user_secret = generate_user_secret();

    // Check if the user already exists in the database
    $checkUserQuery = "SELECT * FROM user WHERE email = ?";
    $checkStmt = $conn->prepare($checkUserQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $name;
        $_SESSION['access_token'] = $accessToken;
        $_SESSION['logged_in'] = true;
        header("Location: http://localhost/login/home.php");
        exit();
    } else {
        // User does not exist, insert into database and send email with password
        $insertSql = "INSERT INTO user (username, email, password, secret) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $username = $name;
        $userEmail = $email;
        $password = $randomPassword;
        $secret = $user_secret;
        if (!$stmt->bind_param("ssss", $username, $userEmail, $password, $secret)) {
            die("Error binding parameters: " . $stmt->error);
        }

        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error executing query: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
    $_SESSION['access_token'] = $accessToken;
    $_SESSION['logged_in'] = true;
    sendEmail($email, $randomPassword, $username);
    header("Location: auth_redirect.php");

} else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
}