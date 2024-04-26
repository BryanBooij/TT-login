<?php
session_start();
$username = $_SESSION['username'];
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page
    header("Location: login.php");
    exit;
}

$config = require 'config/app.php';

$servername = $config['servername'];
$Gusername = $config['usernamelocalhost'];
$Gpassword = $config['passwordlocalhost'];
$database = $config['database'];

$conn = new mysqli($servername, $Gusername, $Gpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT qr_scanned, email FROM user WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}

$user = $result->fetch_assoc();
$email = $user['email'];


// SQL query
$sql = "SELECT qr_scanned FROM user WHERE email = '$email'";

$result = $conn->query($sql);

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
    <title>Authenticator redirects</title>
</head>
<body>
<center>
    <h1 class="title">Authenticator</h1>
<!--    <div class="redirect">-->
<!--        <img src="img/auth-logo.png" alt="auth-logo" width="150" height="150">-->
<!--        <div class="redirect_links">-->
<!--            <a href="qr_auth.php"><button class="btn btn-primary d-inline-flex align-items-center" type="button">I haven't connected my Authenticator yet</button></a><br>-->
<!--            <a href="auth.php"><button class="btn btn-outline-secondary d-inline-flex align-items-center" type="button">i have connected Authenticator to this account</button></a>-->
<!--        </div>-->
<!--        <img src="img/auth-logo-google.png" alt="auth-logo" width="150" height="150">-->
<!--    </div>-->
    <div class="redirect">
        <img src="img/auth-logo.png" alt="auth-logo" width="150" height="150"><br><br>
        <div class="redirect_links">
            <?php
            require_once __DIR__ . '/vendor/autoload.php';

            if ($result = false) {
                echo '<a href="qr_auth.php"><button class="btn btn-primary d-inline-flex align-items-center" type="button">I havent connected my Authenticator yet</button></a><br>';
            } else {
                echo '<a href="auth.php"><button class="btn btn-outline-secondary d-inline-flex align-items-center" type="button">i have connected Authenticator to this account</button></a>';
            }
            ?>
        </div>
        <img src="img/auth-logo-google.png" alt="auth-logo" width="150" height="150">
    </div>
</center>
</body>
</html>
