<?php
session_start();
if(isset($_SESSION['role'])){ header("Location: index.php"); exit; }
include('db.php');
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name']  ?? '');
    $age    = intval($_POST['age']      ?? 0);
    $email  = trim($_POST['email']      ?? '');
    $pass   = trim($_POST['password']   ?? '');
    $conf   = trim($_POST['confirm']    ?? '');

    if(!$first || !$last || !$email || !$pass){
        $error = 'กรุณากรอกข้อมูลที่จำเป็น';
    } elseif($pass !== $conf){
        $error = 'Password และ Confirm Password ไม่ตรงกัน';
    } elseif(strlen($pass) < 6){
        $error = 'Password ต้องมีอย่างน้อย 6 ตัวอักษร';
    } else {
        $em = mysqli_real_escape_string($conn,$email);
        $dup = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE email='$em'"));
        if($dup['c'] > 0){
            $error = "Email นี้ถูกใช้งานแล้ว";
        } else {
            $fn = mysqli_real_escape_string($conn,$first);
            $ln = mysqli_real_escape_string($conn,$last);
            $pw = mysqli_real_escape_string($conn,$pass);
            mysqli_query($conn,"INSERT INTO users(first_name,last_name,age,email,password) VALUES('$fn','$ln',$age,'$em','$pw')");
            $newId = mysqli_insert_id($conn);
            $_SESSION['user_id']   = $newId;
            $_SESSION['fullname']  = "$first $last";
            $_SESSION['email']     = $email;
            $_SESSION['role']      = 'member';
            $_SESSION['member_id'] = $newId;
            header("Location: index.php"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>สมัครสมาชิก - OPAC Library</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 50%,#ec4899 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
.wrap{width:100%;max-width:460px;}
.logo{text-align:center;color:white;margin-bottom:1.5rem;}
.logo i{font-size:2.5rem;margin-bottom:.4rem;display:block;}
.logo h1{font-size:1.6rem;font-weight:800;}
.card{background:white;border-radius:22px;padding:2rem;box-shadow:0 25px 50px rgba(0,0,0,.2);}
.card h2{font-size:1.2rem;font-weight:700;margin-bottom:1.25rem;color:#1e293b;}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.82rem;font-weight:600;margin-bottom:.35rem;color:#374151;}
.input-wrap{position:relative;}
.input-wrap i.icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.85rem;}
.form-control{width:100%;padding:.65rem .9rem .65rem 2.3rem;border:2px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;transition:border-color .2s;color:#1e293b;}
.form-control:focus{border-color:#4f46e5;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.btn-submit{width:100%;padding:.8rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border:none;border-radius:12px;font-size:.95rem;font-weight:700;cursor:pointer;margin-top:.5rem;}
.alert-error{background:#fee2e2;color:#991b1b;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.85rem;}
.footer{text-align:center;margin-top:1rem;font-size:.85rem;color:#64748b;}
.footer a{color:#4f46e5;font-weight:600;}
.section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:.75rem 0 .5rem;padding-bottom:.4rem;border-bottom:1px solid #f1f5f9;}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo"><i class="fas fa-book-open"></i><h1>OPAC Library</h1></div>
  <div class="card">
    <h2><i class="fas fa-user-plus" style="color:#4f46e5;margin-right:.4rem"></i>สมัครสมาชิก</h2>
    <?php if($error): ?><div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
    <form method="post">
      <div class="section-title">ข้อมูลส่วนตัว</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">ชื่อ *</label>
          <div class="input-wrap"><i class="fas fa-user icon"></i><input name="first_name" class="form-control" placeholder="ชื่อ" value="<?= htmlspecialchars($_POST['first_name']??'') ?>" required></div>
        </div>
        <div class="form-group">
          <label class="form-label">นามสกุล *</label>
          <div class="input-wrap"><i class="fas fa-user icon"></i><input name="last_name" class="form-control" placeholder="นามสกุล" value="<?= htmlspecialchars($_POST['last_name']??'') ?>" required></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">อายุ</label>
          <div class="input-wrap"><i class="fas fa-birthday-cake icon"></i><input type="number" name="age" class="form-control" placeholder="อายุ" value="<?= htmlspecialchars($_POST['age']??'') ?>" min="1" max="120"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Email *</label>
          <div class="input-wrap"><i class="fas fa-envelope icon"></i><input type="email" name="email" class="form-control" placeholder="email@example.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required></div>
        </div>
      </div>
      <div class="section-title">ตั้งรหัสผ่าน</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password *</label>
          <div class="input-wrap"><i class="fas fa-lock icon"></i><input type="password" name="password" class="form-control" placeholder="อย่างน้อย 6 ตัว" required></div>
        </div>
        <div class="form-group">
          <label class="form-label">ยืนยัน Password *</label>
          <div class="input-wrap"><i class="fas fa-lock icon"></i><input type="password" name="confirm" class="form-control" placeholder="พิมพ์ซ้ำ" required></div>
        </div>
      </div>
      <button type="submit" class="btn-submit"><i class="fas fa-user-plus"></i> สมัครสมาชิก</button>
    </form>
    <div class="footer">มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a> &nbsp;|&nbsp; <a href="books.php">ดูหนังสือก่อน</a></div>
  </div>
</div>
</body></html>
