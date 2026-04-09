<?php
include 'server.php'; // 1. Connect to Database

// 2. Check Login
if (!isset($_SESSION['id'])) {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['id'];

// 3. Get Deal ID
$deal_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['deal_id']) ? (int)$_POST['deal_id'] : 0);

if ($deal_id === 0) {
    header('Location: dashboard-shopowner.php?error=invalid_id');
    exit();
}

// 4. Verify Ownership & Fetch Current Data
// We join deallist with members to ensure the user is allowed to edit this deal.
$sql = "SELECT d.deal_id, d.deal_name, d.discount, d.shop_id 
        FROM deallist d
        INNER JOIN members m ON d.shop_id = m.shop_id
        WHERE d.deal_id = ? AND m.user_id = ?";

$deal = null;
if ($stmt = $db->prepare($sql)) {
    $stmt->bind_param("ii", $deal_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $deal = $result->fetch_assoc();
    $stmt->close();
}

// If no deal found, user is not authorized or deal doesn't exist
if (!$deal) {
    header('Location: dashboard-shopowner.php?error=unauthorized');
    exit();
}

// 5. Handle Form Submission (Update Deal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_deal'])) {
    
    $new_deal_name = trim($_POST['deal_name']);
    $new_discount = (int)$_POST['discount'];

    if (!empty($new_deal_name) && $new_discount > 0) {
        
        $update_sql = "UPDATE deallist SET deal_name = ?, discount = ? WHERE deal_id = ?";
        
        if ($update_stmt = $db->prepare($update_sql)) {
            $update_stmt->bind_param("sii", $new_deal_name, $new_discount, $deal_id);
            
            if ($update_stmt->execute()) {
                // Success: Redirect back to shop list
                header('Location: dashboard-shopowner.php?msg=deal_updated');
                exit();
            } else {
                $error = "Update failed: " . $db->error;
            }
            $update_stmt->close();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Deal - DealPass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="src/dealpasslogotrans.png">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-gray-300 shadow-sm p-8 w-full max-w-lg">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Edit Deal</h2>
            <?php if(isset($error)): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form action="editdeal.php" method="POST" class="flex flex-col space-y-5">
                <input type="hidden" name="deal_id" value="<?php echo $deal['deal_id']; ?>">
                <div class="flex flex-col space-y-2">
                    <label for="deal_name" class="font-semibold text-gray-700">Deal Name</label>
                    <input type="text" id="deal_name" name="deal_name" required 
                           value="<?php echo htmlspecialchars($deal['deal_name']); ?>"
                           class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 transition duration-200 outline-none">
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="discount" class="font-semibold text-gray-700">Discount (%)</label>
                    <input type="number" id="discount" name="discount" required min="1" max="100" 
                           value="<?php echo htmlspecialchars($deal['discount']); ?>"
                           class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 transition duration-200 outline-none">
                </div>
                <div class="flex space-x-4 pt-4">
                    <button type="submit" name="update_deal"
                            class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition duration-300">
                        Save Changes
                    </button>
                    <a href="dashboard-shopowner.php" 
                       class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-300 transition duration-300 text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>