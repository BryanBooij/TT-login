<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page
    header("Location: login.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Authenticator redirects</title>
</head>
<body>
<center>
<a href="qr_auth.php"><button>I havent connected authenticator yet to this account</button></a><br><br>
<a href="auth.php"><button>i have connected authenticator to this account</button></a>
</center>
</body>
</html>
