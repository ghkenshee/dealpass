<?php
include 'server.php';

// Require POST and logged-in shopowner
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard-shopowner.php');
    exit();
}

if (!isset($_SESSION['id']) || (($_SESSION['user_type'] ?? '') !== 'shopowner')) {
    header('Location: login-shopowner.php');
    exit();
}

$shop_owner_id = (int) $_SESSION['id'];
$shop_name = trim($_POST['shop_name'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($shop_name === '') {
    echo "Shop name is required.";
    exit();
}

// Use the existing $db connection (from server.php). Use prepared statements (procedural API).
if ($description !== '') {
    $sql = "INSERT INTO shops (shop_name, description) VALUES (?, ?)";
    if ($stmt = mysqli_prepare($db, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ss', $shop_name, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $shop_id = mysqli_insert_id($db);
    } else {
        error_log('Prepare failed: ' . mysqli_error($db));
        echo "Database error creating shop.";
        exit();
    }
} else {
    $sql = "INSERT INTO shops (shop_name) VALUES (?)";
    if ($stmt = mysqli_prepare($db, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $shop_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $shop_id = mysqli_insert_id($db);
    } else {
        error_log('Prepare failed: ' . mysqli_error($db));
        echo "Database error creating shop.";
        exit();
    }
}

// Link creator as owner in memberships table
$sql2 = "INSERT INTO members (user_id, shop_id, role) VALUES (?, ?, 'owner')";
if ($stmt2 = mysqli_prepare($db, $sql2)) {
    mysqli_stmt_bind_param($stmt2, 'ii', $shop_owner_id, $shop_id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
} else {
    error_log('Prepare failed (memberships): ' . mysqli_error($db));
    echo "Database error linking owner.";
    exit();
}

// Success - redirect back to dashboard with a success message
$_SESSION['msg'] = 'Shop created successfully';
header("Location: shop-shopowner.php?msg=joined"); 
exit();
?>