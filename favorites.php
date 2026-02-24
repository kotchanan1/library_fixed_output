<?php
session_start();
include('db.php');
$role = $_SESSION['role'] ?? 'guest';
$uid  = (int)($_SESSION['user_id'] ?? 0);

// ลบออกจากโปรด
if($role === 'member' && isset($_GET['remove'])){
    $bid = (int)$_GET['remove'];
    mysqli_query($conn,"DELETE FROM favorites WHERE user_id=$uid AND book_id=$bid");
    header("Location: favorites.php?removed=1"); exit;
}

$favorites = null;
if($role === 'member' && $uid){
    $favorites = mysqli_query($conn,"
        SELECT b.*, f.favorite_id, f.created_at AS fav_date
        FROM favorites f JOIN books b ON f.book_id=b.book_id
        WHERE f.user_id=$uid ORDER BY f.favorite_id DESC");
}

$colors = ['linear-gradient(135deg,#6366f1,#8b5cf6)','linear-gradient(135deg,#ec4899,#f43f5e)','linear-gradient(135deg,#06b6d4,#3b82f6)','linear-gradient(135deg,#10b981,#059669)','linear-gradient(135deg,#f59e0b,#ef4444)','linear-gradient(135deg,#8b5cf6,#ec4899)','linear-gradient(135deg,#14b8a6,#6366f1)','linear-gradient(135deg,#f97316,#eab308)'];
$icons  = ['📕','📗','📘','📙','📔','📒','📓','📃'];
$page_title = 'รายการโปรด';
include('header.php');
?>
<style>
.fav-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:1.25rem;}
.fav-card{background:white;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.04);display:flex;flex-direction:column;transition:all .25s;}
.fav-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.1);}
.fav-cover{height:130px;display:flex;align-items:center;justify-content:center;font-size:3.2rem;position:relative;}
.fav-body{padding:1rem;flex:1;display:flex;flex-direction:column;gap:.35rem;}
.fav-type{font-size:.68rem;background:#eef2ff;color:#4f46e5;padding:2px 9px;border-radius:50px;font-weight:700;display:inline-block;}
.fav-title{font-weight:700;font-size:.9rem;line-height:1.35;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}
.fav-author{font-size:.76rem;color:#64748b;}
.fav-actions{display:flex;gap:.5rem;margin-top:auto;padding-top:.6rem;}
.btn-detail{flex:1;display:flex;align-items:center;justify-content:center;gap:.35rem;padding:.45rem;background:#eef2ff;color:#4f46e5;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;transition:all .2s;}
.btn-detail:hover{background:#4f46e5;color:white;}
.btn-borrow-sm{flex:1;display:flex;align-items:center;justify-content:center;gap:.35rem;padding:.45rem;background:#4f46e5;color:white;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;transition:all .2s;}
.btn-borrow-sm:hover{background:#4338ca;}
.btn-remove{display:flex;align-items:center;justify-content:center;padding:.45rem .6rem;background:#fef2f2;color:#dc2626;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;transition:all .2s;}
.btn-remove:hover{background:#dc2626;color:white;}
.avail-dot{position:absolute;top:8px;right:8px;font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:50px;}
</style>

<div class="page-header">
  <div class="page-header-inner">
    <div class="page-title"><i class="fas fa-heart" style="color:#e11d48"></i> รายการโปรดของฉัน</div>
    <a href="books.php" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> ค้นหาหนังสือ</a>
  </div>
</div>

<div class="container">

<?php if(isset($_GET['removed'])): ?>
<div class="alert alert-warning"><i class="fas fa-trash"></i> นำออกจากรายการโปรดแล้ว</div>
<?php endif; ?>

<?php if($role === 'guest'): ?>
<div style="background:linear-gradient(135deg,#fff1f2,#fdf2f8);border:1.5px solid #fecdd3;border-radius:16px;padding:3rem;text-align:center;margin-top:1rem;">
  <div style="font-size:3.5rem;margin-bottom:1rem;">💝</div>
  <h3 style="font-size:1.3rem;font-weight:800;margin-bottom:.5rem;color:#1e293b;">รายการโปรดของคุณ</h3>
  <p style="color:#64748b;margin-bottom:1.5rem;font-size:.95rem;max-width:400px;margin-left:auto;margin-right:auto;">เข้าสู่ระบบเพื่อบันทึกหนังสือที่คุณชอบ และดูรายการโปรดทั้งหมดของคุณ</p>
  <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
    <a href="login.php?redirect=favorites.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
    <a href="register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
  </div>
  <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #fecdd3;color:#94a3b8;font-size:.82rem;">
    <i class="fas fa-info-circle"></i> สามารถดูหนังสือและรายละเอียดได้โดยไม่ต้องเข้าสู่ระบบ
  </div>
</div>

<?php elseif(!$favorites || mysqli_num_rows($favorites) === 0): ?>
<div style="text-align:center;padding:4rem 2rem;">
  <div style="font-size:4rem;margin-bottom:1rem;opacity:.3">💔</div>
  <h3 style="font-size:1.2rem;font-weight:700;color:#64748b;margin-bottom:.5rem;">ยังไม่มีรายการโปรด</h3>
  <p style="color:#94a3b8;margin-bottom:1.5rem;">กดปุ่ม <i class="fas fa-heart" style="color:#e11d48"></i> บนหน้ารายละเอียดหนังสือ เพื่อเพิ่มในรายการโปรด</p>
  <a href="books.php" class="btn btn-primary"><i class="fas fa-search"></i> ค้นหาหนังสือ</a>
</div>

<?php else: ?>
<?php $total_fav = mysqli_num_rows($favorites); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.5rem;">
  <div style="font-size:.9rem;color:#64748b;">รายการโปรดทั้งหมด <strong style="color:#1e293b"><?= $total_fav ?> เล่ม</strong></div>
</div>
<div class="fav-grid">
<?php while($b = mysqli_fetch_assoc($favorites)):
  $ci    = ($b['book_id']-1) % 8;
  $avail = ($b['status'] ?? 'available') === 'available';
?>
<div class="fav-card">
  <div class="fav-cover" style="background:<?= $colors[$ci] ?>">
    <?= $icons[$ci] ?>
    <span class="avail-dot" style="background:<?= $avail?'#dcfce7':'#fee2e2' ?>;color:<?= $avail?'#166534':'#991b1b' ?>">
      <?= $avail ? '✓ ว่าง' : '✗ ถูกยืม' ?>
    </span>
  </div>
  <div class="fav-body">
    <span class="fav-type"><?= htmlspecialchars($b['type_name'] ?? '–') ?></span>
    <div class="fav-title" title="<?= htmlspecialchars($b['book_name']) ?>"><?= htmlspecialchars($b['book_name']) ?></div>
    <div class="fav-author"><i class="fas fa-user-edit" style="opacity:.4"></i> <?= htmlspecialchars($b['author'] ?? '–') ?></div>
    <div class="fav-actions">
      <a href="book_detail.php?id=<?= $b['book_id'] ?>" class="btn-detail"><i class="fas fa-info-circle"></i> รายละเอียด</a>
      <?php if($avail): ?>
      <a href="books.php?borrow=<?= $b['book_id'] ?>" class="btn-borrow-sm"><i class="fas fa-book-reader"></i> ยืม</a>
      <?php else: ?>
      <span class="btn-borrow-sm" style="opacity:.5;cursor:default"><i class="fas fa-clock"></i> ไม่ว่าง</span>
      <?php endif; ?>
      <a href="favorites.php?remove=<?= $b['book_id'] ?>" class="btn-remove" onclick="return confirm('นำออกจากรายการโปรด?')"><i class="fas fa-trash"></i></a>
    </div>
  </div>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>
</div>
</body></html>
