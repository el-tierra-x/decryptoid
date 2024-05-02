<?php
require_once ('./dbConfig.php');

?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .header {
      background-color: #f3f3f3;
      padding: 20px;
      text-align: center;
    }

    .button {
      padding: 10px 20px;
      margin: 5px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
    }

    .button:hover {
      background-color: #0056b3;
    }
  </style>
</head>

<div class="header">
  <?php if ($isLoggedIn) { ?>
    <h1>Welcome, <?php echo $_SESSION["username"]; ?>!</h1>
    <form method="POST" action="logout.php">
      <button type="submit" name="logout" class="button">Logout</button>
    </form>
    <?php
  } else { ?>
    <h1>Welcome!</h1>
    <a href="/login.php" class="button">Login</a>
    <a href="/signup.php" class="button">Sign Up</a>
  </div>
<?php } ?>

