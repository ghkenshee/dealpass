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

    $user_id = $_SESSION['id'];
    $shop_id_from_url = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : 0;

    // 2. Fetch All Authorized Shops for this User
    $shops_sql = "SELECT s.shop_id, s.shop_name 
                FROM shops s
                INNER JOIN members m ON s.shop_id = m.shop_id
                WHERE m.user_id = ?
                ORDER BY s.shop_name ASC";

    $my_shops = [];
    if ($stmt = mysqli_prepare($db, $shops_sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $my_shops[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log('Prepare failed (shops_sql): ' . mysqli_error($db));
    }

    if (empty($my_shops)) {
        echo "<div style='padding:20px; text-align:center;'>You must join or create a shop first. <a href='shop-shopowner.php'>Go back</a></div>";
        exit();
    }

    // 3. Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_deal'])) {
        
        $selected_shop_id = (int)$_POST['shop_id'];
        $deal_name = trim($_POST['deal_name']);
        $discount = (int)$_POST['discount'];

        // Security Check
        $is_authorized = false;
        foreach ($my_shops as $shop) {
            if ($shop['shop_id'] === $selected_shop_id) {
                $is_authorized = true;
                break;
            }
        }

        if (!$is_authorized) {
            $_SESSION['error_msg'] = "Error: You are not authorized to post for this shop.";
            // Ideally redirect back to the form so the popup shows
            header("Location: deals-shopowner.php"); 
            exit();
        } elseif (!empty($deal_name) && $discount > 0) {
            
            $sql = "INSERT INTO deallist (shop_id, deal_name, discount) VALUES (?, ?, ?)";
            
            if ($insert_stmt = mysqli_prepare($db, $sql)) {
                mysqli_stmt_bind_param($insert_stmt, 'isi', $selected_shop_id, $deal_name, $discount);
                
                // --- SUCCESS BLOCK ---
                if (mysqli_stmt_execute($insert_stmt)) {
                    mysqli_stmt_close($insert_stmt);
                    
                    // 1. Set the Session Message
                    $_SESSION['success_msg'] = "Deal created successfully!";
                    
                    // 2. Redirect (Frontend script will pick up the message)
                    header("Location: deals-shopowner.php");
                    exit();
                } else {
                    $_SESSION['error_msg'] = "Database Error: " . mysqli_error($db);
                    header("Location: deals-shopowner.php");
                    exit();
                }
            }
        } else {
            $_SESSION['error_msg'] = "Please fill in all required fields.";
            header("Location: deals-shopowner.php");
            exit();
        }
    }
?>

<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Deals | Shopowner </title>
    <!-- <link href="/src/style.css" rel="stylesheet"> -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="src/dealpasslogotrans.png">
</head>
<body>
    <div class="flex min-h-screen">
        <div class="h-screen w-20 md:w-64 bg-white p-4 flex flex-col md:fixed md:top-0 md:left-0 border-r border-gray-100 z-10">
            <div class="mb-8 flex justify-center md:justify-start">
                    <img src="src/dealpasstextlogo.png" alt="DealPass Logo" class="h-10 w-10 md:h-12 md:w-12 rounded-lg object-contain">
                </div>
                <nav class="flex-1 flex flex-col w-full space-y-2">
                    <a href="dashboard-shopowner.php" class="flex items-center w-full p-2 rounded-xl text-gray-500 hover:text-red-600 font-medium text-lg transition duration-150 justify-center md:justify-start">
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
                    <a href="deals-shopowner.php" class="flex items-center w-full p-2 rounded-xl font-bold text-lg text-red-600 transition duration-150 justify-center md:justify-start">
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
        <div class="flex-grow bg-gray-50 min-h-screen flex items-center justify-center p-4 md:p-8 md:ml-64">
            <div class="bg-white rounded-2xl border border-gray-300 shadow-sm p-8 w-full max-w-lg">
                <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Create a Deal</h2>
                <?php if(isset($error)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form action="deals-shopowner.php" method="POST" class="flex flex-col space-y-5">
                    <div class="flex flex-col space-y-2">
                        <label for="shop_id" class="font-semibold text-gray-700">Select Shop</label>
                        <div class="relative">
                            <select name="shop_id" id="shop_id" 
                                    class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none appearance-none cursor-pointer">
                                <?php foreach ($my_shops as $shop): ?>
                                    <option value="<?php echo $shop['shop_id']; ?>" 
                                        <?php echo ($shop['shop_id'] == $shop_id_from_url) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($shop['shop_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="deal_name" class="font-semibold text-gray-700">Deal Name</label>
                        <input type="text" id="deal_name" name="deal_name" required placeholder="e.g. Summer Sale"
                            class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="deal_date" class="font-semibold text-gray-700">Deal Date <span class="text-xs font-normal text-gray-400">(Demo Only)</span></label>
                        <input type="date" id="deal_date" name="deal_date"
                            class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="discount" class="font-semibold text-gray-700">Discount (%)</label>
                        <input type="number" id="discount" name="discount" required min="1" max="100" placeholder="e.g. 20"
                            class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none">
                    </div>
                    <div class="flex flex-col space-y-2">
                        <label for="description" class="font-semibold text-gray-700">Description <span class="text-xs font-normal text-gray-400">(Demo Only)</span></label>
                        <textarea id="description" name="description" rows="3" placeholder="Describe the deal..."
                                class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none resize-none"></textarea>
                    </div>
                    <button type="submit" name="create_deal"
                            class="mt-4 w-full bg-gray-300 text-gray-800 font-bold py-3 rounded-xl hover:bg-red-600 hover:text-white transition duration-300">
                        Post Deal
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php if (isset($_SESSION['success_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $_SESSION['success_msg']; ?>',
                confirmButtonColor: '#DC2626', // Matches your red theme
                timer: 3000, // Auto-close after 3 seconds
                timerProgressBar: true
            });
        });
    </script>
    <?php 
        // Clear the message so it doesn't appear again on refresh
        unset($_SESSION['success_msg']); 
    ?>
<?php endif; ?>
<?php if (isset($_SESSION['error_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $_SESSION['error_msg']; ?>',
                confirmButtonColor: '#DC2626'
            });
        });
    </script>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>
</body>
</html>