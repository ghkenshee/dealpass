<?php
    include('server.php');

    if (!isset($_SESSION['email'])) {
        $_SESSION['msg'] = "You must log in first";
        header('location: login-shopowner.php');
    }
    if (($_SESSION['user_type'] ?? '') !== 'shopowner') {
		header('location: login-shopowner.php');
		exit();
	}
    if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['email']);
        header("location: login-shopowner.php");
    }

    // Handle explicit leave action via GET: ?action=leave&shop_id=NN
    if (isset($_GET['action']) && $_GET['action'] === 'leave' && isset($_GET['shop_id'])) {
        // ensure user still logged in
        if (!isset($_SESSION['id'])) {
            header('Location: login-shopowner.php');
            exit();
        }

        $user_id = $_SESSION['id'];
        $shop_id = (int)$_GET['shop_id'];

        $sql = "DELETE FROM members WHERE user_id = ? AND shop_id = ?";

        if ($stmt = mysqli_prepare($db, $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $user_id, $shop_id);
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($affected > 0) {
                header('Location: dashboard-shopowner.php?msg=left_shop');
                exit();
            } else {
                header('Location: dashboard-shopowner.php?error=not_member');
                exit();
            }
        } else {
            header('Location: dashboard-shopowner.php?error=db_error');
            exit();
        }
    }

    // 1. Get the current User ID
    $user_id = $_SESSION['id'];

    // 2. Modified SQL: Only select shops where the user is in the 'members' table
    // We join 'shops' with 'members' matching the shop_id, and filter by user_id.
    $sql_shops = "SELECT s.shop_id, s.shop_name 
                FROM shops s
                INNER JOIN members m ON s.shop_id = m.shop_id
                WHERE m.user_id = ?
                ORDER BY s.shop_id DESC";

    if ($stmt_shops = mysqli_prepare($db, $sql_shops)) {
        mysqli_stmt_bind_param($stmt_shops, 'i', $user_id);
        mysqli_stmt_execute($stmt_shops);
        $shops = mysqli_stmt_get_result($stmt_shops);
    } else {
        // Handle error or set empty result
        echo "Error loading shops.";
        exit();
    }

    $pending_claims = [];

// 1. FETCH PENDING CLAIMS
// Make sure to match your actual database column names here.
// I am assuming the table is 'deallist' based on previous prompts, 
// but if it is 'deals', change it accordingly.
$pending_sql = "SELECT c.id AS claim_id, d.deal_name, u.username AS customer_name, c.claimed_at
                FROM claims c
                JOIN deallist d ON c.deal_id = d.deal_id  
                JOIN users u ON c.customer_id = u.id
                WHERE c.status = 'pending' AND d.shop_id = ? 
                ORDER BY c.claimed_at DESC";

// Note: If your shop owner ID is linked via 'members' table like before, 
// you might need to adjust the WHERE clause or JOIN. 
// Assuming d.shop_id links to the owner or shop directly here.

