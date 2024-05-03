<?php
//session start needed on every page to redirect users that are NOT logged in back to main page
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
use PHPMailer\PHPMailer\PHPMailer;
require "connect.php";

//sql query to get secret and email from user for authenticator validation
$sql = "SELECT secret, email FROM user WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}

$user = $result->fetch_assoc();
$user_secret = $user['secret'];
$email = $user['email'];
$secret = $user_secret;

//creates from user_secret the correct code
$otp = TOTP::create($secret);

//checks if user input is correct
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_otp = $_POST['otp'];
    $verification_result = $otp->verify($input_otp);
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
    <!-- HTML authentication form -->
    <div class="qr_form">
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="otp">Enter 6 digit code:</label><br>
            <input type="text" id="otp" name="otp"><br>
            <input type="submit" value="Submit">
        </form>
        <a href="auth_redirect.php"><button>Back</button></a><br>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?send_qr_email=true&email=<?php echo urlencode($email); ?>">Send QR Email</a>
    </div>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']);
    }
    if (isset($_GET['send_qr_email'])) {
        if (isset($_GET['email'])) {
            $email = $_GET['email'];
            if (send_qr_email($email)) {
                $_SESSION['success_message'] = "Email sent successfully.";
                return $_SESSION['success_message'];
            } else {
                $_SESSION['error_message'] = "Failed to send email.";
            }
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // function to send new qr code with user_secret to user if the old is lost
    function send_qr_email($email) {
        global $conn;
        $config = require 'config/app.php';
        $username = $_SESSION['username'];
        $mail = new PHPMailer(true);
        $sql = "SELECT secret, email FROM user WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die("Error executing the query: " . $conn->error);
        }

        $user = $result->fetch_assoc();
        $user_secret = $user['secret'];
        $otp = TOTP::create($user_secret);
        $otp->setLabel($user['email']);
        $otp->setIssuer('TouchTree');

        // create new qr code from user_secret
        $grCodeUri = $otp->getQrCodeUri(
            'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
            '[DATA]'
        );

        try {
            $mail_FROM = $config['username'];
            $user_RCPT = $email; //get user email
            // SMTP server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.elasticemail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['smtpSecure'];
            $mail->Port = $config['port'];

            // Sender and recipient settings
            $mail->setFrom($mail_FROM);
            $mail->addAddress($user_RCPT);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'New qr code';
            $mail->Body = "<p>Requested new qr code<br><br><img src='{$grCodeUri}' alt='QR Code' class='qr_code'><br></p>";

            // Send email + error message if needed
            $mail->send();
            echo 'Email sent successfully.';
            return 1;
        } catch (Exception $e) {
            echo 'Failed to send email. Error: ' . $mail->ErrorInfo;
            return 0;
        }
    }
    ?>
</center>
</body>
</html>
