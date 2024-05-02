<?php
$hn = 'localhost'; //hostname
$db = 'decryptiod'; //database
$un = 'fileComp'; //username
$pw = 'myCompany'; //password

session_start();

$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error)
  die($conn->connect_error);

$isLoggedIn = false;

// Start a new session or resume the existing one

if (isset($_SESSION["loggedin"]) && isset($_SESSION["username"])) {
  $isLoggedIn = true;
}

