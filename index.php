<?php
session_start();
include('db.php');
$role = $_SESSION['role'] ?? 'guest';
$uid  = (int)($_SESSION['user_id'] ?? 0);

$total_books   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM books"))['c'];
$avail_books   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM books WHERE status='available'"))['c'];
$total_members = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE first_name!='admin'"))['c'];
$latest = mysqli_query($conn,"SELECT * FROM books ORDER BY book_id DESC LIMIT 8");

$my_favorites = [];
if($uid){
    $rf = mysqli_query($conn,"SELECT book_id FROM favorites WHERE user_id=$uid");
    while($row = mysqli_fetch_assoc($rf)) $my_favorites[] = $row['book_id'];
}

$colors = ['linear-gradient(135deg,#6366f1,#8b5cf6)','linear-gradient(135deg,#ec4899,#f43f5e)','linear-gradient(135deg,#06b6d4,#3b82f6)','linear-gradient(135deg,#10b981,#059669)','linear-gradient(135deg,#f59e0b,#ef4444)','linear-gradient(135deg,#8b5cf6,#ec4899)','linear-gradient(135deg,#14b8a6,#6366f1)','linear-gradient(135deg,#f97316,#eab308)'];
$icons  = ['📕','📗','📘','📙','📔','📒','📓','📃'];
$page_title = 'หน้าหลัก';
include('header.php');
?>

<!-- Hero -->
<div style="background:linear-gradient(135deg,#4f46e5,#7c3aed,#ec4899);color:white;padding:4rem 0 3rem;">
  <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap;">
    <div>
      <h1 style="font-size:2.2rem;font-weight:800;margin-bottom:.75rem;line-height:1.3">ยินดีต้อนรับสู่<br>ห้องสมุดดิจิทัล 📚</h1>
      <p style="font-size:1rem;opacity:.85;margin-bottom:1.75rem;max-width:480px;line-height:1.7">ค้นหา ดูรายละเอียด และยืมหนังสือได้ง่ายๆ ผ่านระบบออนไลน์</p>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <a href="books.php" style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:white;color:#4f46e5;border-radius:10px;font-weight:700;font-size:.9rem;text-decoration:none;"><i class="fas fa-search"></i> ค้นหาหนังสือ</a>
        <?php if($role !== 'guest'): ?>
        <a href="favorites.php" style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:rgba(255,255,255,.18);color:white;border:1.5px solid rgba(255,255,255,.4);border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;"><i class="fas fa-heart" style="color:#fca5a5"></i> รายการโปรด</a>
        <?php else: ?>
        <a href="register.php" style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:rgba(255,255,255,.18);color:white;border:1.5px solid rgba(255,255,255,.4);border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;"><i class="fas fa-user-plus"></i> สมัครสมาชิกฟรี</a>
        <?php endif; ?>
      </div>
    </div>
    <div style="font-size:7rem;opacity:.25;user-select:none;">📖</div>
  </div>
</div>

<!-- Stats bar -->
<div style="background:white;border-bottom:1px solid #e2e8f0;padding:1.25rem 0;">
  <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;display:flex;gap:2.5rem;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:.6rem;"><span style="font-size:1.5rem;">📚</span><div><div style="font-size:1.3rem;font-weight:800;color:#4f46e5"><?= $total_books ?></div><div style="font-size:.75rem;color:#64748b">หนังสือทั้งหมด</div></div></div>
    <div style="display:flex;align-items:center;gap:.6rem;"><span style="font-size:1.5rem;">✅</span><div><div style="font-size:1.3rem;font-weight:800;color:#16a34a"><?= $avail_books ?></div><div style="font-size:.75rem;color:#64748b">พร้อมให้ยืม</div></div></div>
    <div style="display:flex;align-items:center;gap:.6rem;"><span style="font-size:1.5rem;">👥</span><div><div style="font-size:1.3rem;font-weight:800;color:#7c3aed"><?= $total_members ?></div><div style="font-size:.75rem;color:#64748b">สมาชิก</div></div></div>
  </div>
