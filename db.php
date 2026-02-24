<?php
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "library_db-1";

$conn = mysqli_connect($host, $user, $pass, $dbname);
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:1rem'>
        ❌ เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . mysqli_connect_error() . "
        <br><small>กรุณาตรวจสอบ db.php และตรวจสอบว่า MySQL เปิดใช้งานอยู่</small>
    </div>");
}
?>
