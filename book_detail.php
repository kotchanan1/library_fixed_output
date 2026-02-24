<?php
session_start();
include('db.php');
$role = $_SESSION['role'] ?? 'guest';
$uid  = (int)($_SESSION['user_id'] ?? 0);

$book_id = (int)($_GET['id'] ?? 0);
if(!$book_id){ header("Location: books.php"); exit; }

$book = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM books WHERE book_id=$book_id"));
if(!$book){ header("Location: books.php"); exit; }

// เช็คว่า favorite อยู่ไหม
$is_fav = false;
if($uid){
    $fav_chk = mysqli_fetch_assoc(mysqli_query($conn,"SELECT favorite_id FROM favorites WHERE user_id=$uid AND book_id=$book_id"));
    $is_fav = (bool)$fav_chk;
}

// toggle favorite
$fav_msg = '';
if(isset($_GET['fav_toggle']) && $uid){
    if($is_fav){
        mysqli_query($conn,"DELETE FROM favorites WHERE user_id=$uid AND book_id=$book_id");
        $is_fav = false; $fav_msg = 'removed';
    } else {
        mysqli_query($conn,"INSERT IGNORE INTO favorites(user_id,book_id) VALUES($uid,$book_id)");
        $is_fav = true; $fav_msg = 'added';
    }
}

// สถิติยืม
$borrow_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM borrow_history WHERE book_id=$book_id"))['c'];
$fav_count    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM favorites WHERE book_id=$book_id"))['c'];

// หนังสือแนะนำ (ประเภทเดียวกัน)
$related = mysqli_query($conn,"SELECT * FROM books WHERE type_name='".mysqli_real_escape_string($conn,$book['type_name'])."' AND book_id!=$book_id LIMIT 4");

$avail = ($book['status'] ?? 'available') === 'available';
$colors = ['linear-gradient(135deg,#6366f1,#8b5cf6)','linear-gradient(135deg,#ec4899,#f43f5e)','linear-gradient(135deg,#06b6d4,#3b82f6)','linear-gradient(135deg,#10b981,#059669)','linear-gradient(135deg,#f59e0b,#ef4444)','linear-gradient(135deg,#8b5cf6,#ec4899)','linear-gradient(135deg,#14b8a6,#6366f1)','linear-gradient(135deg,#f97316,#eab308)'];
$icons  = ['📕','📗','📘','📙','📔','📒','📓','📃'];
$ci = ($book_id - 1) % 8;

