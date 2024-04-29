<?php
global $conn;
session_start();
include 'connect.php';
$number = $_POST['phone'];
$region = $_POST['country'];
$fullNumber = $region . $number;
$username = $_SESSION['username'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['verification_code'])) {
        $userVerificationCode = trim($_POST['verification_code']);

        if ($userVerificationCode == $_SESSION['verification_code']) {
            $stmt = $conn->prepare("UPDATE user SET number = ? WHERE username = ?");
            $stmt->bind_param("ss", $fullNumber, $username);
            $stmt->execute();
            $stmt->close();
            $_SESSION['auth'] = true;
            header("Location: home.php");
            exit();
        } else {
            echo "Verification code is incorrect.";
        }
    }
}



require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
$config = require 'config/app.php';
$accountSid = $config['smsAccountSid'];
$authToken =$config['smsAuthToken'];

$twilio = new Client($accountSid, $authToken);

function generateVerificationCode() {
    return mt_rand(100000, 999999);
}

$verificationCode = generateVerificationCode();
$_SESSION['verification_code'] = $verificationCode;

$message = "Your verification code is: $verificationCode";

try {
    $twilio->messages->create(
        $fullNumber,
        [
            "body" => $message,
            "from" => "+12513698817"
        ]
    );

    // SMS sent successfully
    echo "Verification code sent successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style/stylesheet.css">
    <title>Verification Code</title>
</head>
<body>
<h2>Enter Verification Code</h2>
<form action="" method="post">
    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($fullNumber); ?>"> <!-- needs to be added otherwise the number doesn't get saved and put in the query-->
    <label for="verification_code">Verification Code:</label><br>
    <input type="text" id="verification_code" name="verification_code"><br><br>
    <input type="submit" value="Submit">
</form>
</body>
</html>
