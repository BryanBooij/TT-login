<?php
// logs out google user
require_once __DIR__ . '/vendor/autoload.php';
session_start();
$access_token=$_SESSION['access_token'];

// unset tokens to properly logout user
unset($_SESSION['access_token']);
unset($_SESSION['userData']);

$client = new Google_Client();

// revoke google token
$client->revokeToken($access_token);

// destroy session
session_destroy();
// redirect user back to log in page
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/TT-login/login.php';
header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
