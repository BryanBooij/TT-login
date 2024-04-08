<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "users";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
        $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$old_password'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            // Update the password
            $update_sql = "UPDATE user SET password = '$new_password' WHERE username = '$username'";
            if (mysqli_query($conn, $update_sql)) {
                echo "Password updated successfully!";
            } else {
                echo "Error updating password: " . mysqli_error($conn);
            }
        } else {
            echo "Invalid username or old password!";
        }
    } else {
        echo "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>
<h2>Change Password</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label>Username:</label><br>
    <input type="text" name="username"><br><br>
    <label>Old Password:</label><br>
    <input type="password" name="old_password"><br><br>
    <label>New Password:</label><br>
    <input type="password" name="new_password"><br><br>
    <input type="submit" value="Submit">
</form>
<a href="home.php">Back</a>
</body>
</html>