</div>

<div class="container" style="margin-top:2rem;">
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-star" style="color:#f59e0b"></i> หนังสือล่าสุด</div>
      <a href="books.php" class="btn btn-outline btn-sm">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;padding:1.25rem;">
    <?php while($b = mysqli_fetch_assoc($latest)):
      $ci    = ($b['book_id']-1) % 8;
      $avail = ($b['status'] ?? 'available') === 'available';
      $i_fav = in_array($b['book_id'], $my_favorites);
    ?>
    <div style="background:white;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.04);display:flex;flex-direction:column;transition:all .2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(0,0,0,.04)'">

 <!-- ปกหนังสือ (คลิกได้) -->
<div style="position:relative;height:130px;overflow:hidden;cursor:pointer;" onclick="showDetail(<?= $b['book_id'] ?>)">
<?php
    $coverSrc = '';
    if(!empty($b['book_image_blob'])){
        $coverSrc = 'data:'.($b['book_image_mime']??'image/jpeg').';base64,'.base64_encode($b['book_image_blob']);
    } elseif(!empty($b['book_image']) && file_exists($b['book_image'])){
        $coverSrc = $b['book_image'];
    }
?>
<?php if($coverSrc): ?>
    <img src="<?= htmlspecialchars($coverSrc) ?>"
         alt="<?= htmlspecialchars($b['book_name']) ?>"
         style="width:100%;height:100%;object-fit:cover;display:block;"
         loading="lazy">
<?php else: ?>
    <img
      src="https://covers.openlibrary.org/b/title/<?= urlencode($b['book_name']) ?>-M.jpg"
      alt="<?= htmlspecialchars($b['book_name']) ?>"
      onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
      style="width:100%;height:100%;object-fit:cover;display:block;"
      loading="lazy"
    >
    <div style="display:none;width:100%;height:100%;background:<?= $colors[$ci] ?>;align-items:center;justify-content:center;font-size:3rem;"><?= $icons[$ci] ?></div>
