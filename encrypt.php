<?php
class Decryptoid
{
  private $text;
  private $key;
  private $type;
  private $file;
  private $conn;
  private $lowercase = [
    "a",
    "b",
    "c",
    "d",
    "e",
    "f",
    "g",
    "h",
    "i",
    "j",
    "k",
    "l",
    "m",
    "n",
    "o",
    "p",
    "q",
    "r",
    "s",
    "t",
    "u",
    "v",
    "w",
    "x",
    "y",
    "z"
  ];
  private $uppercase = [
    "A",
    "B",
    "C",
    "D",
    "E",
    "F",
    "G",
    "H",
    "I",
    "J",
    "K",
    "L",
    "M",
    "N",
    "O",
    "P",
    "Q",
    "R",
    "S",
    "T",
    "U",
    "V",
    "W",
    "X",
    "Y",
    "Z"
  ];

  private $encryptedString = "";
  private $decryptedString = "";

  public function __construct($key, $type, $file, $conn)
  {
    $this->key = $key;
    $this->type = $type;
    $this->file = $file;
    $this->conn = $conn;
    $this->text = $this->processAndGetFileContents();

  }

  function escape_file_data($content)
  {
    return $this->conn->real_escape_string($content);

  }

  public function processAndGetFileContents()
  {
    $fileName = $this->file['tmp_name'];
    $text = file_get_contents($fileName);

    return $this->escape_file_data($text);
  }

  public function encrypt()
  {
    switch ($this->type) {
      case 'simpleSubstitution':
        return $this->simpleSubstitionEncrypt();

      case 'doubleTransposition':
        return $this->doubleTranspositionEncrypt();

      case 'rc4':
        return $this->rc4();
    }
  }

  public function decrypt()
  {
    switch ($this->type) {
      case 'simpleSubstitution':
        return $this->simpleSubstitionDecrypt();

      case 'doubleTransposition':
        return $this->doubleTranspositionDecrypt();

      case 'rc4':
        return $this->rc4();
    }
  }

  public function simpleSubstitionEncrypt()
  {
    if (isset($_POST['encrypt'])) {
      for ($i = 0; $i < strlen($this->text); $i++) {
        $letter = substr($this->text, $i, 1);
        if (in_array($letter, $this->lowercase)) {
          $index = array_search($letter, $this->lowercase);
          $newString = $newString . $this->lowercase[($index + $this->key) % 26];
        } else if (in_array($letter, $this->uppercase)) {
          $index = array_search($letter, $this->uppercase);
          $newString = $newString . $this->uppercase[($index + $this->key) % 26];
        } else {
          $newString = $newString . " ";
        }
      }
    }
    $this->encryptedString = $newString;
    return $newString;
  }

  public function simpleSubstitionDecrypt()
  {
    $newString = "";
    for ($i = 0; $i < strlen($this->text); $i++) {
      $letter = substr($this->text, $i, 1);
      if (in_array($letter, $this->lowercase)) {
        $index = array_search($letter, $this->lowercase);
        if ($index - $this->key >= 0) {
          $newString = $newString . $this->lowercase[$index - $this->key];
        } else {
          $posIndex = ($index - $this->key) * -1;
          $newString = $newString . $this->lowercase[25 - ($posIndex % 26)];
        }
      } else if (in_array($letter, $this->uppercase)) {
        $index = array_search($letter, $this->uppercase);
        if ($index - $this->key >= 0) {
          $newString = $newString . $this->uppercase[$index - $this->key];
        } else {
          $posIndex = ($index - $this->key) * -1;
          $newString = $newString . $this->uppercase[25 - ($posIndex % 26)];
        }
      } else {
        $newString = $newString . " ";
      }
    }
    $this->decryptedString = $newString;
    return $newString;
  }

  function doubleTranspositionEncrypt()
  {
    if ($this->key <= 1)
      return $this->text;

    $rail = array_fill(0, $this->key, array_fill(0, strlen($this->text), null));
    $dir_down = false;
    $row = 0;
    $col = 0;

    // Fill the rail matrix
    for ($i = 0; $i < strlen($this->text); $i++) {
      if ($row == 0 || $row == $this->key - 1) {
        $dir_down = !$dir_down;
      }
      $rail[$row][$col++] = $this->text[$i];

      if ($dir_down) {
        $row++;
      } else {
        $row--;
      }
    }

    // Read the matrix in row-major order to form the encrypted string
    $result = '';
    for ($i = 0; $i < $this->key; $i++) {
      for ($j = 0; $j < strlen($this->text); $j++) {
        if ($rail[$i][$j] !== null) {
          $result .= $rail[$i][$j];
        }
      }
    }

    return $result;
  }

  function doubleTranspositionDecrypt()
  {
    if ($this->key <= 1)
      return $this->text;

    $rail = array_fill(0, $this->key, array_fill(0, strlen($this->text), null));
    $dir_down = null;
    $row = 0;
    $col = 0;

    // Mark the positions in the rail matrix
    for ($i = 0; $i < strlen($this->text); $i++) {
      if ($row == 0) {
        $dir_down = true;
      }
      if ($row == $this->key - 1) {
        $dir_down = false;
      }
      $rail[$row][$col++] = '*';

      if ($dir_down) {
        $row++;
      } else {
        $row--;
      }
    }

    // Fill the rail matrix
    $index = 0;
    for ($i = 0; $i < $this->key; $i++) {
      for ($j = 0; $j < strlen($this->text); $j++) {
        if ($rail[$i][$j] == '*' && $index < strlen($this->text)) {
          $rail[$i][$j] = $this->text[$index++];
        }
      }
    }

    // Read the matrix along the rail path
    $result = '';
    $row = 0;
    $col = 0;
    for ($i = 0; $i < strlen($this->text); $i++) {
      if ($row == 0) {
        $dir_down = true;
      }
      if ($row == $this->key - 1) {
        $dir_down = false;
      }
      if ($rail[$row][$col] != '*') {
        $result .= $rail[$row][$col++];
      }
      if ($dir_down) {
        $row++;
      } else {
        $row--;
      }
    }

    return $result;
  }


  function rc4()
  {
    // Convert input strings to arrays of ASCII values
    $key = array_map('ord', str_split($this->key));
    $data = array_map('ord', str_split($this->text));

    // Prepare the state vector
    $state = range(0, 255);
    $len = count($key);
    $index1 = $index2 = 0;

    // Initial permutation of state
    for ($counter = 0; $counter < 256; $counter++) {
      $index2 = ($key[$index1] + $state[$counter] + $index2) % 256;
      $tmp = $state[$counter];
      $state[$counter] = $state[$index2];
      $state[$index2] = $tmp;
      $index1 = ($index1 + 1) % $len;
    }

    // RC4 Generation
    $x = $y = 0;
    $len = count($data);
    for ($counter = 0; $counter < $len; $counter++) {
      $x = ($x + 1) % 256;
      $y = ($state[$x] + $y) % 256;
      $tmp = $state[$x];
      $state[$x] = $state[$y];
      $state[$y] = $tmp;
      $data[$counter] ^= $state[($state[$x] + $state[$y]) % 256];
    }

    // Convert output back to a string
    $dataStr = implode('', array_map('chr', $data));
    return $dataStr;
  }


}
