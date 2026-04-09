<?php
$servername = "localhost";
$username = "root"; // your phpMyAdmin username
$password = "";     // your phpMyAdmin password
$dbname = "myprofile";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
