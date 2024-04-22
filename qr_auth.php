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

// Prepare SQL query with parameters to check if the user already has a secret
$sql = "SELECT secret, email FROM user WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}

$user = $result->fetch_assoc();
// Fetch the user's secret from the result set
$user_secret = $user['secret'];

// Create TOTP object with the user's secret
$otp = TOTP::create($user_secret);
$otp->setLabel($user['email']);
$otp->setIssuer('TouchTree');

// Generate QR code URI for the user to scan
$grCodeUri = $otp->getQrCodeUri(
    'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
    '[DATA]'
);

// Display the QR code for the user to scan
echo "<center><img src='{$grCodeUri}' alt='QR Code' class='qr_code'><br></center>";

// Inform the user that 2FA setup is complete
echo "<center>Scan the QR code above with your authenticator app to complete 2FA setup. <br></center>";

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

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="otp">Enter 6 digit code:</label><br>
    <input type="text" id="otp" name="otp"><br>
    <input type="submit" value="Submit">
</form>

<a href="auth_redirect.php"><button>Back</button></a>
    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']);
    }
    ?>
</center>
</body>
</html>
