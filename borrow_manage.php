<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php"); exit;
}
include('db.php');

// Auto overdue
mysqli_query($conn,"UPDATE borrow_history SET status='overdue' WHERE status='borrowed' AND borrow_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND return_date IS NULL");

$msg = ''; $msg_type = 'success';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'] ?? '';

    if($action === 'add_borrow'){
        $book_id   = (int)$_POST['book_id'];
        $user_id   = (int)$_POST['user_id'];
        $bdate     = $_POST['borrow_date'];
        $chk = mysqli_fetch_assoc(mysqli_query($conn,"SELECT status FROM books WHERE book_id=$book_id"));
        if(($chk['status'] ?? '') !== 'available'){
            $msg = 'หนังสือนี้ไม่ว่างในขณะนี้'; $msg_type = 'danger';
        } else {
            $due = date('Y-m-d', strtotime($bdate . ' +15 days'));
            mysqli_query($conn,"INSERT INTO borrow_history(user_id,book_id,borrow_date,return_date,status) VALUES($user_id,$book_id,'$bdate','$due','borrowed')");
            mysqli_query($conn,"UPDATE books SET status='borrowed' WHERE book_id=$book_id");
            $msg = "✅ บันทึกการยืมเรียบร้อย (กำหนดคืน: ".date('d/m/Y',strtotime($due)).")";
        }
    }

    if($action === 'update_borrow'){
        $hid    = (int)$_POST['history_id'];
        $status = $_POST['status'];
        $rdate  = $_POST['return_date_manual'] ?: ($status === 'returned' ? date('Y-m-d') : null);
        $rdval  = $rdate ? "'$rdate'" : "NULL";
        mysqli_query($conn,"UPDATE borrow_history SET status='$status', return_date=$rdval WHERE history_id=$hid");
        $brow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT book_id FROM borrow_history WHERE history_id=$hid"));
        if($status === 'returned'){
            mysqli_query($conn,"UPDATE books SET status='available' WHERE book_id=".$brow['book_id']);
        } elseif($status === 'lost'){
            mysqli_query($conn,"UPDATE books SET status='lost' WHERE book_id=".$brow['book_id']);
        }

        // บันทึกค่าปรับ
        if(isset($_POST['add_fine']) && floatval($_POST['fine_price']) > 0){
            $uid      = (int)$_POST['uid_hidden'];
            $bid      = (int)$_POST['bid_hidden'];
            $cat      = mysqli_real_escape_string($conn,$_POST['fine_cat']);
            $price    = floatval($_POST['fine_price']);
            mysqli_query($conn,"INSERT INTO fines(user_id,book_id,category,price) VALUES($uid,$bid,'$cat',$price)");
        }
        $msg = "✅ อัปเดตการยืม #$hid เรียบร้อย";
    }
}

$edit_row = null;
if(isset($_GET['edit'])){
    $eid = (int)$_GET['edit'];
    $edit_row = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT bh.*, bk.book_name, CONCAT(u.first_name,' ',u.last_name) AS fullname, u.user_id AS uid
        FROM borrow_history bh
        JOIN books bk ON bh.book_id=bk.book_id
        JOIN users u ON bh.user_id=u.user_id
        WHERE bh.history_id=$eid"));
}

$filter_status = $_GET['status'] ?? '';
$filter_search = $_GET['search'] ?? '';
$where = "WHERE 1=1";
if($filter_status) $where .= " AND bh.status='".mysqli_real_escape_string($conn,$filter_status)."'";
if($filter_search) $where .= " AND (bk.book_name LIKE '%".mysqli_real_escape_string($conn,$filter_search)."%' OR u.first_name LIKE '%".mysqli_real_escape_string($conn,$filter_search)."%' OR u.last_name LIKE '%".mysqli_real_escape_string($conn,$filter_search)."%')";