<?php endif; ?>
<!-- ปุ่มใจบนปก -->
        <?php if($role === 'member'): ?>
        <a href="book_detail.php?id=<?= $b['book_id'] ?>&fav_toggle=1"
           style="position:absolute;top:5px;left:7px;width:26px;height:26px;background:<?= $i_fav?'#dc2626':'rgba(255,255,255,.85)' ?>;color:<?= $i_fav?'white':'#dc2626' ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;box-shadow:0 1px 4px rgba(0,0,0,.2);text-decoration:none;"
           onclick="event.stopPropagation()" title="<?= $i_fav?'ลบออกจากโปรด':'เพิ่มรายการโปรด' ?>">
          <i class="fas fa-heart"></i>
        </a>
        <?php elseif($role === 'guest'): ?>
        <a href="login.php?redirect=favorites.php"
           style="position:absolute;top:5px;left:7px;width:26px;height:26px;background:rgba(255,255,255,.85);color:#dc2626;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;box-shadow:0 1px 4px rgba(0,0,0,.2);text-decoration:none;"
           onclick="event.stopPropagation()" title="Login เพื่อเพิ่มโปรด">
          <i class="far fa-heart"></i>
        </a>
        <?php endif; ?>
      </div>

      <div style="padding:.85rem;flex:1;display:flex;flex-direction:column;gap:.2rem;">
        <div style="font-weight:700;font-size:.875rem;margin-bottom:.15rem;line-height:1.35;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;cursor:pointer;" onclick="showDetail(<?= $b['book_id'] ?>)"><?= htmlspecialchars($b['book_name']) ?></div>
        <div style="font-size:.75rem;color:#64748b;margin-bottom:.4rem;"><?= htmlspecialchars($b['author'] ?? '–') ?></div>
        <div style="font-size:.7rem;background:#eef2ff;color:#4f46e5;padding:2px 8px;border-radius:50px;display:inline-block;font-weight:600;margin-bottom:.5rem;"><?= htmlspecialchars($b['type_name'] ?? '–') ?></div>

        <div style="margin-top:auto;display:flex;flex-direction:column;gap:.3rem;">
          <?php if($avail): ?>
            <?php if($role === 'guest'): ?>
            <a href="login.php?redirect=books.php%3Fborrow%3D<?= $b['book_id'] ?>%26confirm%3D1" style="display:flex;align-items:center;justify-content:center;gap:.35rem;padding:.4rem;background:#f59e0b;color:white;border-radius:7px;font-size:.75rem;font-weight:600;text-decoration:none;"><i class="fas fa-sign-in-alt"></i> Login เพื่อยืม</a>
            <?php else: ?>
            <a href="books.php?borrow=<?= $b['book_id'] ?>" style="display:flex;align-items:center;justify-content:center;gap:.35rem;padding:.4rem;background:#4f46e5;color:white;border-radius:7px;font-size:.75rem;font-weight:600;text-decoration:none;"><i class="fas fa-book-reader"></i> ยืมหนังสือ</a>
            <?php endif; ?>
          <?php else: ?>
          <button onclick="showDetail(<?= $b['book_id'] ?>)" style="display:flex;align-items:center;justify-content:center;gap:.3rem;padding:.4rem;background:#fef9c3;color:#854d0e;border-radius:7px;font-size:.75rem;font-weight:600;border:none;cursor:pointer;width:100%;"><i class="fas fa-clock"></i> ดูกำหนดคืน</button>
          <?php endif; ?>
          <button onclick="showDetail(<?= $b['book_id'] ?>)" style="display:flex;align-items:center;justify-content:center;gap:.3rem;padding:.38rem;background:#f8fafc;color:#475569;border-radius:7px;font-size:.72rem;font-weight:600;border:1px solid #e2e8f0;cursor:pointer;width:100%;transition:.2s;" onmouseover="this.style.background='#eef2ff';this.style.color='#4f46e5'" onmouseout="this.style.background='#f8fafc';this.style.color='#475569'">
            <i class="fas fa-info-circle"></i> ดูรายละเอียด
          </button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
    </div>
  </div>

  <?php if($role === 'guest'): ?>
  <div style="background:linear-gradient(135deg,#eef2ff,#fdf2f8);border:1.5px solid #c7d2fe;border-radius:16px;padding:2rem;text-align:center;margin-bottom:1.5rem;">
    <div style="font-size:2.5rem;margin-bottom:.75rem;">🔐</div>
    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem;">ต้องการยืมหนังสือ?</h3>
    <p style="color:#64748b;margin-bottom:1.25rem;font-size:.9rem;">สมัครสมาชิกฟรีหรือเข้าสู่ระบบ เพื่อยืมหนังสือออนไลน์ได้เลย</p>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
      <a href="register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ====== Modal รายละเอียดหนังสือ (เหมือน books.php) ====== -->
