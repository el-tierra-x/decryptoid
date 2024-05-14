<?php
// Simple example; in real applications, you'd also need to handle validation, security concerns (like SQL injection prevention), and password hashing.
require_once ('./dbConfig.php');
require_once ("./header.php");// Start a new session or resume the existing one
$userAlreadyExists = false;

if ($isLoggedIn) {
  header("location: index.php");
  exit;
}

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

  return ($result->num_rows) > 0;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Assuming you have a connection to your database already established
  // $username = sanitizeInput($_POST['username']); // You should sanitize and validate this
  // $name = sanitizeInput($_POST['name']); // You should sanitize and validate this
  // $email = sanitizeInput($_POST['email']); // You should sanitize and validate this
  $username = sanitizeHTMLString(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING)); // You should sanitize and validate this
  $name = sanitizeHTMLString(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING)); // You should sanitize and validate this
  $email = sanitizeHTMLString(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)); // You should sanitize and validate this

  //check if username is correct format

  if (preg_match('/^[a-zA-Z0-9_-]+$/', $username) === 0) {
    echo "Username must contain only letters, numbers, underscores, and dashes!";
    exit();
  }

  if (preg_match('/^[a-zA-Z ]+$/', $name) === 0) {
    echo "Name must contain only letters and spaces!";
    exit();
  }

  if (preg_match('/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/', $email) === 0) {
    echo "Invalid email address!";
    exit();
  }

  if (preg_match('/[a-z]/', $_POST['password']) === 0 || preg_match('/[A-Z]/', $_POST['password']) === 0 || preg_match('/[0-9]/', $_POST['password']) === 0 || preg_match('/[!@#\$%\^&\*]/', $_POST['password']) === 0 || strlen($_POST['password']) < 8) {
    echo "Password must contain at least one lowercase letter, one uppercase letter, one digit, one special character, and be at least 8 characters long!";
    exit();
  }

  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Always hash passwords

  if (checkIfUserExists($username)) {
    $userAlreadyExists = true;
    // exit();
  } else {

    // Insert into your database (adjust the table and field names as necessary)
    $sql = "INSERT INTO users (username, password , email, name ) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $email, $name);
    $stmt->execute();



    if ($stmt->affected_rows > 0) {
      echo "Signup successful!";


      $_SESSION["loggedin"] = true;
      $_SESSION["username"] = $username;
      setcookie("username", $username, time() + 3600, "/");
      setcookie(session_name(), session_id(), time() + 3600, "/");
      header("location: index.php");
    } else {
      echo "An error occurred. Please try again";
    }
  }
}
?>

<!DOCTYPE html>
< lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Decryptoid - Encrypt and decrypt </title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <!--Stylesheet-->
    <style media="screen">
      *,
      *:before,
      *:after {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
      }

      body {
        background-color: #080710;
      }

      .background {
        width: 430px;
        height: 520px;
        position: absolute;
        transform: translate(-50%, -50%);
        left: 50%;
        top: 50%;
      }

      .background .shape {
        height: 200px;
        width: 200px;
        position: absolute;
        border-radius: 50%;
      }

      .shape:first-child {
        background: linear-gradient(#1845ad,
            #23a2f6);
        left: -80px;
        top: -80px;
      }

      .shape:last-child {
        background: linear-gradient(to right,
            #ff512f,
            #f09819);
        right: -30px;
        bottom: -80px;
      }

      form {
        height: fit-content;
        width: 400px;
        background-color: rgba(255, 255, 255, 0.13);
        position: relative;
        transform: translate(-50%, -50%);
        top: 40%;
        left: 50%;
        border-radius: 10px;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 40px rgba(8, 7, 16, 0.6);
        padding: 50px 35px;
      }

      form * {
        font-family: 'Poppins', sans-serif;
        color: #ffffff;
        letter-spacing: 0.5px;
        outline: none;
        border: none;
      }

      form h3 {
        font-size: 32px;
        font-weight: 500;
        line-height: 42px;
        text-align: center;
      }

      label {
        display: block;
        margin-top: 30px;
        font-size: 16px;
        font-weight: 500;
      }

      input {
        display: block;
        height: 50px;
        width: 100%;
        background-color: rgba(255, 255, 255, 0.07);
        border-radius: 3px;
        padding: 0 10px;
        margin-top: 8px;
        font-size: 14px;
        font-weight: 300;
      }

      ::placeholder {
        color: #e5e5e5;
      }

      button {
        margin-top: 50px;
        width: 100%;
        background-color: #ffffff;
        color: #080710;
        padding: 15px 0;
        font-size: 18px;
        font-weight: 600;
        border-radius: 5px;
        cursor: pointer;
      }

      .social {
        margin-top: 30px;
        display: flex;
      }

      .social div {
        background: red;
        width: 150px;
        border-radius: 3px;
        padding: 5px 10px 10px 5px;
        background-color: rgba(255, 255, 255, 0.27);
        color: #eaf0fb;
        text-align: center;
      }

      .social div:hover {
        background-color: rgba(255, 255, 255, 0.47);
      }

      .social .fb {
        margin-left: 25px;
      }

      .social i {
        margin-right: 4px;
      }

      <?php require_once ('./login.css'); ?>
    </style>

    <script type="text/javascript">
      function validateForm() {
        let noError = true;
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (name === '' || email === '' || username === '' || password === '') {
          alert("All fields are required!");
          ("All fields are required!");
          noError = false;
        }

        if (!/^[a-zA-Z ]+$/.test(name)) {
          const nameError = document.getElementById('nameError');
          nameError.innerHTML = "Name must contain only letters and spaces!";
          nameError.style.color = 'red';
          noError = false;
        }

        if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
          const emailError = document.getElementById('emailError');
          emailError.innerHTML = "Invalid email address!";
          emailError.style.color = 'red';

          noError = false;
        }

        /*
          The username can contain English letters (capitalized or not), digits, and the characters '_' (underscore) and '-' (dash). Nothing else.
         */

        if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
          const usernameError = document.getElementById('usernameError');
          usernameError.innerHTML = "Username must contain only letters, numbers, underscores, and dashes!";
          usernameError.style.color = 'red';
          noError = false;
        }

        if (!(/[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[!@#\$%\^&\*]/.test(password) && password.length >= 8)) {
          const passwordError = document.getElementById('passwordError');
          passwordError.innerHTML = "Password must contain at least one lowercase letter, one uppercase letter, one digit, one special character, and be at least 8 characters long!";
          passwordError.style.color = 'red';
          noError = false;
        }


        return noError;
      }
    </script>
    <title>Sign Up</title>
  </head>

  <body>

    <?php require_once ('./header.php'); ?>
    <div class="background">
      <div class="shape"></div>
      <div class="shape"></div>
    </div>
    <form method="POST" action="signup.php" onsubmit="return validateForm(this)">
      <h3>Decryptiod Signup</h3>

      <label for="username">Username</label>
      <input type="text" placeholder="Username" id="username" required name="username">
      <span id="usernameError"></span>
      <label for="password">Password</label>
      <input type="password" placeholder="Password" id="password" required name="password">
      <span id="passwordError"></span>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required placeholder="email address">
      <span id="emailError"></span>
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" required placeholder="name">
      <span id="nameError"></span>

      <button type="submit">Signup</button>
      <?php if ($userAlreadyExists) {
        echo "<div classname='errorCenter' style='color:red;'>User already exists! User a different username ! <a href='/signup.php'>Try again</a></div>";
      }
      ?>


    </form>

  </body>

  </html>
