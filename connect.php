<?php
//connection to local database change to online database if needed
$config = require 'config/app.php';

// database login information for connection
$servername = $config['servername'];
$Gusername = $config['usernamelocalhost'];
$Gpassword = $config['passwordlocalhost'];
$database = $config['database'];

$conn = new mysqli($servername, $Gusername, $Gpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

