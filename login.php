<?php
require_once 'config.php';
session_start();

$page = $_GET['page'] ?? 'login';

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

// LOGIN
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($conn->real_escape_string($_POST['username']));
    $password = $_POST['password'];
    $result = $conn->query("SELECT * FROM users WHERE username='$username' LIMIT 1");
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $otp = rand(100000, 999999);
        $expire = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $conn->query("UPDATE users SET otp='$otp', otp_expire='$expire' WHERE id={$user['id']}");
        // Kirim OTP
        $send = sendOTP($user['email'], $otp);
        if ($send === true) {
            $_SESSION['pending_email'] = $user['email'];
            header("Location: ?page=verify");
            exit;
        } else {
            $error = "Gagal mengirim OTP. Coba lagi.<br><small style='color:#555;'>$send</small>";
        }
    } else {
        $error = "Username atau password salah.";
    }
}

// REGISTER
if ($page === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($conn->real_escape_string($_POST['username']));
    $email    = trim($conn->real_escape_string($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $check = $conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1");
    if ($check->num_rows > 0) {
        $error = "Email sudah digunakan.";
    } else {
        $insert = $conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')");
        if ($insert) {
            echo "<script>alert('Pendaftaran berhasil! Silakan login.');window.location='index.php';</script>";
            exit;
        } else {
            $error = "Terjadi kesalahan saat pendaftaran.";
        }
    }
}

// OTP
if ($page === 'verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['pending_email'])) {
        header("Location: index.php");
        exit;
    }
    $email = $_SESSION['pending_email'];
    $otp   = trim($_POST['otp']);
    $result = $conn->query("SELECT * FROM users WHERE email='$email' AND otp='$otp' LIMIT 1");
    $user = $result->fetch_assoc();
    if ($user) {
        if (strtotime($user['otp_expire']) > time()) {
            $_SESSION['user'] = $user['username'];
            $conn->query("UPDATE users SET otp=NULL, otp_expire=NULL WHERE id={$user['id']}");
            unset($_SESSION['pending_email']);
            echo "<script>alert('Verifikasi berhasil!');window.location='index.php';</script>";
            exit;
        } else {
            $error = "OTP sudah kadaluarsa.";
        }
    } else {
        $error = "Kode OTP salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login & OTP</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background: radial-gradient(circle at top, #0f0c29, #302b63, #24243e);
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #e0e0e0;
  overflow: hidden;
}

body::before {
  content: '';
  position: absolute;
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(0,255,255,0.3), transparent 70%);
  top: -100px;
  right: -100px;
  filter: blur(80px);
  animation: floatGlow 6s ease-in-out infinite alternate;
}

body::after {
  content: '';
  position: absolute;
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(255,0,255,0.3), transparent 70%);
  bottom: -100px;
  left: -100px;
  filter: blur(80px);
  animation: floatGlow 7s ease-in-out infinite alternate-reverse;
}

@keyframes floatGlow {
  from { transform: translateY(0px); }
  to { transform: translateY(25px); }
}

.container {
  position: relative;
  z-index: 2;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 25px;
  backdrop-filter: blur(25px);
  padding: 50px 55px;
  width: 400px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
  text-align: center;
  animation: fadeInUp 0.8s ease forwards;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.container:hover {
  transform: translateY(-6px);
  box-shadow: 0 25px 70px rgba(0, 0, 0, 0.7);
}

h2 {
  margin-bottom: 25px;
  font-weight: 700;
  font-size: 2em;
  color: #ffffff;
  letter-spacing: 1px;
  text-shadow: 0 0 15px rgba(0, 255, 255, 0.6);
}

form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

input {
  padding: 14px 18px;
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  outline: none;
  font-size: 15px;
  background: rgba(255, 255, 255, 0.07);
  color: #fff;
  transition: all 0.3s ease;
  backdrop-filter: blur(5px);
}

input::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

input:focus {
  background: rgba(255, 255, 255, 0.15);
  box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
  border-color: rgba(0, 255, 255, 0.3);
}

button {
  background: linear-gradient(135deg, #00ffff, #8a2be2);
  color: #fff;
  padding: 14px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.4s ease;
  box-shadow: 0 0 15px rgba(138, 43, 226, 0.4);
}

button:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 0 25px rgba(0, 255, 255, 0.7);
}

p {
  font-size: 14px;
  margin-top: 12px;
  color: rgba(255, 255, 255, 0.75);
}

a {
  color: #00ffff;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.3s;
}

a:hover {
  color: #8a2be2;
  text-decoration: underline;
}

.error {
  background: rgba(255, 0, 0, 0.25);
  color: #ffbaba;
  border-left: 4px solid #ff6b6b;
  padding: 12px;
  margin-bottom: 15px;
  border-radius: 10px;
  text-align: center;
  font-size: 14px;
  box-shadow: 0 0 15px rgba(255, 0, 0, 0.3);
}

@keyframes fadeInUp {
  0% { opacity: 0; transform: translateY(30px); }
  100% { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>
<div class="container">
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if ($page === 'login'): ?>
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="?page=register">Daftar</a></p>

    <?php elseif ($page === 'register'): ?>
        <h2>Daftar Akun Baru</h2>
        <form method="POST" action="?page=register">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="index.php">Login</a></p>

    <?php elseif ($page === 'verify'): ?>
        <h2>Verifikasi OTP</h2>
        <form method="POST" action="?page=verify">
            <p style="font-size:13px;text-align:center;margin-bottom:10px;">
                Masukkan kode OTP yang dikirim ke email
                <b><?= htmlspecialchars($_SESSION['pending_email']) ?></b>
            </p>
            <input type="text" name="otp" placeholder="Masukkan Kode OTP" required>
            <button type="submit">Verifikasi</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
