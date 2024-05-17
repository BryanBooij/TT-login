<?php
global $conn, $phoneNumber;
session_start();
include 'connect.php';
$username = $_SESSION['username'];
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
$config = require 'config/app.php';
$accountSid = $config['smsAccountSid'];
$authToken = $config['smsAuthToken'];
$twilio = new Client($accountSid, $authToken);



$sql = "SELECT number FROM user WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}
$user = $result->fetch_assoc();

if ($user['number']!="")
{
    $fullNumber = $user['number'];
}else{
    $number = $_POST['phone'];
    $region = $_POST['country'];
    $fullNumber = $region . $number;
}



function validatePhoneNumber($phoneNumber) {
    global $username, $conn, $twilio;
    if (preg_match('/^\+?\d{1,3}\s?\(?\d{1,4}\)?[-.\s]?\d{1,10}$/', $phoneNumber)) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if(isset($_POST['verification_code'])) {
                $userVerificationCode = trim($_POST['verification_code']);

                if ($userVerificationCode == $_SESSION['verification_code']) {
                    $stmt = $conn->prepare("UPDATE user SET number = ? WHERE username = ?");
                    $stmt->bind_param("ss", $phoneNumber, $username);
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

        function generateVerificationCode() {
            return mt_rand(100000, 999999);
        }

        $verificationCode = generateVerificationCode();
        $_SESSION['verification_code'] = $verificationCode;
        // phone message to display to user
        $message = "Your verification code is: $verificationCode";

        try {
            $twilio->messages->create(
                $phoneNumber,
                [
                    "body" => $message,
                    "from" => "TouchTree"
                ]
            );
            echo "<center>Verification code sent successfully to $phoneNumber</center>";
        } catch (Exception $e) {
            //echo "Error: " . $e->getMessage();
            $error_message = "An error has occurred while sending SMS to " . $phoneNumber . " please check number and try again";
            $_SESSION['error'] = $error_message;
            header("Location: number.php");
            exit();
        }
        return true;
    } else {
        $error_message = "Invalid phone number";
        $_SESSION['error'] = $error_message;
        header("Location: number.php");
        exit();
    }
}

// Example usage
$userPhoneNumber = $fullNumber;
if (validatePhoneNumber($userPhoneNumber)) {
    echo "";
} else {
    echo "Invalid phone number";
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
<center>
<h2>Enter Verification Code</h2>
<form action="" method="post">
    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($fullNumber); ?>"> <!-- needs to be added otherwise the number doesn't get saved and put in the query-->
    <label for="verification_code">Verification Code:</label><br>
    <input type="text" id="verification_code" name="verification_code"><br><br>
    <input type="submit" value="Submit">
</form>
</center>
</body>
</html>
