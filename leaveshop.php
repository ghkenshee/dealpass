<?php
// 1. Turn on Error Reporting (DEBUGGING MODE)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Start Buffer to prevent "Headers already sent" errors
ob_start();

include 'server.php'; // Connect to DB

// 3. Check Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    die("Error: You are not logged in. <a href='login.html'>Login here</a>");
}

// 4. Check ID
if (!isset($_GET['shop_id'])) {
    die("Error: No Shop ID provided in URL.");
}

// use procedural mysqli (server.php provides $db)
$user_id = $_SESSION['id'];
$shop_id = (int)$_GET['shop_id'];

// 5. Run Delete
$sql = "DELETE FROM members WHERE user_id = ? AND shop_id = ?";

if ($stmt = mysqli_prepare($db, $sql)) {
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $shop_id);
    mysqli_stmt_execute($stmt);

    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        ob_end_clean();
        header("Location: shop-shopowner.php?msg=left_shop");
        exit();
    } else {
        die("Not a member or shop does not exist.");
    }
} else {
    die("SQL Prepare Error: " . mysqli_error($db));
}
?>