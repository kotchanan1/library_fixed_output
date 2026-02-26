<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php"); exit;
}
include('db.php');
$msg = ''; $msg_type = 'success';

// สร้างโฟลเดอร์อัปโหลดถ้ายังไม่มี
if(!is_dir('uploads/books')) mkdir('uploads/books', 0755, true);

// เพิ่ม column book_image ถ้ายังไม่มี (ทำครั้งเดียว)
mysqli_query($conn, "ALTER TABLE books ADD COLUMN IF NOT EXISTS book_image VARCHAR(255) DEFAULT NULL");

// ดึง categories ที่มีอยู่แล้ว
$cat_result = mysqli_query($conn, "SELECT DISTINCT type_name FROM books WHERE type_name IS NOT NULL AND type_name!='' ORDER BY type_name");
$existing_cats = [];
while($c = mysqli_fetch_assoc($cat_result)) $existing_cats[] = $c['type_name'];

function handleImageUpload($field = 'book_image'){
    if(!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $f = $_FILES[$field];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if(!in_array($ext, $allowed)) return null;
    if($f['size'] > 5 * 1024 * 1024) return null;
    $fname = 'book_' . time() . '_' . rand(100,999) . '.' . $ext;
    $dest  = 'uploads/books/' . $fname;
    if(move_uploaded_file($f['tmp_name'], $dest)) return $dest;
    return null;
}

if($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='add'){
    $n = mysqli_real_escape_string($conn, $_POST['book_name']);
    // category: ใช้ที่กรอกใหม่ หรือเลือกจาก dropdown
    $t = trim($_POST['type_new'] ?? '');
    if(!$t) $t = $_POST['type_name'] ?? '';
    $t = mysqli_real_escape_string($conn, $t);
    $a = mysqli_real_escape_string($conn, $_POST['author']);
    $s = mysqli_real_escape_string($conn, $_POST['status']);
    $img = handleImageUpload('book_image');
    $img_sql = $img ? "'".mysqli_real_escape_string($conn,$img)."'" : "NULL";
    mysqli_query($conn, "INSERT INTO books(book_name,type_name,author,status,book_image) VALUES('$n','$t','$a','$s',$img_sql)");
    $new_id = mysqli_insert_id($conn);
    // sync book_types
    mysqli_query($conn, "INSERT INTO book_types(type_name,book_id,book_name) VALUES('$t',$new_id,'$n')");
    $msg = "✅ เพิ่มหนังสือเรียบร้อย";
}
if($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='edit'){
    $id = (int)$_POST['book_id'];
    $n  = mysqli_real_escape_string($conn, $_POST['book_name']);
    $t  = trim($_POST['type_new'] ?? '');
    if(!$t) $t = $_POST['type_name'] ?? '';
    $t  = mysqli_real_escape_string($conn, $t);
    $a  = mysqli_real_escape_string($conn, $_POST['author']);
    $s  = mysqli_real_escape_string($conn, $_POST['status']);
    $img = handleImageUpload('book_image');
    if($img){
        mysqli_query($conn, "UPDATE books SET book_name='$n',type_name='$t',author='$a',status='$s',book_image='$img' WHERE book_id=$id");
    } else {
        mysqli_query($conn, "UPDATE books SET book_name='$n',type_name='$t',author='$a',status='$s' WHERE book_id=$id");
    }
    $msg = "✅ แก้ไขหนังสือเรียบร้อย";
}
if($_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='delete'){
    $id = (int)$_POST['book_id'];
    mysqli_query($conn, "DELETE FROM books WHERE book_id=$id");
    $msg = "🗑️ ลบหนังสือเรียบร้อย"; $msg_type = 'warning';
}

$search = $_GET['search'] ?? '';
$where  = $search ? "WHERE book_name LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR author LIKE '%".mysqli_real_escape_string($conn,$search)."%'" : "";
$books  = mysqli_query($conn, "SELECT * FROM books $where ORDER BY book_id DESC");

$edit_book = null;
if(isset($_GET['edit'])){
    $edit_book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE book_id=".(int)$_GET['edit']));
}

// ดึง category ใหม่หลัง add/edit
$cat_result2 = mysqli_query($conn, "SELECT DISTINCT type_name FROM books WHERE type_name IS NOT NULL AND type_name!='' ORDER BY type_name");
$existing_cats = [];
while($c = mysqli_fetch_assoc($cat_result2)) $existing_cats[] = $c['type_name'];

$page_title = 'จัดการหนังสือ';
include('header.php');
?>
<style>
.img-preview{width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #e2e8f0;}
.upload-area{border:2px dashed #c7d2fe;border-radius:10px;padding:1rem;text-align:center;cursor:pointer;background:#f8faff;transition:.2s;}
.upload-area:hover{border-color:#4f46e5;background:#eef2ff;}
.upload-area input[type=file]{display:none;}
</style>
<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-book"></i> จัดการหนังสือ</div>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalAdd').classList.add('open')"><i class="fas fa-plus"></i> เพิ่มหนังสือ</button>
  </div>
</div>
<div class="container">
<?php if($msg): ?><div class="alert alert-<?= $msg_type ?>"><i class="fas fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>

<?php if($edit_book): ?>
<div class="modal-overlay open">
  <div class="modal-box" style="max-width:580px;">
    <a href="book_list.php" class="modal-close"><i class="fas fa-times"></i></a>
    <div class="modal-title"><i class="fas fa-edit" style="color:#d97706"></i> แก้ไขหนังสือ</div>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="book_id" value="<?= $edit_book['book_id'] ?>">
      <div class="form-group"><label class="form-label">ชื่อหนังสือ *</label><input name="book_name" class="form-control" value="<?= htmlspecialchars($edit_book['book_name']) ?>" required></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">หมวดหมู่</label>
          <select name="type_name" class="form-control form-select" onchange="if(this.value==='__new__')document.getElementById('new_cat_edit').style.display='block';else document.getElementById('new_cat_edit').style.display='none';">
            <option value="">-- เลือกหมวดหมู่ --</option>
            <?php foreach($existing_cats as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $edit_book['type_name']===$cat?'selected':'' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
            <option value="__new__">+ เพิ่มหมวดหมู่ใหม่...</option>
          </select>
          <div id="new_cat_edit" style="display:none;margin-top:.5rem;">
            <input name="type_new" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่">
          </div>
        </div>
        <div class="form-group"><label class="form-label">ผู้แต่ง</label><input name="author" class="form-control" value="<?= htmlspecialchars($edit_book['author']) ?>"></div>
      </div>
      <div class="form-group"><label class="form-label">สถานะ</label>
        <select name="status" class="form-control form-select">
          <option value="available" <?= ($edit_book['status']??'')==='available'?'selected':''?>>ว่าง</option>
          <option value="borrowed"  <?= ($edit_book['status']??'')==='borrowed' ?'selected':''?>>ถูกยืม</option>
          <option value="lost"      <?= ($edit_book['status']??'')==='lost'     ?'selected':''?>>หาย</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">รูปปกหนังสือ</label>
        <?php if(!empty($edit_book['book_image']) && file_exists($edit_book['book_image'])): ?>
        <div style="margin-bottom:.5rem;"><img src="<?= htmlspecialchars($edit_book['book_image']) ?>" class="img-preview" alt="ปกหนังสือ"> <small class="text-muted">รูปปัจจุบัน</small></div>
        <?php endif; ?>
        <div class="upload-area" onclick="this.querySelector('input').click()">
          <input type="file" name="book_image" accept="image/*" onchange="previewImg(this,'prev_edit')">
          <div id="prev_edit"></div>
          <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:#4f46e5;margin-bottom:.3rem;"></i>
          <div style="font-size:.8rem;color:#64748b;">คลิกเพื่ออัปโหลดรูปปก<br><small>JPG, PNG, GIF, WEBP (ไม่เกิน 5MB)</small></div>
        </div>
      </div>
      <div class="flex gap-1">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึก</button>
        <a href="book_list.php" class="btn btn-secondary"><i class="fas fa-times"></i> ยกเลิก</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Modal เพิ่มหนังสือ -->
<div class="modal-overlay" id="modalAdd">
  <div class="modal-box" style="max-width:580px;">
    <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')"><i class="fas fa-times"></i></button>
    <div class="modal-title"><i class="fas fa-plus-circle" style="color:#4f46e5"></i> เพิ่มหนังสือใหม่</div>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div class="form-group"><label class="form-label">ชื่อหนังสือ *</label><input name="book_name" class="form-control" required placeholder="ชื่อหนังสือ"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">หมวดหมู่</label>
          <select name="type_name" class="form-control form-select" onchange="if(this.value==='__new__')document.getElementById('new_cat_add').style.display='block';else document.getElementById('new_cat_add').style.display='none';">
            <option value="">-- เลือกหมวดหมู่ --</option>
            <?php foreach($existing_cats as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
            <option value="__new__">+ เพิ่มหมวดหมู่ใหม่...</option>
          </select>
          <div id="new_cat_add" style="display:none;margin-top:.5rem;">
            <input name="type_new" class="form-control" placeholder="พิมพ์ชื่อหมวดหมู่ใหม่">
          </div>
        </div>
        <div class="form-group"><label class="form-label">ผู้แต่ง</label><input name="author" class="form-control" placeholder="ชื่อผู้แต่ง"></div>
      </div>
      <div class="form-group"><label class="form-label">สถานะ</label>
        <select name="status" class="form-control form-select">
          <option value="available">ว่าง</option>
          <option value="borrowed">ถูกยืม</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">รูปปกหนังสือ</label>
        <div class="upload-area" onclick="this.querySelector('input').click()">
          <input type="file" name="book_image" accept="image/*" onchange="previewImg(this,'prev_add')">
          <div id="prev_add"></div>
          <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:#4f46e5;margin-bottom:.3rem;"></i>
          <div style="font-size:.8rem;color:#64748b;">คลิกเพื่ออัปโหลดรูปปก<br><small>JPG, PNG, GIF, WEBP (ไม่เกิน 5MB)</small></div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%"><i class="fas fa-save"></i> เพิ่มหนังสือ</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fas fa-list" style="color:#4f46e5"></i> รายการหนังสือทั้งหมด</div>
    <form method="get" class="flex gap-1">
      <div class="search-bar"><i class="fas fa-search"></i><input type="text" name="search" placeholder="ค้นหา..." value="<?= htmlspecialchars($search) ?>"></div>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
      <a href="book_list.php" class="btn btn-secondary btn-sm"><i class="fas fa-undo"></i></a>
    </form>
  </div>
  <div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>#</th><th>ปก</th><th>ชื่อหนังสือ</th><th>หมวดหมู่</th><th>ผู้แต่ง</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
    <tbody>
    <?php while($b = mysqli_fetch_assoc($books)): ?>
    <?php $status_badge = match($b['status'] ?? 'available'){ 'available'=>'<span class="badge badge-green">ว่าง</span>', 'borrowed'=>'<span class="badge badge-blue">ถูกยืม</span>', 'lost'=>'<span class="badge badge-gray">หาย</span>', default=>'<span class="badge badge-gray">'.htmlspecialchars($b['status'] ?? '').'</span>' }; ?>
    <tr>
      <td class="text-muted"><?= $b['book_id'] ?></td>
      <td>
        <?php if(!empty($b['book_image']) && file_exists($b['book_image'])): ?>
          <img src="<?= htmlspecialchars($b['book_image']) ?>" style="width:48px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;">
        <?php else: ?>
          <div style="width:48px;height:60px;background:#eef2ff;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;border:1px solid #e2e8f0;">📚</div>
        <?php endif; ?>
      </td>
      <td><strong><?= htmlspecialchars($b['book_name']) ?></strong></td>
      <td class="text-muted"><?= htmlspecialchars($b['type_name'] ?? '–') ?></td>
      <td class="text-muted"><?= htmlspecialchars($b['author'] ?? '–') ?></td>
      <td><?= $status_badge ?></td>
      <td>
        <div class="flex gap-1">
          <a href="book_list.php?edit=<?= $b['book_id'] ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
          <form method="post" style="display:inline" onsubmit="return confirm('ยืนยันลบหนังสือนี้?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
            <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
          </form>
        </div>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>
</div>

<script>
function previewImg(input, divId){
  const div = document.getElementById(divId);
  div.innerHTML = '';
  if(input.files && input.files[0]){
    const reader = new FileReader();
    reader.onload = e => {
      div.innerHTML = `<img src="${e.target.result}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;margin-bottom:.4rem;">`;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
</body></html>