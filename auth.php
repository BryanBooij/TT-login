<?php
session_start();
$_SESSION['logged_in'] = true;
$username = $_SESSION['username'];
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page
    header("Location: login.php");
    exit;
}
global $conn;
require_once 'vendor/autoload.php';
use OTPHP\TOTP;

$config = require 'config/app.php';

$servername = $config['servername'];
$Gusername = $config['usernamelocalhost'];
$Gpassword = $config['passwordlocalhost'];
$database = $config['database'];

$conn = new mysqli($servername, $Gusername, $Gpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL query with parameters
$sql = "SELECT secret FROM user WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}

// Fetch the user's details from the result set
$user = $result->fetch_assoc();
$user_secret = $user['secret'];

$secret = $user_secret;

// Create TOTP object with the generated secret
$otp = TOTP::create($secret);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve input OTP code
    $input_otp = $_POST['otp'];

    // Verify OTP code
    $verification_result = $otp->verify($input_otp);

    // Check verification result
    if ($verification_result) {
        echo "OTP verified successfully!";
        $_SESSION['logged_in'] = true;
        $_SESSION['auth'] = true;
        header("Location: home.php");
    } else {
        $_SESSION['error_message'] = 'Invalid Authentication code. Please try again.';
    }
}
?>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style/stylesheet.css">
    <title>Authorization</title>
</head>
<body>
<center>
<!-- HTML form -->
<div class="qr_form">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="otp">Enter 6 digit code:</label><br>
        <input type="text" id="otp" name="otp"><br>
        <input type="submit" value="Submit">
    </form>
    <a href="auth_redirect.php"><button>Back</button></a>
</div>

<?php
if (isset($_SESSION['error_message'])) {
    echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
    unset($_SESSION['error_message']);
}
?>
</center>
</body>
</html>