$borrows     = mysqli_query($conn,"
    SELECT bh.*, bk.book_name, CONCAT(u.first_name,' ',u.last_name) AS fullname
    FROM borrow_history bh
    JOIN books bk ON bh.book_id=bk.book_id
    JOIN users u ON bh.user_id=u.user_id
    $where ORDER BY bh.history_id DESC");
$all_books   = mysqli_query($conn,"SELECT * FROM books WHERE status='available' ORDER BY book_name");
$all_members = mysqli_query($conn,"SELECT * FROM users WHERE first_name != 'admin' ORDER BY first_name");

$page_title = 'จัดการการยืม-คืน';
include('header.php');
?>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-book-reader"></i> จัดการการยืม-คืน</div>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalAdd').classList.add('open')"><i class="fas fa-plus"></i> บันทึกการยืม</button>
  </div>
</div>
<div class="container">

<?php if($msg): ?><div class="alert alert-<?= $msg_type ?>"><i class="fas fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>

<!-- Edit Modal -->
<?php if($edit_row): ?>
<div class="modal-overlay open">
<div class="modal-box" style="max-width:560px">
  <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')"><i class="fas fa-times"></i></button>
  <div class="modal-title"><i class="fas fa-edit" style="color:#d97706"></i> แก้ไขการยืม #<?= $edit_row['history_id'] ?></div>
  <div style="background:#f8fafc;border-radius:10px;padding:1rem;margin-bottom:1.25rem;font-size:.875rem;">
    <div><strong>📚 หนังสือ:</strong> <?= htmlspecialchars($edit_row['book_name']) ?></div>
    <div class="mt-1"><strong>👤 ผู้ยืม:</strong> <?= htmlspecialchars($edit_row['fullname']) ?></div>
    <div class="mt-1"><strong>📅 ยืมวันที่:</strong> <?= date('d/m/Y',strtotime($edit_row['borrow_date'])) ?></div>
  </div>
  <form method="post">
    <input type="hidden" name="action" value="update_borrow">
    <input type="hidden" name="history_id" value="<?= $edit_row['history_id'] ?>">
    <input type="hidden" name="uid_hidden" value="<?= $edit_row['uid'] ?>">
    <input type="hidden" name="bid_hidden" value="<?= $edit_row['book_id'] ?>">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">สถานะ</label>
        <select name="status" class="form-control form-select" id="statusSel" onchange="toggleFine()">
          <option value="borrowed" <?= $edit_row['status']==='borrowed'?'selected':''?>>กำลังยืม</option>
          <option value="returned" <?= $edit_row['status']==='returned'?'selected':''?>>คืนแล้ว</option>
          <option value="overdue"  <?= $edit_row['status']==='overdue' ?'selected':''?>>เกินกำหนด</option>
          <option value="lost"     <?= $edit_row['status']==='lost'    ?'selected':''?>>หนังสือหาย</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">วันที่คืนจริง</label>
        <input type="date" name="return_date_manual" class="form-control" value="<?= $edit_row['return_date'] ?>">
      </div>
    </div>
    <!-- ค่าปรับ -->
    <div id="fineSection" style="background:#fef9c3;border:1.5px solid #fde68a;border-radius:12px;padding:1rem;margin-bottom:1rem;display:none">
      <div style="font-weight:700;color:#92400e;margin-bottom:.75rem;"><i class="fas fa-exclamation-triangle"></i> เพิ่มค่าปรับ / ค่าเสียหาย</div>
      <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;font-size:.875rem;cursor:pointer;">
        <input type="checkbox" name="add_fine" value="1" id="fineChk" onchange="document.getElementById('fineFields').style.display=this.checked?'block':'none'">
        เพิ่มรายการค่าปรับ
      </label>
      <div id="fineFields" style="display:none">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">ประเภท</label>
            <select name="fine_cat" class="form-control form-select">
              <option value="เก่า">เก่า</option>
              <option value="ขาด">ขาด</option>
              <option value="ใหม่">ใหม่</option>
              <option value="หาย">หาย</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">จำนวนเงิน (บาท)</label>
            <input type="number" name="fine_price" class="form-control" min="0" step="0.01" placeholder="0.00">
          </div>
        </div>
      </div>
    </div>
    <div class="flex gap-1">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึก</button>
      <a href="borrow_manage.php" class="btn btn-secondary"><i class="fas fa-times"></i> ยกเลิก</a>
    </div>
  </form>
</div>
</div>
<script>
function toggleFine(){
  const s = document.getElementById('statusSel').value;
  document.getElementById('fineSection').style.display = (s==='overdue'||s==='lost'||s==='returned') ? 'block':'none';
}
toggleFine();
</script>
<?php endif; ?>

<!-- Add Modal -->
<div class="modal-overlay" id="modalAdd">
<div class="modal-box">
  <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')"><i class="fas fa-times"></i></button>
  <div class="modal-title"><i class="fas fa-plus-circle" style="color:#4f46e5"></i> บันทึกการยืมหนังสือ</div>
  <form method="post">
    <input type="hidden" name="action" value="add_borrow">
    <div class="form-group">
      <label class="form-label">หนังสือ (เฉพาะที่ว่าง)</label>
      <select name="book_id" class="form-control form-select" required>
        <option value="">-- เลือกหนังสือ --</option>
        <?php while($b = mysqli_fetch_assoc($all_books)): ?>
        <option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['book_name']) ?> (<?= $b['type_name'] ?>)</option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">สมาชิกผู้ยืม</label>
      <select name="user_id" class="form-control form-select" required>
        <option value="">-- เลือกสมาชิก --</option>
        <?php while($m = mysqli_fetch_assoc($all_members)): ?>
        <option value="<?= $m['user_id'] ?>"><?= htmlspecialchars($m['first_name'].' '.$m['last_name']) ?> (<?= $m['email'] ?>)</option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">วันที่ยืม</label>
      <input type="date" name="borrow_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%"><i class="fas fa-save"></i> บันทึก</button>
  </form>
