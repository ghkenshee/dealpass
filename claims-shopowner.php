<?php
include 'server.php';

// 1. Check if user is logged in as a shopowner
// Adjust 'username' or 'user_type' based on your specific session setup
if (!isset($_SESSION['username']) || ($_SESSION['user_type'] ?? '') !== 'shopowner') {
    header('location: login-shopowner.php');
    exit();
}

/* --- PART B: SHOP OWNER LOGIC (New) --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] === 'confirm' || $_POST['action'] === 'reject')) {

    // 1. Security Check: Ensure user is a shop owner
    // Adjust this check based on how you store user_type in session
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'shopowner') {
        header("Location: login.php"); // or wherever you send unauthorized users
        exit();
    }

    $claim_id = (int)$_POST['claim_id'];
    $action = $_POST['action'];

    if ($action === 'confirm') {
        // APPROVE: Change status to 'claimed' and set redeemed_at
        // We assume your claims table has a 'redeemed_at' column. If not, remove that part.
        $sql = "UPDATE claims SET status = 'claimed', claimed_at = NOW() WHERE id = ?";
        $msg = "Deal Accepted!";
    } elseif ($action === 'reject') {
        // REJECT: Change status back to 'unclaim' 
        // This allows the customer to see the 'Redeem' button again if it was a mistake, 
        // or effectively "cancels" the transaction.
        $sql = "UPDATE claims SET status = 'unclaim' WHERE id = ?";
        $msg = "Deal Rejected.";
    }

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("i", $claim_id);
        if ($stmt->execute()) {
            // Success
            header("Location: dashboard-shopowner.php?msg=" . urlencode($msg));
        } else {
            // SQL Error
            header("Location: dashboard-shopowner.php?error=update_failed");
        }
        $stmt->close();
    } else {
        header("Location: dashboard-shopowner.php?error=db_error");
    }
    exit();
}
// --- END LOGIC ---

// Fallback redirect
header("Location: login.html");
exit();
?>