<?php
session_start();
$_SESSION['logged_in'] = true;
$username = $_SESSION['username'];
$password = $_SESSION['password'];
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
$sql = "SELECT secret FROM user WHERE username=? AND password=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}
//$user_secret = $result->fetch_all(MYSQLI_ASSOC);
$user = $result->fetch_assoc();
$user_secret = $user['secret'];

// Fetch the user's secret from the result set
// Check if the user's secret is null or empty
if (empty($user_secret)) {
    // Function to generate a secret for a user
    function generate_user_secret($length = 16) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 characters
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $secret;
    }

    // Generate a secret for the user
    $user_secret = generate_user_secret();

    // Prepare SQL query to update the user's secret
    $update_sql = "UPDATE user SET secret = ? WHERE username = ? AND (secret IS NULL OR secret = '')";
    $update_stmt = $conn->prepare($update_sql);

    // Check if the prepare operation was successful
    if ($update_stmt === false) {
        die("Error preparing update statement: " . $conn->error);
    }

    // Bind parameters and execute the update statement
    $update_stmt->bind_param("ss", $user_secret, $username);

    $update_result = $update_stmt->execute();

    // Check if the execute operation was successful
    if ($update_result === false) {
        die("Error executing update statement: " . $update_stmt->error);
    }

// Check if any rows were affected by the update operation
    if ($update_stmt->affected_rows <= 0) {
        die("Error updating user secret. No rows affected.");
    }

}

// Create TOTP object with the user's secret
$otp = TOTP::create($user_secret);
$otp->setLabel('TouchTree');

// Generate QR code URI for the user to scan
$grCodeUri = $otp->getQrCodeUri(
    'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
    '[DATA]'
);
// Display the QR code for the user to scan
echo "<img src='{$grCodeUri}' alt='QR Code'><br>";

// Inform the user that 2FA setup is complete
echo "Scan the QR code above with your authenticator app to complete 2FA setup. <br>";

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
        echo "Invalid OTP code. Please try again.";
    }
}
?>

<!-- HTML form -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="otp">Enter OTP:</label><br>
    <input type="text" id="otp" name="otp"><br>
    <input type="submit" value="Submit">
</form>
<a href="auth_redirect.php">Back</a>
