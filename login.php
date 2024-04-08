<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>loginform</title>
</head>
<body>
<center>
    <form action="connect.php" method="post">
        <h1>Sign in</h1>
        <div class="username">
            <label for="username" class="username">Username: </label>
            <input type="text" name="username" id="username">
        </div>
        <div class="password">
            <label for="password" class="password">Password: </label>
            <input type="password" id="password" name="password">
        </div>
        <div>
            <button type="submit">Login</button>
        </div>
    </form>
    <a href="register.php"><button>register</button></a>
    <a href="google.php"><button>Register using google</button></a>
    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']);
    }
    ?>
</center>
</body>
</html>
