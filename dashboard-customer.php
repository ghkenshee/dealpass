<?php
    include('server.php');

    if (!isset($_SESSION['email'])) {
        $_SESSION['msg'] = "You must log in first";
        header('location: login-customer.php');
    }
    if (($_SESSION['user_type'] ?? '') !== 'customer') {
        header('location: login-customer.php');
        exit();
    }
    if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['email']);
        header("location: login-customer.php");
    }

    $user_id = $_SESSION['id'];
    $username = $_SESSION['username'];

    // --- UPDATED QUERY LOGIC ---
    
    $query = "SELECT 
            d.deal_id, 
            d.deal_name, 
            d.discount, 
            s.shop_name,
            c.id AS user_claim_id,
            c.status AS claim_status  -- FETCH THE STATUS HERE
        FROM deallist d
        INNER JOIN shops s ON d.shop_id = s.shop_id
        LEFT JOIN claims c 
            ON d.deal_id = c.deal_id 
            AND c.customer_id = '$user_id'
        ORDER BY d.deal_id DESC";

$result = mysqli_query($db, $query);
    
    // Check for errors in case table names are wrong
    if (!$result) {
        die("Query Failed: " . mysqli_error($db));
    }
?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Dashboard | Customer </title>
        <link rel='stylesheet' href='css/style_two.css'>
        <link rel="icon" type="image/x-icon" href="src/dealpasslogotrans.png">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            /* Custom Ticket Styles */
        .ticket-card {
            position: relative;
            background: #949494ff; /* Gray-200 background like in your image */
            border: 1px solid #d1d5db; /* Gray-300 border */
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .ticket-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        /* The circular cutouts */
        .ticket-notch {
            position: absolute;
            top: 50%;
            width: 30px;
            height: 30px;
            background-color: white; /* Matches page background */
            border-radius: 50%;
            transform: translateY(-50%);
            border: 1px solid #d1d5db;
        }
        .notch-left {
            left: -12px; /* Positioned halfway off the left edge */
            border-right-color: transparent; /* Hide inner border if needed */
        }
        .notch-right {
            right: -12px; /* Positioned halfway off the right edge */
            border-left-color: transparent;
        }
        </style>
    </head>
    <body>
        <div class="flex min-h-screen">
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
                    <a href="account-customer.php" class="mt-auto flex items-center w-full p-2 rounded-xl text-gray-500 hover:text-red-600 font-medium text-lg transition duration-150 justify-center md:justify-start">
                        <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden md:inline">Account</span>
                    </a>
                </nav>
            </div>
            <div class="flex-grow bg-white flex items-start justify-center md:ml-64 overflow-auto flex-col">
                <div class="max-w-7xl mx-auto px-8 py-10 md:px-12">
                    <header class="mb-10">
                        <h1 class="text-3xl font-bold text-gray-900">
                            Welcome <?php echo htmlspecialchars($username); ?>!
                        </h1>
                        <p class="text-white mt-1">‌‌oooooooooooooooooo<br>‌oooooooooooooooooo</p>
                        <hr>
                    </header>
                    <section>
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            Deals For You
                        </h2>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                <?php while ($deal = mysqli_fetch_assoc($result)): ?>
                                    <?php 
                                        // --- LOGIC: Determine Status ---
                                        // Get status safely (default to empty string)
                                        $status = isset($deal['claim_status']) ? strtolower($deal['claim_status']) : '';

                                        // Define States
                                        $is_approved = ($status == 'claimed'); 
                                        $is_pending  = ($status == 'pending'); 

                                        // --- STYLING: Set Classes ---
                                        if ($is_approved) {
                                            // GRAY (Claimed): Unclickable
                                            $card_bg = "bg-green-200 opacity-80 cursor-not-allowed pointer-events-none";
                                            $text_style = "text-green-500 line-through";
                                            $link_url = "dealcard.php?deal_id=" . $deal['deal_id'];
                                        } elseif ($is_pending) {
                                            // YELLOW (Pending): Wait cursor
                                            $card_bg = "bg-yellow-50 border-gray-200 cursor-wait pointer-events-none"; 
                                            $text_style = "text-yellow-800 opacity-75"; 
                                            $link_url = "dealcard.php?deal_id=" . $deal['deal_id'];
                                        } else {
                                            // WHITE (Active/Rejected): Clickable
                                            $card_bg = "bg-white cursor-pointer group hover:shadow-md hover:border-red-200";
                                            $text_style = "text-gray-900 group-hover:text-red-600";
                                            $link_url = "dealcard.php?deal_id=" . $deal['deal_id'];
                                        }
                                    ?>
                                    <a href="<?php echo $link_url; ?>" class="block h-full">
                                        <div class="ticket-card relative overflow-hidden p-6 h-40 flex flex-col justify-between transition border rounded-xl <?php echo $card_bg; ?>">
                                            <div class="ticket-notch notch-left bg-gray-50 absolute"></div>
                                            <div class="ticket-notch notch-right bg-gray-50 absolute"></div>
                                            <div>
                                                <h3 class="font-bold text-lg leading-tight transition <?php echo $text_style; ?>">
                                                    <?php echo htmlspecialchars($deal['deal_name']); ?>
                                                </h3>
                                                <p class="text-xs mt-1 text-gray-500">
                                                    <?php echo htmlspecialchars($deal['shop_name']); ?>
                                                </p>
                                            </div>
                                            <div class="mt-4">
                                                <?php if ($is_approved): ?>
                                                    <span class="inline-flex items-center bg-green-600 text-white text-sm font-bold px-3 py-1 rounded shadow-sm">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        CLAIMED
                                                    </span>
                                                <?php elseif ($is_pending): ?>
                                                    <span class="inline-flex items-center bg-yellow-500 text-white text-sm font-bold px-3 py-1 rounded shadow-sm">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        PENDING
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-block bg-red-600 text-white text-sm font-bold px-3 py-1 rounded shadow-sm">
                                                        <?php echo $deal['discount']; ?>% OFF
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div> 
                            <?php else: ?>
                            <div class="bg-gray-50 rounded-xl p-10 text-center border border-gray-200">
                                <p class="text-gray-500 text-lg">No deals available right now.</p>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    </body>
</html>