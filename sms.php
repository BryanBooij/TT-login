<?php
session_start();
$number = $_POST['phone'];
$region = $_POST['country'];


$fullNumber = $region . $number;
var_dump($fullNumber);
$testing = '+31643934343';
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
$config = require 'config/app.php';
$accountSid = $config['smsAccountSid'];
$authToken =$config['smsAuthToken'];

$twilio = new Client($accountSid, $authToken);

// Phone number to send the SMS to (user's phone number)
$to = $fullNumber;
//$to = $testing;
function generateVerificationCode() {
    // Generate a random 6-digit number
    return mt_rand(100000, 999999);
}
// Generate the verification code
$verificationCode = generateVerificationCode();

$message = "Your verification code is: $verificationCode";

try {
$twilio->messages->create(
$to,
[
"body" => $message,
"from" => "+12513698817"
]
);

// SMS sent successfully
echo "Verification code sent successfully.";
var_dump($verificationCode);

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
        <label for="verification_code">Verification Code:</label><br>
        <input type="text" id="verification_code" name="verification_code"><br><br>
        <input type="submit" value="Submit">
    </form>
    <?php
    $userVerificationCode = $_POST['verification_code'];
    if ($userVerificationCode == $verificationCode) {
        $_SESSION['auth'] = true;
        header("Location: home.php");
        exit();
    } else {
        echo "Verification code is false.";
    }
    ?>
</body>
</html>
