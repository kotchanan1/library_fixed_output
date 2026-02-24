<?php
session_start();
include('db.php');
$role = $_SESSION['role'] ?? 'guest';
$uid  = (int)($_SESSION['user_id'] ?? 0);
$stats   = ['total'=>0,'borrowing'=>0,'returned'=>0,'overdue'=>0];
$borrows = null;
$filter  = $_GET['status'] ?? '';
if($role === 'member' && $uid){
    mysqli_query($conn,"UPDATE borrow_history SET status='overdue' WHERE user_id=$uid AND status='borrowed' AND return_date < CURDATE()");
    $where = "WHERE bh.user_id=$uid";
    if($filter) $where .= " AND bh.status='".mysqli_real_escape_string($conn,$filter)."'";
    $borrows = mysqli_query($conn,"
        SELECT bh.*, bk.book_name, bk.author FROM borrow_history bh
        JOIN books bk ON bh.book_id=bk.book_id $where ORDER BY bh.history_id DESC");
    $stats = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COUNT(*) total,
               SUM(status IN('borrowed','overdue')) borrowing,
               SUM(status='returned') returned,
               SUM(status='overdue') overdue
        FROM borrow_history WHERE user_id=$uid"));
}
$page_title = 'ประวัติการยืม';
include('header.php');
?>
<?php if(isset($_GET['success']) && isset($_GET['book'])): ?>
<div style="background:#dcfce7;border:1.5px solid #86efac;border-radius:14px;padding:1.1rem 1.5rem;margin:1.25rem auto;max-width:1280px;padding-left:1.5rem;padding-right:1.5rem;display:flex;align-items:center;gap:.75rem;font-size:.95rem;font-weight:600;color:#166534;">
  <span style="font-size:1.5rem;">✅</span>
  ยืม <strong>"<?= htmlspecialchars($_GET['book']) ?>"</strong> สำเร็จแล้ว! กำหนดคืนภายใน 15 วัน
</div>
<?php endif; ?>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-history"></i> ประวัติการยืม-คืนหนังสือ</div>
    <a href="books.php" class="btn btn-primary btn-sm"><i class="fas fa-book"></i> ยืมหนังสือเพิ่ม</a>
  </div>
</div>
<div class="container">
<?php if($role==='guest'): ?>
  <div style="background:linear-gradient(135deg,#eef2ff,#fdf2f8);border:1.5px solid #c7d2fe;border-radius:16px;padding:2.5rem;text-align:center;margin-top:1rem;">
    <div style="font-size:3rem;margin-bottom:.75rem;">📋</div>
    <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:.5rem;">เข้าสู่ระบบเพื่อดูประวัติการยืม</h3>
    <p style="color:#64748b;margin-bottom:1.5rem;font-size:.9rem;">ประวัติการยืมเป็นข้อมูลส่วนตัว กรุณาเข้าสู่ระบบก่อน</p>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="login.php?redirect=my_borrow.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
      <a href="register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
    </div>
  </div>
<?php else: ?>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon" style="background:#f3e8ff">📚</div><div><div class="stat-value" style="color:#7c3aed"><?= $stats['total'] ?></div><div class="stat-label">ยืมทั้งหมด</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#dbeafe">📖</div><div><div class="stat-value" style="color:#2563eb"><?= $stats['borrowing'] ?></div><div class="stat-label">กำลังยืม</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#dcfce7">✅</div><div><div class="stat-value" style="color:#16a34a"><?= $stats['returned'] ?></div><div class="stat-label">คืนแล้ว</div></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fee2e2">⚠️</div><div><div class="stat-value" style="color:#dc2626"><?= $stats['overdue'] ?></div><div class="stat-label">เกินกำหนด</div></div></div>
  </div>
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-list" style="color:#4f46e5"></i> รายการยืม</div>
      <div style="display:flex;gap:.35rem;background:#f8fafc;border-radius:10px;padding:4px;border:1px solid #e2e8f0;">
        <?php foreach([''=> 'ทั้งหมด','borrowed'=>'กำลังยืม','overdue'=>'เกินกำหนด','returned'=>'คืนแล้ว'] as $val=>$label): ?>
        <a href="my_borrow.php?status=<?= $val ?>" style="padding:.4rem .85rem;border-radius:7px;font-size:.8rem;font-weight:600;text-decoration:none;transition:all .2s;<?= $filter===$val?'background:white;color:#4f46e5;box-shadow:0 1px 4px rgba(0,0,0,.08)':'color:#64748b' ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>#</th><th>หนังสือ</th><th>วันที่ยืม</th><th>กำหนดคืน (15 วัน)</th><th>สถานะ</th><th>เหลือ/เกิน</th></tr></thead>
      <tbody>
      <?php if(!$borrows || mysqli_num_rows($borrows)===0): ?>
      <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>ไม่พบรายการ</p></div></td></tr>
      <?php else: ?>
      <?php while($r = mysqli_fetch_assoc($borrows)): ?>
      <?php 
        $badge = match($r['status']){
          'borrowed'=>'<span class="badge badge-blue">📖 กำลังยืม</span>',
          'overdue'=>'<span class="badge badge-red">⚠️ เกินกำหนด</span>',
          'returned'=>'<span class="badge badge-green">✅ คืนแล้ว</span>',
          'lost'=>'<span class="badge badge-gray">❌ หนังสือหาย</span>',
          default=>'<span class="badge badge-gray">'.$r['status'].'</span>'
        };
        $due_date = $r['return_date'];
        $days_info = '';
        if($due_date && $r['status'] === 'borrowed'){
          $diff = (new DateTime($due_date))->diff(new DateTime())->days;
          $is_past = new DateTime($due_date) < new DateTime();
          if($is_past) $days_info = '<span style="color:#dc2626;font-weight:700;font-size:.78rem;">เกิน '.$diff.' วัน</span>';
          else $days_info = '<span style="color:#16a34a;font-weight:700;font-size:.78rem;">เหลือ '.$diff.' วัน</span>';
        } elseif($r['status']==='returned'){
          $days_info = '<span style="color:#94a3b8;font-size:.75rem;">คืนแล้ว</span>';
        }
      ?>
      <tr class="<?= $r['status']==='overdue'?'overdue-row':'' ?>">
        <td class="text-muted"><?= $r['history_id'] ?></td>
        <td><strong><?= htmlspecialchars($r['book_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['author'] ?? '') ?></small></td>
        <td class="text-muted"><?= date('d/m/Y',strtotime($r['borrow_date'])) ?></td>
        <td style="font-weight:600;color:<?= ($r['status']==='overdue')?'#dc2626':'#1e293b' ?>"><?= $due_date ? date('d/m/Y',strtotime($due_date)) : '–' ?></td>
        <td><?= $badge ?></td>
        <td><?= $days_info ?></td>
      </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table></div>
  </div>
<?php endif; ?>
</div></body></html>
