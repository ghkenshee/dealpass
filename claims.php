<?php
include 'server.php'; // Ensure this connects to your DB as $db

// 1. Check if user is logged in as a customer
// Adjust 'username' or 'user_type' based on your specific session setup
if (!isset($_SESSION['username']) || ($_SESSION['user_type'] ?? '') !== 'customer') {
    header('location: login-customer.php');
    exit();
}

// 2. Check if the form was actually submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'claim') {
    
    // Get Data from the POST request
    $deal_id = (int)$_POST['deal_id'];
    $customer_id = $_SESSION['id']; // IMPORTANT: Ensure this session var matches your login logic

    // 3. Prepare SQL to insert the claim
    // We set status to 'pending' immediately
    // ON DUPLICATE KEY UPDATE handles cases where they might have 'unclaimed' it before
    $sql = "INSERT INTO claims (deal_id, customer_id, status, claimed_at) 
            VALUES (?, ?, 'pending', NOW()) 
            ON DUPLICATE KEY UPDATE status = 'pending', claimed_at = NOW()";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("ii", $deal_id, $customer_id);
        
        if ($stmt->execute()) {
            // Success: Redirect back to the deal card
            // We add a success message in the URL
            header("Location: dealcard.php?deal_id=" . $deal_id . "&msg=success");
            exit();
        } else {
            // Database Error
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Database Preparation Error";
    }

} else {
    // If someone tries to access claims.php directly without clicking the button
    header("Location: dashboard-customer.php");
    exit();
}
?>