<div id="detailModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:9990;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(4px);" onclick="if(event.target===this)closeDetail()">
<div style="background:white;border-radius:24px;width:100%;max-width:560px;max-height:92vh;overflow-y:auto;position:relative;box-shadow:0 32px 80px rgba(0,0,0,.35);animation:popIn .28s cubic-bezier(.34,1.56,.64,1);">

  <div id="detailLoading" style="padding:4rem;text-align:center;color:#94a3b8;">
    <div style="width:48px;height:48px;border:4px solid #e2e8f0;border-top-color:#4f46e5;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 1rem;"></div>
    <div style="font-weight:600;font-size:.9rem;">กำลังโหลดข้อมูล...</div>
  </div>

  <div id="detailContent" style="display:none;">
    <div id="detailHeader" style="border-radius:24px 24px 0 0;overflow:hidden;">
      <div style="position:relative;height:200px;overflow:hidden;">
        <img id="modalCoverImg" src="" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';document.getElementById('modalCoverFallback').style.display='flex';">
        <div id="modalCoverFallback" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:5rem;"></div>
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75) 0%,rgba(0,0,0,.1) 60%);"></div>
        <button onclick="closeDetail()" style="position:absolute;top:.85rem;right:.85rem;background:rgba(0,0,0,.4);border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;color:white;font-size:1rem;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">✕</button>
        <div style="position:absolute;bottom:0;left:0;right:0;padding:1rem 1.25rem;color:white;">
          <div id="detailTypeBadge" style="display:inline-block;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.35);padding:2px 12px;border-radius:50px;font-size:.7rem;font-weight:700;margin-bottom:.4rem;"></div>
          <div id="detailTitle" style="font-size:1.25rem;font-weight:800;line-height:1.3;text-shadow:0 2px 8px rgba(0,0,0,.4);"></div>
          <div id="detailAuthor" style="font-size:.82rem;opacity:.85;margin-top:.2rem;"></div>
        </div>
      </div>
    </div>

    <div style="display:flex;border-bottom:1px solid #f1f5f9;">
      <div style="flex:1;padding:.85rem;text-align:center;border-right:1px solid #f1f5f9;"><div id="statStatus" style="font-size:1rem;font-weight:800;"></div><div style="font-size:.7rem;color:#94a3b8;margin-top:2px;">สถานะ</div></div>
      <div style="flex:1;padding:.85rem;text-align:center;border-right:1px solid #f1f5f9;"><div id="statBorrow" style="font-size:1rem;font-weight:800;color:#4f46e5;"></div><div style="font-size:.7rem;color:#94a3b8;margin-top:2px;">ครั้งที่ยืม</div></div>
      <div style="flex:1;padding:.85rem;text-align:center;"><div id="statFav" style="font-size:1rem;font-weight:800;color:#e11d48;"></div><div style="font-size:.7rem;color:#94a3b8;margin-top:2px;">❤️ คนโปรด</div></div>
    </div>

    <div id="borrowAlert" style="display:none;margin:1rem 1.25rem 0;background:#fef9c3;border:1.5px solid #fde68a;border-radius:12px;padding:1rem;">
      <div style="font-weight:700;color:#92400e;margin-bottom:.6rem;font-size:.875rem;"><i class="fas fa-clock"></i> กำลังถูกยืมอยู่</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.82rem;">
        <div><div style="color:#94a3b8;font-size:.7rem;font-weight:600;margin-bottom:2px;">วันที่ยืม</div><div id="infoBorrowDate" style="font-weight:700;color:#1e293b;"></div></div>
        <div><div style="color:#94a3b8;font-size:.7rem;font-weight:600;margin-bottom:2px;">กำหนดคืน</div><div id="infoDueDate" style="font-weight:700;color:#dc2626;"></div></div>
      </div>
      <div id="overdueWarn" style="display:none;margin-top:.6rem;background:#fee2e2;color:#991b1b;border-radius:8px;padding:.4rem .7rem;font-size:.78rem;font-weight:700;"><i class="fas fa-exclamation-triangle"></i> เกินกำหนดคืนแล้ว!</div>
    </div>

    <div style="padding:1rem 1.25rem;">
      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.65rem;">📋 ข้อมูลหนังสือ</div>
      <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:.55rem 0;color:#64748b;font-weight:600;width:110px;">รหัส</td><td id="infoId" style="padding:.55rem 0;color:#1e293b;font-weight:500;"></td></tr>
        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:.55rem 0;color:#64748b;font-weight:600;">ชื่อหนังสือ</td><td id="infoName" style="padding:.55rem 0;color:#1e293b;font-weight:500;"></td></tr>
        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:.55rem 0;color:#64748b;font-weight:600;">ผู้แต่ง</td><td id="infoAuthor" style="padding:.55rem 0;color:#1e293b;font-weight:500;"></td></tr>
        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:.55rem 0;color:#64748b;font-weight:600;">ประเภท</td><td id="infoType" style="padding:.55rem 0;"></td></tr>
        <tr><td style="padding:.55rem 0;color:#64748b;font-weight:600;">สถานะ</td><td id="infoStatus" style="padding:.55rem 0;"></td></tr>
      </table>
    </div>

    <div id="historySection" style="padding:0 1.25rem 1rem;display:none;">
      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.65rem;">🕐 ประวัติการยืมล่าสุด</div>
      <div id="historyList"></div>
    </div>

    <div style="padding:1rem 1.25rem 1.5rem;display:flex;gap:.6rem;border-top:1px solid #f1f5f9;">
      <a id="btnBorrow" href="#" style="flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.7rem;background:#4f46e5;color:white;border-radius:10px;font-size:.85rem;font-weight:700;text-decoration:none;"><i class="fas fa-book-reader"></i> ยืมหนังสือ</a>
      <a id="btnDetail" href="#" style="flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.7rem;background:#f8fafc;color:#475569;border-radius:10px;font-size:.85rem;font-weight:700;text-decoration:none;border:1px solid #e2e8f0;"><i class="fas fa-external-link-alt"></i> หน้ารายละเอียด</a>
    </div>
  </div>
