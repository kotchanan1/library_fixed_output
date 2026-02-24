<?php
session_start();
include('db.php');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: login.php"); exit;
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = trim($_POST['redirect'] ?? '');

if(!$email || !$password){
    header("Location: login.php?error=empty&redirect=".urlencode($redirect)); exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? AND password=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ss", $email, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) === 1){
    $row = mysqli_fetch_assoc($result);
    $_SESSION['user_id']   = $row['user_id'];
    $_SESSION['fullname']  = $row['first_name'].' '.$row['last_name'];
    $_SESSION['email']     = $row['email'];
    // กำหนด role: ถ้า email มีคำว่า admin หรือ first_name = 'admin' → admin
    $_SESSION['role']      = (strtolower($row['first_name']) === 'admin') ? 'admin' : 'member';
    $_SESSION['member_id'] = $row['user_id']; // alias เพื่อความเข้ากัน

    if($redirect && $_SESSION['role'] !== 'admin'){
        header("Location: " . $redirect); exit;
    }
    if($_SESSION['role'] === 'admin'){
        header("Location: admin_home.php");
    } else {
        header("Location: index.php");
    }
} else {
    header("Location: login.php?error=wrong&redirect=".urlencode($redirect));
}
exit;
?>
