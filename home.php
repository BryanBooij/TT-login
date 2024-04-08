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
    <title>Document</title>
</head>
<body>
<h1>Successfully logged in!</h1>
<a href="change_password.php">change password</a><br>
</body>
<?php
require_once __DIR__ . '/vendor/autoload.php';

if (isset($_SESSION['access_token'])) {
    echo '<a href="google_logout.php">google logout</a>';
} else {
    echo '<a href="logout.php">Logout</a>';
}
?>
</html>
