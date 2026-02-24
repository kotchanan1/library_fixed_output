<?php
session_start();
if(isset($_SESSION['role'])){
    if($_SESSION['role'] === 'admin') header("Location: admin_home.php");
    else header("Location: index.php");
    exit;
}
$error    = $_GET['error']    ?? '';
$redirect = $_GET['redirect'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>เข้าสู่ระบบ - OPAC Library</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 50%,#ec4899 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;}
.wrap{width:100%;max-width:400px;}
.logo{text-align:center;color:white;margin-bottom:1.75rem;}
.logo i{font-size:3rem;margin-bottom:.5rem;display:block;}
.logo h1{font-size:1.8rem;font-weight:800;}
.logo p{opacity:.8;font-size:.875rem;margin-top:.2rem;}
.card{background:white;border-radius:24px;padding:2.25rem;box-shadow:0 25px 50px rgba(0,0,0,.2);}
.card h2{font-size:1.2rem;font-weight:700;margin-bottom:1.4rem;color:#1e293b;}
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;color:#374151;}
.input-wrap{position:relative;}
.input-wrap i{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.85rem;}
.form-control{width:100%;padding:.7rem .9rem .7rem 2.4rem;border:2px solid #e2e8f0;border-radius:12px;font-size:.9rem;outline:none;transition:border-color .2s;color:#1e293b;}
.form-control:focus{border-color:#4f46e5;}
.btn-login{width:100%;padding:.85rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:all .2s;margin-top:.5rem;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(79,70,229,.4);}
.alert-error{background:#fee2e2;color:#991b1b;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.85rem;display:flex;align-items:center;gap:.5rem;border:1px solid #fecaca;}
.alert-info{background:#dbeafe;color:#1e40af;padding:.65rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.82rem;display:flex;align-items:center;gap:.5rem;border:1px solid #bfdbfe;}
.demo-accounts{margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;}
.demo-title{font-size:.72rem;color:#94a3b8;margin-bottom:.55rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
.demo-btn{display:block;width:100%;padding:.5rem .85rem;border:1.5px dashed #e2e8f0;border-radius:8px;font-size:.82rem;color:#64748b;cursor:pointer;text-align:left;background:transparent;margin-bottom:.4rem;transition:all .2s;}
.demo-btn:hover{border-color:#4f46e5;color:#4f46e5;background:#f5f3ff;}
.footer{text-align:center;margin-top:1.1rem;font-size:.85rem;color:#64748b;}
.footer a{color:#4f46e5;font-weight:600;}
.back-link{display:inline-flex;align-items:center;gap:.4rem;color:rgba(255,255,255,.8);font-size:.85rem;margin-bottom:1rem;text-decoration:none;}
.back-link:hover{color:white;}
</style>
</head>
<body>
<div class="wrap">
  <a href="books.php" class="back-link"><i class="fas fa-arrow-left"></i> กลับไปดูหนังสือก่อน</a>
  <div class="logo">
    <i class="fas fa-book-open"></i>
    <h1>OPAC Library</h1>
    <p>ระบบห้องสมุดออนไลน์</p>
  </div>
  <div class="card">
    <h2><i class="fas fa-sign-in-alt" style="color:#4f46e5;margin-right:.4rem"></i>เข้าสู่ระบบ</h2>

    <?php if($redirect): ?>
    <div class="alert-info"><i class="fas fa-info-circle"></i> กรุณาเข้าสู่ระบบก่อนเพื่อยืมหนังสือ</div>
    <?php endif; ?>
    <?php if($error === 'wrong'): ?>
    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> Email หรือ Password ไม่ถูกต้อง</div>
    <?php elseif($error === 'empty'): ?>
    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> กรุณากรอก Email และ Password</div>
    <?php endif; ?>

    <form action="check_login.php" method="post">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-wrap">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" class="form-control" placeholder="กรอก email" required autofocus>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" class="form-control" placeholder="กรอก password" required>
        </div>
      </div>
      <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</button>
    </form>

    <div class="demo-accounts">
      <div class="demo-title">บัญชีทดสอบ</div>
      <button class="demo-btn" onclick="fill('admin002@gmail.com','admin123456')">👑 <strong>Admin:</strong> admin002@gmail.com / admin123456</button>
      <button class="demo-btn" onclick="fill('a@mail.com','12345678')">👤 <strong>สมาชิก:</strong> a@mail.com / 12345678</button>
    </div>
    <div class="footer">
      ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกฟรี</a>
    </div>
  </div>
</div>
<script>
function fill(e,p){
  document.querySelector('[name=email]').value = e;
  document.querySelector('[name=password]').value = p;
}
</script>
</body></html>
