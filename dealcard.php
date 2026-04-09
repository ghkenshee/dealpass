<?php
include('server.php');

// 1. Check Login
if (!isset($_SESSION['username']) || ($_SESSION['user_type'] ?? '') !== 'customer') {
    header('location: login-customer.php');
    exit();
}

// 2. Validate Deal ID
if (!isset($_GET['deal_id'])) {
    header('Location: customer-dashboard.php');
    exit();
}

$deal_id = (int)$_GET['deal_id'];

// 3. Fetch Deal Details
$query = "SELECT d.*, s.shop_name 
          FROM deallist d
          INNER JOIN shops s ON d.shop_id = s.shop_id
          WHERE d.deal_id = ?";

if ($stmt = $db->prepare($query)) {
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $deal = $result->fetch_assoc();
    $stmt->close();
}

// 4. Check if deal exists
if (!$deal) {
    echo "Deal not found.";
    exit();
}

// 5. Check CUSTOMER'S Claim Status
$customer_id = $_SESSION['id']; // Make sure this matches your login session variable
$user_status = 'unclaim'; // Default state
$claim_id = null;

$claim_query = "SELECT status, id FROM claims WHERE deal_id = ? AND customer_id = ?";
if ($c_stmt = $db->prepare($claim_query)) {
    $c_stmt->bind_param("ii", $deal_id, $customer_id);
    $c_stmt->execute();
    $c_result = $c_stmt->get_result();
    $claim_data = $c_result->fetch_assoc();
    
    if ($claim_data) {
        // If a row exists, use that status (pending or claimed)
        $user_status = $claim_data['status'];
    }
    $c_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="src/dealpasslogotrans.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($deal['deal_name']); ?> - DealPass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Skeuomorphic Coupon Edge Logic 
           This creates the "wavy" or "scalloped" top and bottom edges using radial gradients.
        */
        .coupon-card {
            background-color: white;
            position: relative;
            /* The scallop size */
            --r: 12px; 
            
            /* Create the scalloped cuts */
            background: 
                radial-gradient(circle at top, transparent var(--r), white calc(var(--r) + 0.5px)) top left / 40px 51%,
                radial-gradient(circle at bottom, transparent var(--r), white calc(var(--r) + 0.5px)) bottom left / 40px 51%;
            background-repeat: repeat-x;
            
            /* Drop shadow filter to make the shape pop */
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            
            /* Ensure content doesn't touch the wavy edges */
            padding-top: 20px;
            padding-bottom: 20px;
        }

        /* The dashed line separator */
        .coupon-separator {
            border-top: 3px dashed #e5e7eb; /* Gray-200 dashed line */
            margin: 20px 0;
            position: relative;
        }
        
        /* The decorative half-circles at the ends of the separator (optional detail) */
        .coupon-separator::before { left: -30px; }
        .coupon-separator::after { right: -30px; }
    </style>
    </head>
    <body class="bg-gray-50 text-gray-800 flex min-h-screen">
        <div class="w-20 md:w-64 bg-white p-4 flex flex-col md:fixed md:top-0 md:left-0 md:h-screen border-r border-gray-100 border-gray-300 shadow-sm z-10">
            <div class="mb-8 flex justify-center md:justify-start">
                <img src="src/dealpasstextlogo.png" alt="DealPass Logo" class="h-10 w-10 md:h-12 md:w-12 rounded-lg object-contain">
            </div>
            <nav class="flex-1 flex flex-col w-full space-y-2">
                <a href="dashboard-customer.php" class="flex items-center w-full p-2 rounded-xl font-bold text-lg text-red-600 transition duration-150 justify-center md:justify-start">
                    <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-10v10a1 1 0 001 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="hidden md:inline">Home</span>
                </a>
                <a href="#" class="mt-auto flex items-center w-full p-2 rounded-xl text-gray-500 hover:text-red-600 font-medium text-lg transition duration-150 justify-center md:justify-start">
                    <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="hidden md:inline">Account</span>
                </a>
            </nav>
        </div>
        <div class="flex-grow min-h-screen flex flex-col items-center justify-center md:ml-64 p-6">
            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] items-start gap-4 w-full">
                <div class="w-full md:w-auto md:col-start-1 md:justify-self-end md:text-right md:pt-2">
                    <a href="dashboard-customer.php" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-red-600 rounded-lg font-bold text-sm hover:bg-red-50 transition shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back
                    </a>
                </div>
                <div class="w-full max-w-sm md:col-start-2">
        <div class="coupon-card w-full px-6 border-l border-r border-gray-200 h-[600px] flex flex-col relative">
            <div class="w-full h-48 bg-gray-300 rounded-lg mb-6 flex items-center justify-center relative overflow-hidden shrink-0">
                <svg class="h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="flex-grow flex flex-col">
                <div class="flex items-start justify-between mb-2">
                    <div class="pr-4">
                        <h1 class="text-2xl font-bold text-gray-900 leading-tight line-clamp-3 overflow-hidden text-ellipsis">
                            <?php echo htmlspecialchars($deal['deal_name']); ?>
                        </h1>
                        <p class="text-gray-500 font-medium mt-1 line-clamp-1">
                            <?php echo htmlspecialchars($deal['shop_name']); ?>
                        </p>
                    </div>
                    <div class="flex-shrink-0 h-16 w-16 bg-red-600 rounded-full flex items-center justify-center ring-1 ring-gray-100">
                        <span class="text-white font-bold text-lg">
                            <?php echo $deal['discount']; ?>%
                        </span>
                    </div>
                </div>
                <div class="mb-4 flex-grow overflow-hidden">
                    <p class="text-sm text-gray-600 leading-relaxed text-justify">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                </div>
                <div class="text-center mt-2">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                        Valid Until 01/01/2026
                    </p>
                </div>
            </div>
            <div class="coupon-separator shrink-0"></div>
            <div class="pb-8 text-center shrink-0 h-24 flex flex-col justify-end relative z-10">
                <?php if($user_status === 'unclaim'): ?>
                    <form action="claims.php" method="POST" onsubmit="return confirm('Claim this deal?');">
                        <input type="hidden" name="deal_id" value="<?php echo $deal['deal_id']; ?>">
                        <input type="hidden" name="action" value="claim">
                        <button type="submit" 
                                class="inline-block w-auto px-10 py-3 bg-red-600 text-white font-bold text-xl rounded-xl hover:bg-red-700 transition shadow-lg transform hover:-translate-y-0.5">
                            REDEEM
                        </button>
                    </form>
                <?php elseif($user_status === 'pending'): ?>
                    <div class="w-full py-2">
                        <span class="text-yellow-600 font-black text-3xl tracking-widest uppercase drop-shadow-sm">
                            PENDING
                        </span>
                    </div>
                <?php elseif($user_status === 'claimed'): ?>
                    <button disabled 
                            class="inline-block w-auto px-10 py-3 bg-green-100 text-green-600 font-bold text-xl rounded-xl cursor-not-allowed border border-green-200 shadow-sm">
                        CLAIMED
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>