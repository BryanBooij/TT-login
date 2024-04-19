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

// Set the label for the user (optional)
$otp->setLabel('TouchTree');

// Generate QR code URI for the user to scan
$grCodeUri = $otp->getQrCodeUri(
    'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
    '[DATA]'
);
// Display the QR code for the user to scan

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
        $_SESSION['error_message'] = 'Invalid OTP code. Please try again.';
    }
}
?>
<center>
<!-- HTML form -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="otp">Enter 6 digit code:</label><br>
    <input type="text" id="otp" name="otp"><br>
    <input type="submit" value="Submit">
</form>
<a href="auth_redirect.php">Back</a>

<?php
if (isset($_SESSION['error_message'])) {
    echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
    unset($_SESSION['error_message']);
}
?>
</center>
