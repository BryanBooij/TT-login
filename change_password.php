<?php
//session start needed on every page to redirect users that are NOT logged in back to main page
global $conn;
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page
    header("Location: login.php");
    exit;
}
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all fields are filled
    if (isset($_POST['username'], $_POST['old_password'], $_POST['new_password'])) {
        $username = $_POST['username'];
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];

        // Sanitize inputs
        $username = mysqli_real_escape_string($conn, $username);
        $old_password = mysqli_real_escape_string($conn, $old_password);
        $new_password = mysqli_real_escape_string($conn, $new_password);

        // Fetch user from the database
        $sql = "SELECT * FROM user WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $hashed_password = $row['password'];

            // Verify old password
            if (password_verify($old_password, $hashed_password)) {
                // Hash the new password
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password
                $update_sql = "UPDATE user SET password = '$hashed_new_password' WHERE username = '$username'";
                if (mysqli_query($conn, $update_sql)) {
                    echo "<div class='message success'>Password updated successfully!</div>";
                } else {
                    echo "<div class='message error'>Error updating password: " . mysqli_error($conn) . "</div>";
                }
            } else {
                echo "<div class='message error'>Invalid old password!</div>";
            }
        } else {
            echo "<div class='message error'>Invalid username!</div>";
        }
    } else {
        echo "<div class='message error'>All fields are required!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style/stylesheet.css">
    <title>Change Password</title>
</head>
<body>
<center>
<h1 class="title">Change Password</h1>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label>Username:</label><br>
    <input type="text" id="username" name="username" value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" readonly>
    <br><br>
    <label>Old Password:</label><br>
    <input type="password" name="old_password"><br><br>
    <label>New Password:</label><br>
    <input type="password" name="new_password"><br><br>
    <input type="submit" value="Submit">
</form>
<a href="home.php"><button>Back</button></a>
</center>
</body>
</html>
