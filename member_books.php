<?php
session_start();
include('db.php');
$role   = $_SESSION['role'] ?? 'guest';
$search = $_GET['search'] ?? '';
$where  = $search ? "WHERE book_name LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR author LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR type_name LIKE '%".mysqli_real_escape_string($conn,$search)."%'" : "";
$books  = mysqli_query($conn,"SELECT * FROM books $where ORDER BY book_id DESC");
$page_title = 'รายการหนังสือ';
include('header.php');
?>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-book"></i> รายการหนังสือในห้องสมุด</div>
    <form method="get" class="flex gap-1">
      <div class="search-bar"><i class="fas fa-search"></i><input type="text" name="search" placeholder="ค้นหาหนังสือ..." value="<?= htmlspecialchars($search) ?>"></div>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    </form>
  </div>
</div>
<div class="container">
  <div class="card">
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>#</th><th>ชื่อหนังสือ</th><th>ประเภท</th><th>ผู้แต่ง</th><th>สถานะ</th></tr></thead>
      <tbody>
      <?php while($b = mysqli_fetch_assoc($books)): ?>
      <?php $s = match($b['status'] ?? 'available'){'available'=>'<span class="badge badge-green"><i class="fas fa-check"></i> ว่าง</span>','borrowed'=>'<span class="badge badge-blue"><i class="fas fa-book-open"></i> ถูกยืม</span>','lost'=>'<span class="badge badge-gray"><i class="fas fa-times"></i> หาย</span>',default=>'<span class="badge badge-gray">'.htmlspecialchars($b['status']).'</span>'}; ?>
      <tr>
        <td class="text-muted"><?= $b['book_id'] ?></td>
        <td><strong><?= htmlspecialchars($b['book_name']) ?></strong></td>
        <td class="text-muted"><?= htmlspecialchars($b['type_name']) ?></td>
        <td class="text-muted"><?= htmlspecialchars($b['author']) ?></td>
        <td><?= $s ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table></div>
  </div>
  <?php if($role === 'guest' || $role === 'member'): ?>
  <div class="alert alert-info"><i class="fas fa-info-circle"></i> ต้องการยืมหนังสือ ไปที่ <a href="books.php" style="font-weight:700;color:#1e40af">รายการหนังสือ</a> แล้วกดปุ่มยืม</div>
  <?php endif; ?>
</div></body></html>
