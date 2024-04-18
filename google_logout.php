<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();
$access_token=$_SESSION['access_token'];

unset($_SESSION['access_token']);
unset($_SESSION['userData']);


$client = new Google_Client();


$client->revokeToken($access_token);


session_destroy();


$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/TT-login/login.php';
header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
