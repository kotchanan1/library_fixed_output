<?php
// header.php - Navigation สำหรับทุก role รวมถึง guest
$page_title = $page_title ?? 'OPAC Library';
$role = $_SESSION['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> - ระบบห้องสมุด</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Tahoma,sans-serif;background:#f1f5f9;color:#1e293b;min-height:100vh;}
a{text-decoration:none;color:inherit;}
.navbar{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;padding:0;box-shadow:0 4px 16px rgba(79,70,229,.3);position:sticky;top:0;z-index:100;}
.nav-container{max-width:1280px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
.nav-brand{display:flex;align-items:center;gap:.6rem;padding:.85rem 0;font-size:1.2rem;font-weight:800;color:white;}
.nav-brand i{font-size:1.4rem;}
.nav-brand span small{font-size:.55rem;background:rgba(255,255,255,.25);padding:2px 6px;border-radius:4px;margin-left:4px;vertical-align:middle;}
.nav-links{display:flex;align-items:center;gap:.15rem;flex:1;flex-wrap:wrap;}
.nav-link{display:flex;align-items:center;gap:.4rem;padding:.6rem .95rem;border-radius:8px;font-size:.85rem;font-weight:500;color:rgba(255,255,255,.85);transition:all .2s;white-space:nowrap;}
.nav-link:hover,.nav-link.active{background:rgba(255,255,255,.2);color:white;}
.nav-right{display:flex;align-items:center;gap:.6rem;margin-left:auto;flex-wrap:wrap;}
.nav-user{display:flex;align-items:center;gap:.5rem;padding:.4rem .8rem;border-radius:50px;background:rgba(255,255,255,.15);font-size:.85rem;color:white;}
.nav-avatar{width:28px;height:28px;background:rgba(255,255,255,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;}
.role-badge{font-size:.65rem;padding:2px 7px;border-radius:50px;font-weight:700;}
.role-badge.admin{background:#fbbf24;color:#78350f;}
.role-badge.member{background:#34d399;color:#064e3b;}
.btn-nav{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .2s;border:none;}
.btn-nav-white{background:white;color:#4f46e5;}
.btn-nav-white:hover{background:#eef2ff;}
.btn-nav-outline{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35)!important;color:white;}
.btn-nav-outline:hover{background:rgba(255,255,255,.28);}
.btn-nav-logout{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3)!important;color:white;}
.btn-nav-logout:hover{background:rgba(239,68,68,.55);}

/* Layout */
.page-header{background:white;border-bottom:1px solid #e2e8f0;padding:1.25rem 0;margin-bottom:1.5rem;}
.page-header-inner{max-width:1280px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;}
.page-title{font-size:1.3rem;font-weight:700;display:flex;align-items:center;gap:.5rem;}
.page-title i{color:#4f46e5;}
.container{max-width:1280px;margin:0 auto;padding:0 1.5rem 3rem;}

/* Cards */
.card{background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 2px 12px rgba(0,0,0,.04);overflow:hidden;margin-bottom:1.5rem;}
.card-header{padding:1.1rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;}
.card-title{font-size:1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;}
.card-body{padding:1.5rem;}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;border:none;transition:all .2s;text-decoration:none;}
.btn:hover{transform:translateY(-1px);}
.btn-primary{background:#4f46e5;color:white;}
.btn-primary:hover{background:#4338ca;}
.btn-success{background:#16a34a;color:white;}
.btn-success:hover{background:#15803d;}
.btn-warning{background:#d97706;color:white;}
.btn-warning:hover{background:#b45309;}
.btn-danger{background:#dc2626;color:white;}
.btn-danger:hover{background:#b91c1c;}
.btn-secondary{background:#64748b;color:white;}
.btn-secondary:hover{background:#475569;}
.btn-outline{background:transparent;border:1.5px solid #4f46e5;color:#4f46e5;}
.btn-outline:hover{background:#4f46e5;color:white;}
.btn-sm{padding:.35rem .75rem;font-size:.78rem;}
.btn-xs{padding:.25rem .55rem;font-size:.72rem;}

/* Table */
.table-wrap{overflow-x:auto;}
table.tbl{width:100%;border-collapse:collapse;font-size:.875rem;}
table.tbl th{background:#f8fafc;padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;border-bottom:2px solid #e2e8f0;white-space:nowrap;}
table.tbl td{padding:.85rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
table.tbl tr:last-child td{border-bottom:none;}
table.tbl tr:hover td{background:#f8fafc;}

/* Badges */
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .65rem;border-radius:50px;font-size:.72rem;font-weight:700;white-space:nowrap;}
.badge-green{background:#dcfce7;color:#166534;}
.badge-red{background:#fee2e2;color:#991b1b;}
.badge-yellow{background:#fef9c3;color:#854d0e;}
.badge-blue{background:#dbeafe;color:#1e40af;}
.badge-gray{background:#f1f5f9;color:#475569;}
.badge-purple{background:#f3e8ff;color:#6b21a8;}

/* Form */
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#374151;}
.form-control{width:100%;padding:.65rem .9rem;border:2px solid #e2e8f0;border-radius:10px;font-size:.9rem;color:#1e293b;transition:border-color .2s;outline:none;background:white;}
.form-control:focus{border-color:#4f46e5;}
.form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;}
.form-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .75rem center;background-size:1rem;padding-right:2.5rem;}

/* Alert */
.alert{padding:.85rem 1.2rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem;display:flex;align-items:center;gap:.5rem;}
.alert-success{background:#dcfce7;color:#166534;border:1px solid #bbf7d0;}
.alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
.alert-warning{background:#fef9c3;color:#854d0e;border:1px solid #fde68a;}
.alert-info{background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;}

/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:white;border-radius:16px;padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,.04);}
.stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;}
.stat-value{font-size:1.7rem;font-weight:800;line-height:1;}
.stat-label{font-size:.75rem;color:#64748b;margin-top:2px;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal-box{background:white;border-radius:20px;padding:2rem;width:90%;max-width:500px;position:relative;max-height:90vh;overflow-y:auto;}
.modal-title{font-size:1.2rem;font-weight:800;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.modal-close{position:absolute;top:1rem;right:1rem;background:#f1f5f9;border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;font-size:1rem;color:#64748b;display:flex;align-items:center;justify-content:center;}
.modal-close:hover{background:#e2e8f0;}

/* Misc */
.text-muted{color:#64748b;font-size:.82rem;}
.text-center{text-align:center;}
.mt-1{margin-top:.5rem;}.mt-2{margin-top:1rem;}.mt-3{margin-top:1.5rem;}
.flex{display:flex;}.gap-1{gap:.5rem;}.items-center{align-items:center;}
.search-bar{display:flex;align-items:center;gap:.5rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:.5rem .85rem;max-width:320px;}
.search-bar i{color:#94a3b8;font-size:.9rem;}
.search-bar input{border:none;background:transparent;outline:none;font-size:.875rem;color:#1e293b;width:100%;}
.search-bar:focus-within{border-color:#4f46e5;}
.empty-state{text-align:center;padding:4rem 2rem;color:#94a3b8;}
.empty-state i{font-size:3.5rem;margin-bottom:1rem;display:block;opacity:.3;}
.overdue-row td{background:#fff5f5!important;}
</style>
</head>
<body>

<nav class="navbar">
<div class="nav-container">
  <a href="index.php" class="nav-brand">
    <i class="fas fa-book-open"></i>
    <span>OPAC Library <small>PRO</small></span>
  </a>

  <div class="nav-links">
  <?php if($role === 'admin'): ?>
    <a href="admin_home.php"     class="nav-link <?= basename($_SERVER['PHP_SELF'])==='admin_home.php'     ?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="book_list.php"      class="nav-link <?= basename($_SERVER['PHP_SELF'])==='book_list.php'      ?'active':'' ?>"><i class="fas fa-book"></i> หนังสือ</a>
    <a href="member_list.php"    class="nav-link <?= basename($_SERVER['PHP_SELF'])==='member_list.php'    ?'active':'' ?>"><i class="fas fa-users"></i> สมาชิก</a>
    <a href="borrow_manage.php"  class="nav-link <?= basename($_SERVER['PHP_SELF'])==='borrow_manage.php'  ?'active':'' ?>"><i class="fas fa-book-reader"></i> การยืม-คืน</a>
    <a href="payment_manage.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='payment_manage.php' ?'active':'' ?>"><i class="fas fa-money-bill-wave"></i> ค่าปรับ</a>
  <?php elseif($role === 'member'): ?>
    <a href="index.php"     class="nav-link <?= basename($_SERVER['PHP_SELF'])==='index.php'     ?'active':'' ?>"><i class="fas fa-home"></i> หน้าหลัก</a>
    <a href="books.php"     class="nav-link <?= basename($_SERVER['PHP_SELF'])==='books.php'     ?'active':'' ?>"><i class="fas fa-book"></i> รายการหนังสือ</a>
    <a href="my_borrow.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='my_borrow.php' ?'active':'' ?>"><i class="fas fa-history"></i> ประวัติการยืม</a>
    <a href="my_fines.php"  class="nav-link <?= basename($_SERVER['PHP_SELF'])==='my_fines.php'  ?'active':'' ?>"><i class="fas fa-exclamation-circle"></i> ค่าปรับของฉัน</a>
    <a href="favorites.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='favorites.php' ?'active':'' ?>"><i class="fas fa-heart" style="color:#fca5a5"></i> รายการโปรด</a>
  <?php else: ?>
    <!-- Guest - ดูได้ทุกหน้า ยกเว้นการยืมหนังสือ -->
    <a href="index.php"   class="nav-link <?= basename($_SERVER['PHP_SELF'])==='index.php'   ?'active':'' ?>"><i class="fas fa-home"></i> หน้าหลัก</a>
    <a href="books.php"   class="nav-link <?= basename($_SERVER['PHP_SELF'])==='books.php'   ?'active':'' ?>"><i class="fas fa-book"></i> รายการหนังสือ</a>
  <?php endif; ?>
  </div>

  <div class="nav-right">
  <?php if($role === 'guest'): ?>
    <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-nav btn-nav-white">
      <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
    </a>
    <a href="register.php" class="btn-nav btn-nav-outline">
      <i class="fas fa-user-plus"></i> สมัครสมาชิก
    </a>
  <?php else: ?>
    <div class="nav-user">
      <div class="nav-avatar"><i class="fas fa-user"></i></div>
      <span><?= htmlspecialchars($_SESSION['fullname'] ?? '') ?></span>
      <span class="role-badge <?= $role ?>"><?= $role === 'admin' ? 'ADMIN' : 'สมาชิก' ?></span>
    </div>
    <a href="logout.php" class="btn-nav btn-nav-logout">
      <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
    </a>
  <?php endif; ?>
  </div>
</div>
</nav>
