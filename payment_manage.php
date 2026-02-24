<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php"); exit;
}
include('db.php');
$msg = ''; $msg_type = 'success';

if($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='add_fine'){
    $uid  = (int)$_POST['user_id'];
    $bid  = (int)$_POST['book_id'];
    $cat  = mysqli_real_escape_string($conn,$_POST['category']);
    $pr   = floatval($_POST['price']);
    mysqli_query($conn,"INSERT INTO fines(user_id,book_id,category,price) VALUES($uid,$bid,'$cat',$pr)");
    $msg = "✅ เพิ่มค่าปรับเรียบร้อย";
}
if($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='delete_fine'){
    $fid = (int)$_POST['fine_id'];
    mysqli_query($conn,"DELETE FROM fines WHERE fine_id=$fid");
    $msg = "🗑️ ลบรายการค่าปรับเรียบร้อย"; $msg_type = 'warning';
}

$fines = mysqli_query($conn,"
    SELECT f.*, CONCAT(u.first_name,' ',u.last_name) AS fullname, bk.book_name
    FROM fines f
    JOIN users u ON f.user_id=u.user_id
    JOIN books bk ON f.book_id=bk.book_id
    ORDER BY f.fine_id DESC");

$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(price),0) s FROM fines"))['s'];

$all_members = mysqli_query($conn,"SELECT * FROM users WHERE first_name!='admin' ORDER BY first_name");
$all_books   = mysqli_query($conn,"SELECT * FROM books ORDER BY book_name");

$page_title = 'จัดการค่าปรับ';
include('header.php');
?>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-money-bill-wave"></i> จัดการค่าปรับ</div>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalAdd').classList.add('open')"><i class="fas fa-plus"></i> เพิ่มค่าปรับ</button>
  </div>
</div>
<div class="container">
<?php if($msg): ?><div class="alert alert-<?= $msg_type ?>"><i class="fas fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:#fef9c3"><span style="font-size:1.4rem">💰</span></div>
    <div><div class="stat-value" style="color:#d97706">฿<?= number_format($total,2) ?></div><div class="stat-label">ค่าปรับรวมทั้งหมด</div></div>
  </div>
</div>

<div class="modal-overlay" id="modalAdd">
  <div class="modal-box">
    <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')"><i class="fas fa-times"></i></button>
    <div class="modal-title"><i class="fas fa-plus-circle" style="color:#dc2626"></i> เพิ่มค่าปรับ</div>
    <form method="post">
      <input type="hidden" name="action" value="add_fine">
      <div class="form-group">
        <label class="form-label">สมาชิก</label>
        <select name="user_id" class="form-control form-select" required>
          <option value="">-- เลือกสมาชิก --</option>
          <?php while($m = mysqli_fetch_assoc($all_members)): ?>
          <option value="<?= $m['user_id'] ?>"><?= htmlspecialchars($m['first_name'].' '.$m['last_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">หนังสือ</label>
        <select name="book_id" class="form-control form-select" required>
          <option value="">-- เลือกหนังสือ --</option>
          <?php while($b = mysqli_fetch_assoc($all_books)): ?>
          <option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['book_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">ประเภท</label>
          <select name="category" class="form-control form-select">
            <option value="เก่า">เก่า</option>
            <option value="ขาด">ขาด</option>
            <option value="ใหม่">ใหม่</option>
            <option value="หาย">หาย</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">จำนวนเงิน (บาท)</label>
          <input type="number" name="price" class="form-control" min="0" step="0.01" placeholder="0.00" required>
        </div>
      </div>
      <button type="submit" class="btn btn-danger" style="width:100%"><i class="fas fa-save"></i> บันทึกค่าปรับ</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title"><i class="fas fa-list" style="color:#d97706"></i> รายการค่าปรับทั้งหมด</div></div>
  <div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>#</th><th>สมาชิก</th><th>หนังสือ</th><th>ประเภท</th><th>จำนวนเงิน</th><th>จัดการ</th></tr></thead>
    <tbody>
    <?php if(mysqli_num_rows($fines)===0): ?>
    <tr><td colspan="6"><div class="empty-state"><i class="fas fa-smile"></i><p>ไม่มีรายการค่าปรับ</p></div></td></tr>
    <?php else: ?>
    <?php while($f = mysqli_fetch_assoc($fines)): ?>
    <tr>
      <td class="text-muted"><?= $f['fine_id'] ?></td>
      <td><strong><?= htmlspecialchars($f['fullname']) ?></strong></td>
      <td><?= htmlspecialchars($f['book_name']) ?></td>
      <td><span class="badge badge-yellow"><?= htmlspecialchars($f['category']) ?></span></td>
      <td><strong style="color:#dc2626">฿<?= number_format($f['price'],2) ?></strong></td>
      <td>
        <form method="post" style="display:inline" onsubmit="return confirm('ยืนยันลบรายการนี้?')">
          <input type="hidden" name="action" value="delete_fine">
          <input type="hidden" name="fine_id" value="<?= $f['fine_id'] ?>">
          <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
        </form>
      </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody>
  </table>
  </div>
</div>
</div></body></html>