$page_title = htmlspecialchars($book['book_name']);
include('header.php');
?>
<style>
.detail-hero{background:<?= $colors[$ci] ?>;min-height:320px;display:flex;align-items:center;padding:3rem 0;}
.detail-cover{width:200px;height:260px;background:rgba(255,255,255,.15);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:5rem;flex-shrink:0;box-shadow:0 20px 60px rgba(0,0,0,.25);border:2px solid rgba(255,255,255,.2);}
.detail-info{color:white;flex:1;}
.detail-info h1{font-size:2rem;font-weight:800;line-height:1.3;margin-bottom:.5rem;}
.detail-info .author{font-size:1.1rem;opacity:.85;margin-bottom:.75rem;}
.detail-info .type-badge{display:inline-block;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.35);color:white;padding:.3rem .9rem;border-radius:50px;font-size:.82rem;font-weight:600;margin-bottom:1.25rem;}
.status-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:50px;font-size:.85rem;font-weight:700;}
.status-avail{background:#dcfce7;color:#166534;}
.status-borrow{background:#fee2e2;color:#991b1b;}
.action-bar{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.5rem;}
.btn-borrow{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.75rem;background:white;color:#4f46e5;border-radius:12px;font-weight:700;font-size:.95rem;text-decoration:none;transition:all .2s;}
.btn-borrow:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);}
.btn-fav{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;border-radius:12px;font-weight:700;font-size:.95rem;text-decoration:none;border:2px solid rgba(255,255,255,.5);color:white;background:rgba(255,255,255,.15);transition:all .2s;cursor:pointer;}
.btn-fav:hover{background:rgba(255,255,255,.28);}
.btn-fav.active{background:rgba(255,255,255,.9);color:#e11d48;border-color:white;}
.stat-pill{display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:rgba(255,255,255,.8);}
.info-card{background:white;border-radius:16px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem;}
.info-row{display:flex;gap:.5rem;padding:.65rem 0;border-bottom:1px solid #f1f5f9;font-size:.9rem;}
.info-row:last-child{border-bottom:none;}
.info-label{color:#64748b;font-weight:600;min-width:130px;}
.info-value{color:#1e293b;font-weight:500;}
.related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;}
.related-card{background:white;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;text-decoration:none;color:inherit;transition:all .2s;display:block;}
.related-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1);}
.toast{position:fixed;top:1.5rem;right:1.5rem;z-index:9999;background:#1e293b;color:white;padding:.85rem 1.4rem;border-radius:12px;font-size:.88rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.2);display:flex;align-items:center;gap:.5rem;animation:slideIn .3s ease;}
@keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
</style>

<?php if($fav_msg): ?>
<div class="toast" id="toast">
  <?= $fav_msg==='added' ? '❤️ เพิ่มในรายการโปรดแล้ว' : '🗑️ นำออกจากรายการโปรดแล้ว' ?>
</div>
<script>setTimeout(()=>document.getElementById('toast').remove(),3000)</script>
<?php endif; ?>

<!-- Hero -->
<div class="detail-hero">
  <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;display:flex;gap:2.5rem;align-items:center;flex-wrap:wrap;">
    <div class="detail-cover"><?= $icons[$ci] ?></div>
    <div class="detail-info">
      <div class="type-badge"><i class="fas fa-tag"></i> <?= htmlspecialchars($book['type_name'] ?? '–') ?></div>
      <h1><?= htmlspecialchars($book['book_name']) ?></h1>
      <div class="author"><i class="fas fa-user-edit" style="opacity:.6"></i> โดย <?= htmlspecialchars($book['author'] ?? '–') ?></div>
      <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1rem;">
        <span class="stat-pill"><i class="fas fa-book-reader"></i> ยืมแล้ว <?= $borrow_count ?> ครั้ง</span>
        <span class="stat-pill"><i class="fas fa-heart"></i> <?= $fav_count ?> คนโปรด</span>
      </div>
      <div>
        <?php if($avail): ?>
        <span class="status-pill status-avail"><i class="fas fa-check-circle"></i> พร้อมให้ยืม</span>
        <?php else: ?>
        <span class="status-pill status-borrow"><i class="fas fa-times-circle"></i> ถูกยืมแล้ว</span>
        <?php endif; ?>
      </div>
      <div class="action-bar">
        <?php if($avail): ?>
          <?php if($role === 'guest'): ?>
          <a href="login.php?redirect=books.php%3Fborrow%3D<?= $book_id ?>%26confirm=1" class="btn-borrow"><i class="fas fa-sign-in-alt"></i> Login เพื่อยืม</a>
          <?php else: ?>
          <a href="books.php?borrow=<?= $book_id ?>" class="btn-borrow"><i class="fas fa-book-reader"></i> ยืมหนังสือ</a>
          <?php endif; ?>
        <?php else: ?>
        <span class="btn-borrow" style="opacity:.5;cursor:default"><i class="fas fa-clock"></i> ไม่ว่างในขณะนี้</span>
        <?php endif; ?>

        <?php if($role === 'guest'): ?>
        <a href="login.php?redirect=book_detail.php%3Fid%3D<?= $book_id ?>%26fav_toggle=1" class="btn-fav <?= $is_fav?'active':'' ?>">
          <i class="fas fa-heart"></i> <?= $is_fav ? 'ในรายการโปรด':'เพิ่มโปรด' ?>
        </a>
        <?php else: ?>
        <a href="book_detail.php?id=<?= $book_id ?>&fav_toggle=1" class="btn-fav <?= $is_fav?'active':'' ?>">
          <i class="fas fa-heart"></i> <?= $is_fav ? 'ในรายการโปรดแล้ว':'เพิ่มในรายการโปรด' ?>
        </a>
        <?php endif; ?>

        <a href="javascript:history.back()" class="btn-fav"><i class="fas fa-arrow-left"></i> ย้อนกลับ</a>
      </div>
    </div>
  </div>
</div>

<div class="container" style="margin-top:2rem;">
  <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;" class="detail-layout">

    <!-- Info -->
    <div>
      <div class="info-card">
        <div style="font-size:1rem;font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;"><i class="fas fa-info-circle" style="color:#4f46e5"></i> รายละเอียดหนังสือ</div>
        <div class="info-row"><span class="info-label">ชื่อหนังสือ</span><span class="info-value"><?= htmlspecialchars($book['book_name']) ?></span></div>
        <div class="info-row"><span class="info-label">ผู้แต่ง</span><span class="info-value"><?= htmlspecialchars($book['author'] ?? '–') ?></span></div>
        <div class="info-row"><span class="info-label">ประเภท</span><span class="info-value"><span style="background:#eef2ff;color:#4f46e5;padding:2px 10px;border-radius:50px;font-size:.82rem;font-weight:600;"><?= htmlspecialchars($book['type_name'] ?? '–') ?></span></span></div>
        <div class="info-row"><span class="info-label">สถานะ</span><span class="info-value"><?= $avail ? '<span style="color:#16a34a;font-weight:700">✅ พร้อมให้ยืม</span>' : '<span style="color:#dc2626;font-weight:700">❌ ถูกยืมแล้ว</span>' ?></span></div>
        <div class="info-row"><span class="info-label">ถูกยืมทั้งหมด</span><span class="info-value"><?= $borrow_count ?> ครั้ง</span></div>
        <div class="info-row"><span class="info-label">เพิ่มในรายการโปรด</span><span class="info-value"><?= $fav_count ?> คน</span></div>
        <div class="info-row"><span class="info-label">รหัสหนังสือ</span><span class="info-value text-muted">#<?= $book['book_id'] ?></span></div>
      </div>

      <!-- Related -->
      <?php $related_arr = mysqli_fetch_all($related, MYSQLI_ASSOC); ?>
      <?php if(count($related_arr) > 0): ?>
      <div class="info-card">
        <div style="font-size:1rem;font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;"><i class="fas fa-th-large" style="color:#4f46e5"></i> หนังสือในประเภทเดียวกัน</div>
        <div class="related-grid">
        <?php foreach($related_arr as $r):
          $rci = ($r['book_id']-1) % 8;
          $ravail = ($r['status'] ?? 'available') === 'available';
        ?>
        <a href="book_detail.php?id=<?= $r['book_id'] ?>" class="related-card">
          <div style="background:<?= $colors[$rci] ?>;height:80px;display:flex;align-items:center;justify-content:center;font-size:2rem;position:relative;">
            <?= $icons[$rci] ?>
            <span style="position:absolute;top:5px;right:6px;font-size:.6rem;font-weight:700;padding:1px 6px;border-radius:50px;background:<?= $ravail?'#dcfce7':'#fee2e2' ?>;color:<?= $ravail?'#166534':'#991b1b' ?>"><?= $ravail?'ว่าง':'ยืม' ?></span>
          </div>
          <div style="padding:.75rem;">
            <div style="font-weight:700;font-size:.8rem;line-height:1.3;margin-bottom:.2rem;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?= htmlspecialchars($r['book_name']) ?></div>
            <div style="font-size:.72rem;color:#64748b;"><?= htmlspecialchars($r['author'] ?? '') ?></div>
          </div>
        </a>
        <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div>
      <div class="info-card" style="text-align:center;">
        <div style="font-size:4rem;margin-bottom:1rem;"><?= $icons[$ci] ?></div>
        <?php if($avail): ?>
          <?php if($role === 'guest'): ?>
          <a href="login.php?redirect=books.php%3Fborrow%3D<?= $book_id ?>%26confirm=1" class="btn btn-primary" style="width:100%;margin-bottom:.75rem;justify-content:center;"><i class="fas fa-sign-in-alt"></i> Login เพื่อยืม</a>
          <?php else: ?>
          <a href="books.php?borrow=<?= $book_id ?>" class="btn btn-primary" style="width:100%;margin-bottom:.75rem;justify-content:center;"><i class="fas fa-book-reader"></i> ยืมหนังสือ</a>
          <?php endif; ?>
        <?php else: ?>
        <div class="btn btn-secondary" style="width:100%;margin-bottom:.75rem;justify-content:center;cursor:default;opacity:.6"><i class="fas fa-clock"></i> ไม่ว่างในขณะนี้</div>
        <?php endif; ?>

        <?php if($role === 'guest'): ?>
        <a href="login.php?redirect=book_detail.php%3Fid%3D<?= $book_id ?>%26fav_toggle=1" class="btn" style="width:100%;justify-content:center;background:<?= $is_fav?'#fef2f2':'#f8fafc' ?>;color:<?= $is_fav?'#e11d48':'#64748b' ?>;border:1.5px solid <?= $is_fav?'#fecaca':'#e2e8f0' ?>">
          <i class="fas fa-heart"></i> <?= $is_fav?'ในรายการโปรด':'เพิ่มรายการโปรด' ?>
        </a>
        <?php else: ?>
        <a href="book_detail.php?id=<?= $book_id ?>&fav_toggle=1" class="btn" style="width:100%;justify-content:center;background:<?= $is_fav?'#fef2f2':'#f8fafc' ?>;color:<?= $is_fav?'#e11d48':'#64748b' ?>;border:1.5px solid <?= $is_fav?'#fecaca':'#e2e8f0' ?>">
          <i class="fas fa-heart"></i> <?= $is_fav?'นำออกจากโปรด':'เพิ่มรายการโปรด' ?>
        </a>
        <?php endif; ?>
      </div>

      <div class="info-card" style="background:#f8fafc;">
        <div style="font-size:.82rem;color:#64748b;font-weight:600;margin-bottom:.5rem;"><i class="fas fa-lightbulb" style="color:#f59e0b"></i> เกี่ยวกับการยืม</div>
        <ul style="font-size:.82rem;color:#64748b;padding-left:1.1rem;line-height:1.9;">
          <li>ยืมได้ครั้งละ 1 เล่มต่อชื่อ</li>
          <li>กำหนดคืนตามที่เจ้าหน้าที่กำหนด</li>
          <li>คืนช้ามีค่าปรับตามประเภท</li>
          <li>ต้อง Login ก่อนยืม</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<style>
@media(max-width:768px){
  .detail-layout{grid-template-columns:1fr !important;}
  .detail-hero .detail-cover{display:none}
  .detail-info h1{font-size:1.4rem}
}
</style>
</body></html>
