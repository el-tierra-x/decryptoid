create database decryptiod ;

use decryptiod;

GRANT ALL ON decryptoid.* TO 'fileComp'@'localhost' IDENTIFIED BY 'myCompany';

CREATE TABLE IF NOT EXISTS users (
    name VARCHAR(100),
    username VARCHAR(50) PRIMARY KEY,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100)
);
 
 
CREATE TABLE texts(
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VarChar(255) NOT NULL,
  text LONGTEXT NOT NULL,
  cipher_type ENUM('Simple Substitution', 'Double Transposition', 'RC4') NOT NULL,
  action ENUM('Encrypt', 'Decrypt') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(username) REFERENCES users(username)
);
