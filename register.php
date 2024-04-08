<?php
// Check if there's an error message in the URL parameters
if(isset($_GET['error'])) {
    $error_message = $_GET['error'];
    // Display the error message to the user
    echo '<p style="color: red;">' . htmlspecialchars($error_message) . "</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
<center>
<h2>Register</h2>
<form action="register_progress.php" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>
    <input type="submit" value="Register">
</form>
<a href="login.php"><button>Back</button></a>
</center>
</body>
</html>