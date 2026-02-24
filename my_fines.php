<?php
session_start();
include('db.php');
$role = $_SESSION['role'] ?? 'guest';
$uid  = (int)($_SESSION['user_id'] ?? 0);
$fines = null;
$total = 0;
if($role === 'member' && $uid){
    $fines = mysqli_query($conn,"
        SELECT f.*, bk.book_name FROM fines f
        JOIN books bk ON f.book_id=bk.book_id
        WHERE f.user_id=$uid ORDER BY f.fine_id DESC");
    $total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(price),0) s FROM fines WHERE user_id=$uid"))['s'];
}
$page_title = 'ค่าปรับของฉัน';
include('header.php');
?>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-exclamation-circle"></i> ค่าปรับของฉัน</div>
  </div>
</div>
<div class="container">
<?php if($role==='guest'): ?>
  <div style="background:linear-gradient(135deg,#fef9c3,#fef2f2);border:1.5px solid #fde68a;border-radius:16px;padding:2.5rem;text-align:center;margin-top:1rem;">
    <div style="font-size:3rem;margin-bottom:.75rem;">💳</div>
    <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:.5rem;">เข้าสู่ระบบเพื่อดูค่าปรับ</h3>
    <p style="color:#64748b;margin-bottom:1.5rem;font-size:.9rem;">ข้อมูลค่าปรับเป็นข้อมูลส่วนตัว กรุณาเข้าสู่ระบบก่อน</p>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="login.php?redirect=my_fines.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
      <a href="register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
    </div>
  </div>
<?php else: ?>
  <?php if($total > 0): ?>
  <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> คุณมีค่าปรับรวม <strong>฿<?= number_format($total,2) ?></strong> — กรุณาติดต่อเจ้าหน้าที่ห้องสมุด</div>
  <?php else: ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> ไม่มีค่าปรับ 🎉</div>
  <?php endif; ?>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon" style="background:#fef9c3">💰</div><div><div class="stat-value" style="color:#d97706">฿<?= number_format($total,2) ?></div><div class="stat-label">ค่าปรับรวม</div></div></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list" style="color:#d97706"></i> รายละเอียดค่าปรับ</div></div>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>#</th><th>หนังสือ</th><th>ประเภท</th><th>จำนวนเงิน</th></tr></thead>
      <tbody>
      <?php if(!$fines || mysqli_num_rows($fines)===0): ?>
      <tr><td colspan="4"><div class="empty-state"><i class="fas fa-smile"></i><p>ไม่มีรายการค่าปรับ</p></div></td></tr>
      <?php else: ?>
      <?php while($f = mysqli_fetch_assoc($fines)): ?>
      <tr>
        <td class="text-muted"><?= $f['fine_id'] ?></td>
        <td><strong><?= htmlspecialchars($f['book_name']) ?></strong></td>
        <td><span class="badge badge-yellow"><?= htmlspecialchars($f['category']) ?></span></td>
        <td><strong style="color:#dc2626;font-size:1rem">฿<?= number_format($f['price'],2) ?></strong></td>
      </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table></div>
  </div>
  <div class="alert alert-info"><i class="fas fa-info-circle"></i> การชำระค่าปรับต้องติดต่อเจ้าหน้าที่ห้องสมุดโดยตรง</div>
<?php endif; ?>
</div></body></html>
