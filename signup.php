<?php
// Simple example; in real applications, you'd also need to handle validation, security concerns (like SQL injection prevention), and password hashing.
require_once ('./dbConfig.php');
require_once ("./header.php");// Start a new session or resume the existing one

function sanitizeHTMLString($string)
{
  global $conn;
  return $conn->real_escape_string($string);
}

function checkIfUserExists($username)
{
  global $conn;
  $sql = "SELECT * FROM users WHERE username = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  return $result->num_rows > 0;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Assuming you have a connection to your database already established
  // $username = sanitizeInput($_POST['username']); // You should sanitize and validate this
  // $name = sanitizeInput($_POST['name']); // You should sanitize and validate this
  // $email = sanitizeInput($_POST['email']); // You should sanitize and validate this
  $username = sanitizeHTMLString(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING)); // You should sanitize and validate this
  $name = sanitizeHTMLString(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING)); // You should sanitize and validate this
  $email = sanitizeHTMLString(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)); // You should sanitize and validate this

  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Always hash passwords

  if (checkIfUserExists($username)) {
    echo "<div style='display:flex; justify-content:center; margin-top:50px;'>User already exists! User a different username ! <a href='/signup.php'>Try again</a></div>";
    exit();
  }

  // Insert into your database (adjust the table and field names as necessary)
  $sql = "INSERT INTO users (username, password , email, name ) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssss", $username, $password, $email, $name);
  $stmt->execute();



  if ($stmt->affected_rows > 0) {
    echo "Signup successful!";

    session_regenerate_id();

    $_SESSION["loggedin"] = true;
    $_SESSION["username"] = $username;
    setcookie("username", $username, time() + 3600, "/");
    setcookie(session_name(), session_id(), time() + 3600, "/");
    header("location: index.php");
  } else {
    echo "An error occurred. Please try again";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <style>
    <?php include './login.css'; ?>
  </style>

  <script type="text/javascript">
    function validateForm() {
      console.log("Validating form")
      debugger;
      const name = document.getElementById('name').value;
      const email = document.getElementById('email').value;
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;

      if (name === '' || email === '' || username === '' || password === '') {
        alert("All fields are required!");
        return false;
      }

      if (!/^[a-zA-Z ]+$/.test(name)) {
        alert("Name must contain only letters and spaces!");
        return false;
      }

      if (!/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/.test(email)) {
        alert("Invalid email address!");
        return false;
      }

      /*
        The username can contain English letters (capitalized or not), digits, and the characters '_' (underscore) and '-' (dash). Nothing else.
       */

      if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
        alert("Username must contain only letters, numbers, underscores, and dashes!");
        return false;
      }

      if (!(/[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[!@#\$%\^&\*]/.test(password) && password.length >= 8)) {
        alert("Password must contain at least one lowercase letter, one uppercase letter, one digit, one special character, and be at least 8 characters long!");
        return false;
      }


      return true;
    }
  </script>
  <title>Sign Up</title>
</head>

<body>
  <div class="mainDiv">
    <div class="mainBox">
      <form method="POST" action="signup.php" class="formClass" onsubmit="return validateForm(this)">
        <h2 style="margin: 0; display:flex; justify-content:center;">Sign Up</h2>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <button type="submit">Signup</button>
      </form>
    </div>
  </div>
</body>

</html>
