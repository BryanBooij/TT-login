<?php
session_start();
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    // Redirect to login page
    header("Location: login.php");
    exit;
}
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style/stylesheet.css">
    <title>Home</title>
</head>
<body>
<center>
<h1 class="title">Successfully logged in!</h1>
<a href="change_password.php"><button>Change password</button></a><br>
<?php
require_once __DIR__ . '/vendor/autoload.php';

if (isset($_SESSION['access_token'])) {
    echo '<a href="google_logout.php"><button>Logout</button></a>';
} else {
    echo '<a href="logout.php"><button>Logout</button></a>';
}
?>
</center>
</center>
</body>
</html>
