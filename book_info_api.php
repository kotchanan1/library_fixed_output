<?php
session_start();
include('db.php');
header('Content-Type: application/json; charset=utf-8');

// เพิ่ม column สำหรับเก็บ path, BLOB และ mime ถ้ายังไม่มี
@mysqli_query($conn, "ALTER TABLE books ADD COLUMN IF NOT EXISTS book_image VARCHAR(255) DEFAULT NULL");
@mysqli_query($conn, "ALTER TABLE books ADD COLUMN IF NOT EXISTS book_image_blob LONGBLOB DEFAULT NULL");
@mysqli_query($conn, "ALTER TABLE books ADD COLUMN IF NOT EXISTS book_image_mime VARCHAR(50) DEFAULT NULL");

$book_id = (int)($_GET['id'] ?? 0);
if(!$book_id){ echo json_encode(['error'=>'not found']); exit; }

$book = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM books WHERE book_id=$book_id"));
if(!$book){ echo json_encode(['error'=>'not found']); exit; }

$borrow_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM borrow_history WHERE book_id=$book_id"))['c'];
$fav_count    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM favorites WHERE book_id=$book_id"))['c'];

// ข้อมูลผู้ที่กำลังยืมอยู่ — ใช้ return_date จาก DB (บันทึกตอนยืมแล้ว +15 วัน)
$current_borrow = null;
$cbq = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT bh.borrow_date, bh.return_date, bh.status,
           CONCAT(u.first_name,' ',u.last_name) AS fullname
    FROM borrow_history bh JOIN users u ON bh.user_id=u.user_id
    WHERE bh.book_id=$book_id AND bh.status IN('borrowed','overdue')
    ORDER BY bh.history_id DESC LIMIT 1"));
if($cbq) $current_borrow = $cbq;

// due_date ใช้ return_date จาก DB ถ้ามี ไม่งั้นคำนวณ +15 วัน
$due_date = null;
if($current_borrow){
    $due_date = $current_borrow['return_date']
        ?? date('Y-m-d', strtotime($current_borrow['borrow_date'] . ' +15 days'));
}

// ประวัติการยืมล่าสุด 5 รายการ
$history = [];
$hq = mysqli_query($conn,"
    SELECT CONCAT(u.first_name,' ',u.last_name) AS fullname,
           bh.borrow_date, bh.return_date, bh.status
    FROM borrow_history bh JOIN users u ON bh.user_id=u.user_id
    WHERE bh.book_id=$book_id ORDER BY bh.history_id DESC LIMIT 5");
while($h = mysqli_fetch_assoc($hq)) $history[] = $h;

// build image URL: ถ้ามี blob ให้ส่งเป็น data-uri เพื่อให้โค้ดฝั่ง client ไม่ต้องรู้เรื่อง blob
$imageUrl = null;
if(!empty($book['book_image_blob'])){
    $mime = $book['book_image_mime'] ?? 'image/jpeg';
    $imageUrl = 'data:'.$mime.';base64,'.base64_encode($book['book_image_blob']);
} elseif(!empty($book['book_image'])){
    $imageUrl = $book['book_image'];
}

echo json_encode([
    'book_id'        => $book['book_id'],
    'book_name'      => $book['book_name'],
    'author'         => $book['author'] ?? '–',
    'type_name'      => $book['type_name'] ?? '–',
    'status'         => $book['status'] ?? 'available',
    'book_image'     => $imageUrl,
    'borrow_count'   => $borrow_count,
    'fav_count'      => $fav_count,
    'current_borrow' => $current_borrow,
    'due_date'       => $due_date,
    'history'        => $history,
], JSON_UNESCAPED_UNICODE);
