<?php
session_start();
include('db.php');
$role = $_SESSION['role'] ?? 'guest';
$uid  = (int)($_SESSION['user_id'] ?? 0);

$borrowing = $overdue = $returned = $fine_total = 0;
$recent = null;
if($role === 'member' && $uid){
    mysqli_query($conn,"UPDATE borrow_history SET status='overdue' WHERE user_id=$uid AND status='borrowed' AND return_date < CURDATE()");
    $borrowing  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM borrow_history WHERE user_id=$uid AND status IN('borrowed','overdue')"))['c'];
    $overdue    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM borrow_history WHERE user_id=$uid AND status='overdue'"))['c'];
    $returned   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM borrow_history WHERE user_id=$uid AND status='returned'"))['c'];
    $fine_total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(price),0) s FROM fines WHERE user_id=$uid"))['s'];
    $recent = mysqli_query($conn,"
        SELECT bh.*, bk.book_name FROM borrow_history bh JOIN books bk ON bh.book_id=bk.book_id
        WHERE bh.user_id=$uid ORDER BY bh.history_id DESC LIMIT 5");
}
$total_books = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM books"))['c'];
$avail_books = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM books WHERE status='available'"))['c'];
$total_borrow= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM borrow_history WHERE status='borrowed'"))['c'];
$page_title = 'หน้าหลัก';
include('header.php');
?>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-home"></i> <?= $role==='member' ? 'ยินดีต้อนรับ, '.htmlspecialchars($_SESSION['fullname']) : 'ยินดีต้อนรับสู่ระบบห้องสมุด' ?></div>
    <a href="books.php" class="btn btn-primary btn-sm"><i class="fas fa-book"></i> ดูหนังสือ / ยืมหนังสือ</a>
  </div>
</div>
<div class="container">
  <?php if($role==='member' && $overdue>0): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> คุณมีหนังสือเกินกำหนดคืน <strong><?= $overdue ?> เล่ม</strong> กรุณาติดต่อเจ้าหน้าที่</div>
  <?php endif; ?>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon" style="background:#eef2ff"><span style="font-size:1.4rem">📚</span></div><div><div class="stat-value" style="color:#4f46e5"><?= $total_books ?></div><div class="stat-label">หนังสือทั้งหมด</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#dcfce7"><span style="font-size:1.4rem">✅</span></div><div><div class="stat-value" style="color:#16a34a"><?= $avail_books ?></div><div class="stat-label">พร้อมให้ยืม</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#dbeafe"><span style="font-size:1.4rem">📖</span></div><div><div class="stat-value" style="color:#2563eb"><?= $total_borrow ?></div><div class="stat-label">กำลังถูกยืม</div></div></div>
    <?php if($role==='member'): ?>
    <div class="stat-card"><div class="stat-icon" style="background:#fee2e2"><span style="font-size:1.4rem">⚠️</span></div><div><div class="stat-value" style="color:#dc2626"><?= $overdue ?></div><div class="stat-label">ของฉัน: เกินกำหนด</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fef9c3"><span style="font-size:1.4rem">💰</span></div><div><div class="stat-value" style="color:#d97706">฿<?= number_format($fine_total,2) ?></div><div class="stat-label">ค่าปรับรวม</div></div></div>
    <?php endif; ?>
  </div>
  <?php if($role==='member' && $recent): ?>
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-history" style="color:#4f46e5"></i> ประวัติการยืมล่าสุด</div>
      <a href="my_borrow.php" class="btn btn-outline btn-sm">ดูทั้งหมด</a>
    </div>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>หนังสือ</th><th>วันที่ยืม</th><th>วันที่คืน</th><th>สถานะ</th></tr></thead>
      <tbody>
      <?php while($r = mysqli_fetch_assoc($recent)): ?>
      <?php $badge = match($r['status']){'borrowed'=>'<span class="badge badge-blue">📖 กำลังยืม</span>','overdue'=>'<span class="badge badge-red">⚠️ เกินกำหนด</span>','returned'=>'<span class="badge badge-green">✅ คืนแล้ว</span>',default=>'<span class="badge badge-gray">'.$r['status'].'</span>'}; ?>
      <tr class="<?= $r['status']==='overdue'?'overdue-row':'' ?>">
        <td><strong><?= htmlspecialchars($r['book_name']) ?></strong></td>
        <td class="text-muted"><?= date('d/m/Y',strtotime($r['borrow_date'])) ?></td>
        <td class="text-muted"><?= $r['return_date'] ? date('d/m/Y',strtotime($r['return_date'])) : '–' ?></td>
        <td><?= $badge ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table></div>
  </div>
  <?php endif; ?>
  <?php if($role==='guest'): ?>
  <div style="background:linear-gradient(135deg,#eef2ff,#fdf2f8);border:1.5px solid #c7d2fe;border-radius:16px;padding:2rem;text-align:center;margin-top:1rem;">
    <div style="font-size:2.5rem;margin-bottom:.75rem;">🔐</div>
    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem;">ต้องการยืมหนังสือ?</h3>
    <p style="color:#64748b;margin-bottom:1.25rem;font-size:.9rem;">สมัครสมาชิกฟรีหรือเข้าสู่ระบบ เพื่อยืมหนังสือออนไลน์</p>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
      <a href="register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
    </div>
  </div>
  <?php endif; ?>
</div></body></html>