if ($stmt = mysqli_prepare($db, $pending_sql)) {
    // If getting claims by shop_id (assuming shop_owner_id is the shop_id)
    mysqli_stmt_bind_param($stmt, 'i', $shop_owner_id);
    mysqli_stmt_execute($stmt);
    $pending_result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($pending_result)) {
        $pending_claims[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Dashboard | Shopowner </title>
        <link rel='stylesheet' href='css/style_two.css'>
        <link rel="icon" type="image/x-icon" href="src/dealpasslogotrans.png">
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body>
        <div class="flex min-h-screen">
            <div class="w-20 md:w-64 bg-white p-4 flex flex-col md:fixed md:top-0 md:left-0 md:h-screen border-r border-gray-100 border-gray-300 shadow-sm z-10">
                <div class="mb-8 flex justify-center md:justify-start">
                    <img src="src/dealpasstextlogo.png" alt="DealPass Logo" class="h-10 w-10 md:h-12 md:w-12 rounded-lg object-contain">
                </div>
                <nav class="flex-1 flex flex-col w-full space-y-2">
                    <a href="dashboard-shopowner.php" class="flex items-center w-full p-2 rounded-xl font-bold text-lg text-red-600 transition duration-150 justify-center md:justify-start">
                        <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-10v10a1 1 0 001 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="hidden md:inline">Home</span>
                    </a>
                    <a href="shop-shopowner.php" class="flex items-center w-full p-2 rounded-xl text-gray-500 hover:text-red-600 font-medium text-lg transition duration-150 justify-center md:justify-start">
                        <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l2-4h14l2 4M4 10h16v11H4V10z"/>
                        </svg>
                        <span class="hidden md:inline">Shops</span>
                    </a>
                    <a href="deals-shopowner.php" class="flex items-center w-full p-2 rounded-xl text-gray-500 hover:text-red-600 font-medium text-lg transition duration-150 justify-center md:justify-start">
                        <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="hidden md:inline">Deals</span>
                    </a>
                    <a href="account-shopowner.php" class="mt-auto flex items-center w-full p-2 rounded-xl text-gray-500 hover:text-red-600 font-medium text-lg transition duration-150 justify-center md:justify-start">
                        <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden md:inline">Account</span>
                    </a>
                </nav>
            </div>
            <div class="flex-grow bg-white flex items-start justify-center md:ml-64 overflow-auto">
                <div class="w-full max-w-6xl mx-auto space-y-8 p-6 py-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">My Shops & Deals</h1>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>

                <?php while($shop = $shops->fetch_assoc()): ?>
                
                <?php 
                    $pending_claims = [];
                    // Ensure table names match your DB (deallist, claims, users)
                    $p_sql = "SELECT c.id AS claim_id, d.deal_name, u.username AS customer_name, c.claimed_at
                            FROM claims c
                            JOIN deallist d ON c.deal_id = d.deal_id
                            JOIN users u ON c.customer_id = u.id
                            WHERE d.shop_id = ? AND c.status = 'pending'
                            ORDER BY c.claimed_at ASC";
                            
                    if ($p_stmt = mysqli_prepare($db, $p_sql)) {
                        mysqli_stmt_bind_param($p_stmt, 'i', $shop['shop_id']);
                        mysqli_stmt_execute($p_stmt);
                        $p_res = mysqli_stmt_get_result($p_stmt);
                        while($row = $p_res->fetch_assoc()) {
                            $pending_claims[] = $row;
                        }
                        mysqli_stmt_close($p_stmt);
                    }
                ?>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">
                            <?php echo htmlspecialchars($shop['shop_name']); ?>
                        </h2>
                        <div class="flex items-center space-x-2">
                            <a href="dashboard-shopowner.php?action=leave&shop_id=<?php echo $shop['shop_id']; ?>" 
                            class="text-sm bg-gray-200 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-300 transition flex items-center"
                            onclick="return confirm('Are you sure you want to leave this shop?');">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Leave Shop
                            </a>
                            <a href="deals-shopowner.php?shop_id=<?php echo $shop['shop_id']; ?>" class="text-sm bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition">
                                + Add New Deal
                            </a>
                        </div>
                    </div>
                    <?php if (!empty($pending_claims)): ?>
                    <div class="bg-yellow-50 border-b border-yellow-100 px-6 py-4">
                        <h3 class="text-xs font-bold text-yellow-800 uppercase tracking-widest mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            Pending Approvals
                        </h3>
                        <div class="bg-white rounded-lg border border-yellow-200 overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="bg-yellow-100 text-yellow-800 text-xs uppercase font-semibold">
                                    <tr>
                                        <th class="px-4 py-3">ID</th>
                                        <th class="px-4 py-3">Customer</th>
                                        <th class="px-4 py-3">Deal Name</th>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-yellow-100">
                                    <?php foreach($pending_claims as $claim): ?>
                                    <tr class="hover:bg-yellow-50/50">
                                        <td class="px-4 py-3 font-mono font-bold text-gray-700">#<?php echo $claim['claim_id']; ?></td>
                                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($claim['customer_name']); ?></td>
                                        <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($claim['deal_name']); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('M d, H:i', strtotime($claim['claimed_at'])); ?></td>
                                        <td class="px-4 py-3 text-right space-x-1">
                                            <form action="claims-shopowner.php" method="POST" class="inline-block">
                                                <input type="hidden" name="claim_id" value="<?php echo $claim['claim_id']; ?>">
                                                <button type="submit" name="action" value="confirm" 
                                                        class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded text-xs font-bold transition">
                                                    Accept
                                                </button>
                                            </form>
                                            <form action="claims-shopowner.php" method="POST" class="inline-block" onsubmit="return confirm('Reject this claim?');">
                                                <input type="hidden" name="claim_id" value="<?php echo $claim['claim_id']; ?>">
                                                <button type="submit" name="action" value="reject" 
                                                        class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded text-xs font-bold transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="p-0">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-white text-gray-500 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-medium text-sm">Deal Name</th>
                                    <th class="px-6 py-3 font-medium text-sm">Discount</th>
                                    <th class="px-6 py-3 font-medium text-sm text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php
                                    // Fetch deals for this specific shop
                                    $sql2 = "SELECT deal_id, deal_name, discount FROM deallist WHERE shop_id = ? ORDER BY deal_id DESC";
                                    $has_deals = false; 
                                    if ($stmt2 = mysqli_prepare($db, $sql2)) {
                                        mysqli_stmt_bind_param($stmt2, 'i', $shop['shop_id']);
                                        mysqli_stmt_execute($stmt2);
                                        $deals = mysqli_stmt_get_result($stmt2);
                                        while($deal = $deals->fetch_assoc()):
                                            $has_deals = true;
                                ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 text-gray-800 font-medium">
                                        <?php echo htmlspecialchars($deal['deal_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-sm font-bold">
                                            <?php echo htmlspecialchars($deal['discount']); ?>% OFF
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="editdeal.php?id=<?php echo $deal['deal_id']; ?>" 
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm transition duration-150">
                                            Edit
                                        </a>
                                        <span class="text-gray-300">|</span>
                                        <a href="deletedeal.php?id=<?php echo $deal['deal_id']; ?>" 
                                        class="text-red-600 hover:text-red-800 font-medium text-sm transition duration-150" 
                                        onclick="return confirm('Are you sure you want to delete this deal? This cannot be undone.');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                        endwhile;
                                        mysqli_stmt_close($stmt2);
                                    }
                                ?>
                                <?php if (!$has_deals): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                                        No published deals yet.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if ($shops->num_rows === 0): ?>
                    <div class="text-center py-10">
                        <p class="text-gray-500 text-lg">You do not manage any shops yet.</p>
                        <a href="shop-shopowner.php" class="mt-4 inline-block text-red-600 font-bold hover:underline">Create a Shop</a>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($stmt_shops)) mysqli_stmt_close($stmt_shops); ?>
                </div>
            </div>
        </div>
    </body>
</html>