</div>
</div>

<!-- Filter + Table -->
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fas fa-list" style="color:#4f46e5"></i> รายการยืม-คืนทั้งหมด</div>
    <form method="get" class="flex gap-1 items-center" style="flex-wrap:wrap">
      <div class="search-bar"><i class="fas fa-search"></i><input type="text" name="search" placeholder="ค้นหา..." value="<?= htmlspecialchars($filter_search) ?>"></div>
      <select name="status" class="form-control form-select" style="width:160px;padding:.5rem .85rem">
        <option value="">ทุกสถานะ</option>
        <option value="borrowed" <?= $filter_status==='borrowed'?'selected':''?>>กำลังยืม</option>
        <option value="overdue"  <?= $filter_status==='overdue' ?'selected':''?>>เกินกำหนด</option>
        <option value="returned" <?= $filter_status==='returned'?'selected':''?>>คืนแล้ว</option>
        <option value="lost"     <?= $filter_status==='lost'    ?'selected':''?>>หนังสือหาย</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
      <a href="borrow_manage.php" class="btn btn-secondary btn-sm"><i class="fas fa-undo"></i></a>
    </form>
  </div>
  <div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>#</th><th>หนังสือ</th><th>ผู้ยืม</th><th>วันที่ยืม</th><th>วันที่คืน</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
    <tbody>
    <?php if(mysqli_num_rows($borrows) === 0): ?>
    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-inbox"></i><p>ไม่พบรายการ</p></div></td></tr>
    <?php else: ?>
    <?php while($r = mysqli_fetch_assoc($borrows)): ?>
    <?php $badge = match($r['status']){
      'borrowed'=>'<span class="badge badge-blue"><i class="fas fa-book-open"></i> กำลังยืม</span>',
      'overdue' =>'<span class="badge badge-red"><i class="fas fa-exclamation-circle"></i> เกินกำหนด</span>',
      'returned'=>'<span class="badge badge-green"><i class="fas fa-check-circle"></i> คืนแล้ว</span>',
      'lost'    =>'<span class="badge badge-gray"><i class="fas fa-times-circle"></i> หนังสือหาย</span>',
      default   =>'<span class="badge badge-gray">'.$r['status'].'</span>'
    }; ?>
    <tr class="<?= $r['status']==='overdue'?'overdue-row':'' ?>">
      <td class="text-muted"><?= $r['history_id'] ?></td>
      <td><strong><?= htmlspecialchars($r['book_name']) ?></strong></td>
      <td><?= htmlspecialchars($r['fullname']) ?></td>
      <td class="text-muted"><?= date('d/m/Y',strtotime($r['borrow_date'])) ?></td>
      <td class="text-muted"><?= $r['return_date'] ? date('d/m/Y',strtotime($r['return_date'])) : '–' ?></td>
      <td><?= $badge ?></td>
      <td><a href="borrow_manage.php?edit=<?= $r['history_id'] ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i> แก้ไข</a></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody>
  </table>
  </div>
</div>
</div></body></html>
