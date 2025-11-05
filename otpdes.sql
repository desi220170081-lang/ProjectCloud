CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50),
  email VARCHAR(100),
  password VARCHAR(255),
  otp VARCHAR(10),
  otp_expire DATETIME,
  verified TINYINT(1) DEFAULT 0
);
