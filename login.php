<!DOCTYPE html>
<html lang="en">


<head>
  <?php
  session_start();

  require_once ("./header.php");// Start a new session or resume the existing one
  ?>
  <meta charset="UTF-8">
  <style>
    <?php include './login.css'; ?>
  </style>
  <title>Login</title>
</head>

<body>
  <div class="mainDiv">
    <div class="mainBox">
      <form method="POST" action="login.php" class="formClass">
        <h2 style="margin: 0; display:flex; justify-content:center;">Login</h2>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
      </form>
    </div>
  </div>

  <?php

  require_once ("./dbConfig.php");

  if ($isLoggedIn) {
    header("location: index.php");
    exit;
  }
  function sanitizeInput($data)
  {
    $data = trim($data);
    $data = sanitizeHTMLString($data);

    if (get_magic_quotes_gpc()) {
      $data = stripslashes($data);
    }
    return $data;
  }

  function sanitizeHTMLString($string)
  {
    global $conn;
    return $conn->real_escape_string($string);
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Check credentials (this is a simplified example; always use prepared statements to prevent SQL injection)
    $sql = "SELECT username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      $data = password_verify($password, $user['password']);
      echo $data;
      if (password_verify($password, $user['password'])) {
        // Password is correct, so start a new session
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;

        setcookie("username", $username, time() + 3600, "/");
        setcookie("loggedin", true, time() + 3600, "/");
        setcookie("session_id", session_id(), time() + 3600, "/");
        header("location: index.php");

      } else {
        // Display an error message if password is not valid
        echo "The password you entered was not valid.";
      }
    } else {
      // Display an error message if username doesn't exist
      echo "No account found with that username.";
    }
  }
  ?>


</body>

</html>