</div>
</div>

<style>
@keyframes popIn{from{transform:scale(.88) translateY(20px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
@keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
const COLORS = <?= json_encode($colors) ?>;
const ICONS  = ['📕','📗','📘','📙','📔','📒','📓','📃'];
const ROLE   = '<?= $role ?>';

function thDate(str){
  if(!str) return '–';
  return new Date(str).toLocaleDateString('th-TH',{day:'2-digit',month:'short',year:'numeric'});
}

function showDetail(bookId){
  const modal = document.getElementById('detailModal');
  modal.style.display = 'flex';
  document.getElementById('detailLoading').style.display = 'block';
  document.getElementById('detailContent').style.display = 'none';
  document.getElementById('borrowAlert').style.display = 'none';
  document.getElementById('historySection').style.display = 'none';

  fetch('book_info_api.php?id=' + bookId)
    .then(r => r.json())
    .then(b => {
      const ci    = (b.book_id - 1) % 8;
      const avail = b.status === 'available';

      // ปก
      const coverImg = document.getElementById('modalCoverImg');
      const fallback = document.getElementById('modalCoverFallback');
      coverImg.style.display = 'block';
      fallback.style.display = 'none';
      fallback.style.background = COLORS[ci];
      fallback.textContent = ICONS[ci];
      if(b.book_image){
        coverImg.src = b.book_image;
      } else {
        coverImg.src = 'https://covers.openlibrary.org/b/title/' + encodeURIComponent(b.book_name) + '-L.jpg';
      }
      coverImg.onerror = function(){ this.style.display='none'; fallback.style.display='flex'; };

      document.getElementById('detailTypeBadge').textContent = '🏷 ' + b.type_name;
      document.getElementById('detailTitle').textContent     = b.book_name;
      document.getElementById('detailAuthor').innerHTML      = '<i class="fas fa-user-edit" style="opacity:.6"></i> ' + b.author;

      document.getElementById('statStatus').innerHTML = avail ? '<span style="color:#16a34a">ว่าง</span>' : '<span style="color:#dc2626">📤 ถูกยืม</span>';
      document.getElementById('statBorrow').textContent = b.borrow_count + ' ครั้ง';
      document.getElementById('statFav').textContent    = b.fav_count + ' คน';

      document.getElementById('infoId').textContent     = '#' + b.book_id;
      document.getElementById('infoName').textContent   = b.book_name;
      document.getElementById('infoAuthor').textContent = b.author;
      document.getElementById('infoType').innerHTML     = '<span style="background:#eef2ff;color:#4f46e5;padding:2px 12px;border-radius:50px;font-size:.78rem;font-weight:700;">' + b.type_name + '</span>';
      document.getElementById('infoStatus').innerHTML   = avail ? '<span style="color:#16a34a;font-weight:700;">ว่าง</span>' : '<span style="color:#dc2626;font-weight:700;">📤 ถูกยืมแล้ว</span>';

      if(!avail && b.current_borrow){
        const dueD = b.due_date ? new Date(b.due_date) : null;
        document.getElementById('borrowAlert').style.display  = 'block';
        document.getElementById('infoBorrowDate').textContent = thDate(b.current_borrow.borrow_date);
        document.getElementById('infoDueDate').textContent    = thDate(b.due_date);
        document.getElementById('overdueWarn').style.display  = (dueD && new Date() > dueD) ? 'block' : 'none';
      }

      if(b.history && b.history.length > 0){
        document.getElementById('historySection').style.display = 'block';
        const statMap = {
          borrowed:'<span style="color:#2563eb;font-weight:700;font-size:.75rem;">📖 ยืมอยู่</span>',
          overdue :'<span style="color:#dc2626;font-weight:700;font-size:.75rem;">⚠️ เกินกำหนด</span>',
          returned:'<span style="color:#16a34a;font-weight:700;font-size:.75rem;">✅ คืนแล้ว</span>'
        };
        document.getElementById('historyList').innerHTML = b.history.map(h =>
          `<div style="display:flex;align-items:center;gap:.5rem;padding:.5rem .7rem;background:#f8fafc;border-radius:8px;margin-bottom:.35rem;font-size:.8rem;">
            <i class="fas fa-user-circle" style="color:#94a3b8;font-size:1rem;flex-shrink:0;"></i>
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${h.fullname}</div>
              <div style="color:#94a3b8;font-size:.72rem;">${thDate(h.borrow_date)} → ${thDate(h.return_date)}</div>
            </div>
            <div style="flex-shrink:0;">${statMap[h.status]||h.status}</div>
          </div>`
        ).join('');
      }

      const btnBorrow = document.getElementById('btnBorrow');
      if(avail){
        if(ROLE === 'guest'){
          btnBorrow.href = 'login.php?redirect=books.php%3Fborrow%3D' + b.book_id + '%26confirm%3D1';
          btnBorrow.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login เพื่อยืม';
          btnBorrow.style.cssText = 'flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.7rem;background:#f59e0b;color:white;border-radius:10px;font-size:.85rem;font-weight:700;text-decoration:none;';
        } else {
          btnBorrow.href = 'books.php?borrow=' + b.book_id;
          btnBorrow.innerHTML = '<i class="fas fa-book-reader"></i> ยืมหนังสือ';
          btnBorrow.style.cssText = 'flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.7rem;background:#4f46e5;color:white;border-radius:10px;font-size:.85rem;font-weight:700;text-decoration:none;';
        }
      } else {
        btnBorrow.href = '#';
        btnBorrow.innerHTML = '<i class="fas fa-clock"></i> ไม่ว่างในขณะนี้';
        btnBorrow.style.cssText = 'flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.7rem;background:#f1f5f9;color:#94a3b8;border-radius:10px;font-size:.85rem;font-weight:700;text-decoration:none;pointer-events:none;';
      }
      document.getElementById('btnDetail').href = 'book_detail.php?id=' + b.book_id;

      document.getElementById('detailLoading').style.display = 'none';
      document.getElementById('detailContent').style.display = 'block';
    })
    .catch(() => {
      document.getElementById('detailLoading').innerHTML = '<div style="color:#dc2626;padding:2rem;text-align:center;">❌ โหลดข้อมูลไม่สำเร็จ</div>';
    });
}

function closeDetail(){
  document.getElementById('detailModal').style.display = 'none';
}
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeDetail(); });
</script>
</body></html>
