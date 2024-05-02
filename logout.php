<?php

require_once ('./dbConfig.php');

function onLogout()
{
  $_SESSION = array();
  setcookie(session_name(), '', time() - 3600);
  session_destroy();
  header("location: login.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" & isset($_POST['logout'])) {
  onLogout();
}


?>

