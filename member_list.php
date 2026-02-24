<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php"); exit;
}
include('db.php');
$msg = ''; $msg_type = 'success';

if($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='add'){
    $fn = mysqli_real_escape_string($conn,$_POST['first_name']);
    $ln = mysqli_real_escape_string($conn,$_POST['last_name']);
    $ag = intval($_POST['age']);
    $em = mysqli_real_escape_string($conn,$_POST['email']);
    $pw = mysqli_real_escape_string($conn,$_POST['password']);
    $dup = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE email='$em'"));
    if($dup['c'] > 0){ $msg = "❌ Email นี้ถูกใช้แล้ว"; $msg_type = 'danger'; }
    else {
        mysqli_query($conn,"INSERT INTO users(first_name,last_name,age,email,password) VALUES('$fn','$ln',$ag,'$em','$pw')");
        $msg = "✅ เพิ่มสมาชิกเรียบร้อย";
    }
}
if($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='delete'){
    $id = (int)$_POST['user_id'];
    mysqli_query($conn,"DELETE FROM users WHERE user_id=$id");
    $msg = "🗑️ ลบสมาชิกเรียบร้อย"; $msg_type = 'warning';
}

$members = mysqli_query($conn,"SELECT * FROM users ORDER BY user_id DESC");
$page_title = 'จัดการสมาชิก';
include('header.php');
?>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-users"></i> จัดการสมาชิก</div>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalAdd').classList.add('open')"><i class="fas fa-plus"></i> เพิ่มสมาชิก</button>
  </div>
</div>
<div class="container">
<?php if($msg): ?><div class="alert alert-<?= $msg_type ?>"><i class="fas fa-info-circle"></i> <?= $msg ?></div><?php endif; ?>

<div class="modal-overlay" id="modalAdd">
  <div class="modal-box">
    <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')"><i class="fas fa-times"></i></button>
    <div class="modal-title"><i class="fas fa-user-plus" style="color:#4f46e5"></i> เพิ่มสมาชิกใหม่</div>
    <form method="post">
      <input type="hidden" name="action" value="add">
      <div class="form-row">
        <div class="form-group"><label class="form-label">ชื่อ *</label><input name="first_name" class="form-control" required></div>
        <div class="form-group"><label class="form-label">นามสกุล *</label><input name="last_name" class="form-control" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">อายุ</label><input type="number" name="age" class="form-control" min="1"></div>
        <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
      </div>
      <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required></div>
      <button type="submit" class="btn btn-primary" style="width:100%"><i class="fas fa-save"></i> เพิ่มสมาชิก</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title"><i class="fas fa-list" style="color:#4f46e5"></i> รายชื่อสมาชิกทั้งหมด</div></div>
  <div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>#</th><th>ชื่อ</th><th>นามสกุล</th><th>อายุ</th><th>Email</th><th>Role</th><th>จัดการ</th></tr></thead>
    <tbody>
    <?php while($m = mysqli_fetch_assoc($members)): ?>
    <?php $isAdmin = strtolower($m['first_name']) === 'admin'; ?>
    <tr>
      <td class="text-muted"><?= $m['user_id'] ?></td>
      <td><strong><?= htmlspecialchars($m['first_name']) ?></strong></td>
      <td><?= htmlspecialchars($m['last_name']) ?></td>
      <td class="text-muted"><?= $m['age'] ?? '–' ?></td>
      <td class="text-muted"><?= htmlspecialchars($m['email']) ?></td>
      <td><span class="badge <?= $isAdmin ? 'badge-purple':'badge-blue' ?>"><?= $isAdmin ? 'admin':'member' ?></span></td>
      <td>
        <?php if($m['user_id'] !== (int)$_SESSION['user_id']): ?>
        <form method="post" style="display:inline" onsubmit="return confirm('ยืนยันลบสมาชิกนี้?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="user_id" value="<?= $m['user_id'] ?>">
          <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> ลบ</button>
        </form>
        <?php else: ?><span class="text-muted">(ตัวเอง)</span><?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>
</div></body></html>
