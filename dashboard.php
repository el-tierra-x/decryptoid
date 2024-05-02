<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <style>
    <?php include './login.css'; ?>
    td {
      padding: 20px;
      border: 1px solid black;
      border-radius: 10px;
    }

    tbody {
      border: 1px dotted grey;
      border-radius: 10px;
      background: aliceblue;
    }

    button {
      height: 30px;
      width: 70px;
      border-radius: 10px;
      border: 1px double;
      background: burlywood;
      cursor: pointer;
    }

    button:hover {
      background: darkorange;
    }

    input {
      height: 30px;
      border-radius: 4px;
    }

    .centerText {
      text-align: center;
    }

    .successAlign {
      color: green;
      text-align: center;
      margin-top: 20px;
      font-size: 20px;
    }
  </style>

  <script>
    function validateForm() {
      const allowedExtensions = /(\.txt)$/i;
      const file = document.getElementById('file').value;
      const key = document.getElementById('key').value;
      const type = document.getElementById('type').value;

      if (file === '' || key === '' || type === '') {
        alert("All fields are required!");
        return false;
      }

      if (!allowedExtensions.test(file)) {
        alert('Invalid file type! Allowed text files only!');
        return false;
      }

      return true;
    }
  </script>
  <title>Dashboard</title>

<body>

  <div><?php require ('./header.php'); ?></div>

  <div>
    <h2 class="centerText">Dashboard</h2>
    <h3 class="centerText">Encrypt/Decrypt file</h3>
  </div>
  <table class="tableUpload">
    <form method="post" action="dashboard.php" enctype="multipart/form-data" onsubmit="return validateForm(this)">
      <tr>
        <td>
          <label for="file">Chooose a File:</label>
        </td>
        <td>
          <input type="file" name="file" id="file" required accept=".txt" placeholder="choose a txt file">

        </td>
      </tr>
      <tr>
        <td>
          <label for="key">Key:</label>
        </td>
        <td>
          <input type="text" name="key" id="key" required placeholder="choose a key">
        </td>
      </tr>
      <tr>
        <td>
          <label for="type">Type:</label>
        </td>
        <td>
          <select name="type" id="type" required placeholder="choose a encyption mechanism">
            <option value="simpleSubstitution">Simple Substitution</option>
            <option value="doubleTransposition">Double Transposition</option>
            <option value="rc4">RC4</option>
          </select>
        </td>
      </tr>
      <tr style="display:flex;">
        <td colspan=" 2" style="display:flex; justify-content:space-evenly; width:100%">
          <button type="submit" name="encrypt">Encrypt</button>
          <button type="submit" name="decrypt">Decrypt</button>
        </td>
      </tr>
  </table>
  </head>

  <?php
  session_start();
  include ('./encrypt.php');
  require_once ('./dbConfig.php');
  if (!isset($_SESSION['username'])) {
    header('Location: login.php');
  }


  function processFileAndEncrypt($file, $key, $type)
  {
    global $conn;
    $decryptiod = new Decryptoid($key, $type, $file, $conn);

    return $decryptiod->encrypt();
  }

  function processFileAndDecrypt($file, $key, $type)
  {
    global $conn;
    $decryptiod = new Decryptoid($key, $type, $file, $conn);

    return $decryptiod->decrypt();
  }

  function storeEncryptedFile($cipher, $key, $type)
  {
    global $conn;

    $username = $_SESSION['username'];
    $actionType = 'Encrypt';

    $query = "INSERT INTO encrypted_files (username, text, cipher_type, action) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $cipher, $type, $actionType);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      echo "File stored in database!";
    } else {
      echo "An error occurred. Please try again";
    }

  }


  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $key = filter_input(INPUT_POST, 'key', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
      echo "<div class='centerText'>Please choose a file to encrypt!</div>";
      exit();
    }


    $file = $_FILES['file'];
    $fileSize = $_FILES['file']['size'];

    if ($fileSize == 0) {
      die("File is empty");
    }

    $allowed_types = array('text/plain' => 'txt');

    if (!in_array($_FILES['file']['type'], $allowed_types) && (!empty($_FILES["uploaded_file"]["type"]))) {
      die("Invalid file type! Only text files are allowed.");
    }

    if (isset($_POST['encrypt'])) {

      $result = processFileAndEncrypt($file, $key, $type);

      $filename = 'encrypted_' . $file['name'];
      file_put_contents($filename, $result);

      echo "<div class='successAlign'>File encrypted successfully! <a href='$filename' download>Download</a></div>";

    } else if (isset($_POST['decrypt'])) {

      $result = processFileAndDecrypt($file, $key, $type);

      $filename = 'decrypted_' . $file['name'];
      file_put_contents($filename, $result);

      echo "<div class='successAlign'>File decrypted successfully! <a href='$filename' download>Download</a></div>";
    }
  }

  ?>


</body>

</html>
