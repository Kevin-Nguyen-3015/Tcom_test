<?php
$servername = "mysql";
$username = "laravel_user";
$password = "laravel_password";
$dbname = "booking_tcom";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
