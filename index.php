<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$query = $conn->prepare("SELECT username, email, otp_expire FROM users WHERE username = ?");
$query->bind_param("s", $user);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard - Sistem Login OTP</title>
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
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(0,255,255,0.3), transparent 70%);
  top: -100px;
  right: -100px;
  filter: blur(80px);
  animation: floatGlow 6s ease-in-out infinite alternate;
}

body::after {
  content: '';
  position: absolute;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(138,43,226,0.3), transparent 70%);
  bottom: -100px;
  left: -100px;
  filter: blur(80px);
  animation: floatGlow 7s ease-in-out infinite alternate-reverse;
}

@keyframes floatGlow {
  from { transform: translateY(0px); }
  to { transform: translateY(25px); }
}

.dashboard-container {
  position: relative;
  z-index: 2;
  background: rgba(255, 255, 255, 0.07);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 25px;
  backdrop-filter: blur(25px);
  padding: 55px 45px;
  width: 420px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
  animation: fadeInUp 0.8s ease forwards;
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dashboard-container:hover {
  transform: translateY(-6px);
  box-shadow: 0 25px 70px rgba(0, 0, 0, 0.7);
}

h2 {
  font-weight: 700;
  font-size: 2em;
  margin-bottom: 10px;
  color: #ffffff;
  letter-spacing: 1px;
  text-shadow: 0 0 15px rgba(0, 255, 255, 0.6);
}

.subtext {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.75);
  margin-bottom: 25px;
}

.content {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 18px;
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: inset 0 0 15px rgba(255,255,255,0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.content:hover {
  box-shadow: inset 0 0 20px rgba(0,255,255,0.15);
}

.content p {
  color: rgba(255, 255, 255, 0.9);
  font-size: 15px;
  margin-bottom: 10px;
  line-height: 1.6;
}

.logout-btn {
  background: linear-gradient(135deg, #00ffff, #8a2be2);
  color: #fff;
  padding: 12px 30px;
  border-radius: 12px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.4s ease;
  display: inline-block;
  box-shadow: 0 0 15px rgba(138, 43, 226, 0.4);
}

.logout-btn:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 0 25px rgba(0, 255, 255, 0.7);
}

@keyframes fadeInUp {
  0% { opacity: 0; transform: translateY(30px); }
  100% { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<div class="dashboard-container">
    <h2>Selamat Datang 🎉</h2>
    <p class="subtext"><?= htmlspecialchars($data['username']) ?> | <?= htmlspecialchars($data['email']) ?></p>

    <div class="content">
        <p>Anda berhasil login dengan sistem <b>verifikasi OTP</b>.</p>
    </div>

    <a href="logout.php" class="logout-btn">Logout</a>
</div>

</body>
</html>