<?php
session_start();
global $conn;
require __DIR__ . '/vendor/autoload.php';
include_once 'send_email.php';
include_once 'connect.php';

//google connection
$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/secret/client_secret.json'); //login credentials for Google connection
$client->setRedirectUri('http://localhost/TT-login/google.php');
$client->addScope(['openid', 'profile', 'email']);

if (isset($_GET['code'])) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']); // get access token from Google
    if (isset($accessToken['error'])) {
        unset($_GET['code']);
        header('Location: login.php');
        exit();
    }
    $accessToken = $client->getAccessToken();

    $googleOAuthService = new Google_Service_Oauth2($client);
    $userInfo = $googleOAuthService->userinfo->get();
    $email = $userInfo->getEmail();
    $name = strtolower($userInfo->getGivenName()); // lowercase string for easier access for users

    function generateRandomPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        // as long as $i is smaller than 12 continue for loop for random numbers
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    function generate_user_secret($length = 16) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 secret
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $secret;
    }

    // create temp password for Google users
    $randomPassword = generateRandomPassword();
    $hashed_password = password_hash($randomPassword, PASSWORD_DEFAULT);
    // create user_secret for authenticator qr
    $user_secret = generate_user_secret();

    // Check if the email already exists in the database
    $checkUserQuery = "SELECT * FROM user WHERE email = ?";
    $checkStmt = $conn->prepare($checkUserQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $userEmail = $email;
    $password = $hashed_password;
    $secret = $user_secret;

    // checks if user already exists if so redirect with correct tokens
    if ($checkResult->num_rows > 0) {
        $_SESSION['access_token'] = $accessToken;
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $userEmail;
        $_SESSION['password'] = $password;
        header("Location: auth_redirect.php");
        exit();
    } else {
        // user doesn't exist create new user
        // Insert the user into the database
        $insertSql = "INSERT INTO user (username, display_username, email, password, secret) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        if (!$stmt->bind_param("sssss", $userEmail, $name, $userEmail, $password, $secret)) {
            die("Error binding parameters: " . $stmt->error);
        }

        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error executing query: " . $stmt->error;
        }

        $stmt->close();
    }
    // close database connection
    $conn->close();
    $_SESSION['access_token'] = $accessToken;
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $userEmail;
    $_SESSION['password'] = $password;
    // send email to Google user with temp password (optional to use)
    sendEmail($email, $randomPassword, $name);
    header("Location: auth_redirect.php");
} else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
}