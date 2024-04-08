<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();

if (isset($_SESSION['access_token'])) {

    // User logged in via Google
    unset($_SESSION['access_token']);
    unset($_SESSION['userData']);

    // Create a new instance of Google_Client
    $client = new Google_Client();

    // Check if the client has a valid access token
    if ($client->isAccessTokenExpired()) {
        // If the access token is expired or not set, there's no need to revoke it
        // Redirect to homepage or login page
        header("Location: login.php");
        exit;
    } else {
        // Revoke the access token associated with the current client instance
        $client->revokeToken();
    }

    // Redirect to homepage or login page
    header("Location: google.php");
    exit;
} else {
    // User logged in via personal login form
    $_SESSION = array();
    session_destroy();
    // Redirect to homepage or login page
    header("Location: login.php");
    exit;
}
?>
