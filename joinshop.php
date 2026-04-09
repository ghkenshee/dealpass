<?php
include 'server.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit();
}

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    echo "You must be logged in to join a shop.";
    exit();
}

$user_id = (int) $_SESSION['id'];
$shop_name = trim($_POST['shop_name'] ?? '');

if ($shop_name === '') {
    echo "Shop name is required.";
    exit();
}

// Find shop by name (select id as shop_id for compatibility)
$sql = "SELECT shop_id FROM shops WHERE shop_name = ? LIMIT 1";
if ($stmt = mysqli_prepare($db, $sql)) {
    mysqli_stmt_bind_param($stmt, 's', $shop_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $shop_id);
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);

        // Check if already a member (use COUNT to avoid depending on specific column names)
        $sql2 = "SELECT COUNT(*) FROM members WHERE user_id = ? AND shop_id = ?";
        if ($stmt2 = mysqli_prepare($db, $sql2)) {
            mysqli_stmt_bind_param($stmt2, 'ii', $user_id, $shop_id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_bind_result($stmt2, $count);
            mysqli_stmt_fetch($stmt2);
            mysqli_stmt_close($stmt2);

            if ($count > 0) {
                echo "You are already a member of this shop.";
                exit();
            }
        } else {
            error_log('Prepare failed (check membership): ' . mysqli_error($db));
            echo "Database error.";
            exit();
        }

        // Add membership
        $sql3 = "INSERT INTO members (user_id, shop_id, role) VALUES (?, ?, 'member')";
        if ($stmt3 = mysqli_prepare($db, $sql3)) {
            mysqli_stmt_bind_param($stmt3, 'ii', $user_id, $shop_id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);
            header("Location: shop-shopowner.php?msg=joined");
exit();
        } else {
            error_log('Prepare failed (insert membership): ' . mysqli_error($db));
            echo "Database error.";
            exit();
        }
    } else {
        mysqli_stmt_close($stmt);
        echo "Shop not found. Please check the name.";
        exit();
    }
} else {
    error_log('Prepare failed (find shop): ' . mysqli_error($db));
    echo "Database error.";
    exit();
}
?>

