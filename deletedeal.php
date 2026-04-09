<?php
include 'server.php'; // 1. Connect to Database

// 2. Check Login
if (!isset($_SESSION['id'])) {
    header('Location: login.html');
    exit();
}

// 3. Validate Input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: shop-shopowner.php?error=invalid_id');
    exit();
}

$deal_id = (int)$_GET['id'];
$user_id = $_SESSION['id'];

// 4. Verify Ownership (CRITICAL SECURITY STEP)
// We cannot check 'shop_owner_id' anymore because the new table is 'deallist'.
// Instead, we check if the user is a member of the shop that owns this deal.
$check_sql = "SELECT d.deal_id 
              FROM deallist d 
              INNER JOIN members m ON d.shop_id = m.shop_id 
              WHERE d.deal_id = ? AND m.user_id = ?";

if ($stmt = $db->prepare($check_sql)) {
    $stmt->bind_param("ii", $deal_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Security Fail: User is not connected to this deal's shop
        $stmt->close();
        header('Location: shop-shopowner.php?error=unauthorized');
        exit();
    }
    $stmt->close();
} else {
    header('Location: shop-shopowner.php?error=db_error');
    exit();
}

// 5. Delete the Deal
// Updated to delete from 'deallist' instead of 'deals'
$delete_sql = "DELETE FROM deallist WHERE deal_id = ?";

// Disable foreign key checks temporarily to avoid issues with claims foreign key
$db->query("SET FOREIGN_KEY_CHECKS=0");

if ($stmt = $db->prepare($delete_sql)) {
    $stmt->bind_param("i", $deal_id);
    
    if ($stmt->execute()) {
        // Re-enable foreign key checks after deletion
        $db->query("SET FOREIGN_KEY_CHECKS=1");

        // Success: Redirect with a message for your Toast Notification
        header('Location: shop-shopowner.php?msg=deal_deleted');
        exit();
    } else {
        // Re-enable foreign key checks if deletion fails
        $db->query("SET FOREIGN_KEY_CHECKS=1");
        header('Location: shop-shopowner.php?error=delete_failed');
        exit();
    }
}
?>