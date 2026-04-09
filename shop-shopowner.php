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

    if (isset($_GET['msg']) || isset($_GET['error'])):
?>

<div id="notificationToast" class="fixed bottom-5 right-5 z-50 transform transition-all duration-500 ease-in-out translate-y-20 opacity-0">
        <div class="flex items-center p-4 rounded-lg shadow-lg border-l-4 <?php echo isset($_GET['error']) ? 'bg-white border-red-500 text-red-700' : 'bg-white border-green-500 text-green-700'; ?>">
            <div class="mr-3">
                <?php if (isset($_GET['error'])): ?>
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                <?php else: ?>
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="font-medium">
                <?php
                    // Dynamic Message Logic
                    if (isset($_GET['error'])) {
                        // Handle Errors
                        switch ($_GET['error']) {
                            case 'db_error': echo "A database error occurred."; break;
                            case 'not_member': echo "You are not a member of this shop."; break;
                            default: echo "An error occurred.";
                        }
                    } else {
                        // Handle Success Messages
                        switch ($_GET['msg']) {
                            case 'joined': echo "You successfully joined the shop!"; break;
                            case 'created': echo "Shop created successfully!"; break;
                            case 'left_shop': echo "You have left the shop."; break;
                            case 'deal_posted': echo "Deal published successfully!"; break;
                            default: echo "Success!";
                        }
                    }
                ?>
            </div>
            <button onclick="closeToast()" class="ml-4 text-gray-400 hover:text-gray-900 focus:outline-none">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <script>
        // Show the toast nicely on page load
        window.addEventListener('DOMContentLoaded', (event) => {
            const toast = document.getElementById('notificationToast');
            if (toast) {
                // Wait a tiny bit for render, then slide up
                setTimeout(() => {
                    toast.classList.remove('translate-y-20', 'opacity-0');
                }, 100);
                // Auto hide after 5 seconds
                setTimeout(() => {
                    closeToast();
                }, 5000);
            }
        });
        function closeToast() {
            const toast = document.getElementById('notificationToast');
            if (toast) {
                // Slide down and fade out
                toast.classList.add('translate-y-20', 'opacity-0');
                // Remove from DOM after animation finishes
                setTimeout(() => { toast.style.display = 'none'; }, 500);
            }
        }
    </script>
    <script>
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('msg');
            url.searchParams.delete('error');
            window.history.replaceState({path: url.href}, '', url.href);
        }
    </script>
<?php endif; ?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Shop | Shopowner </title>
        <link rel='stylesheet' href='css/style_two.css'>
        <link rel="icon" type="image/x-icon" href="src/dealpasslogotrans.png">
        <script src="https://cdn.tailwindcss.com"></script>
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
                    <a href="shop-shopowner.php" class="flex items-center w-full p-2 rounded-xl font-bold text-lg text-red-600 transition duration-150 justify-center md:justify-start">
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
            <div class="flex-grow bg-white flex items-center justify-center p-4 md:p-8 md:ml-64">
                <div class="flex-grow bg-white min-h-screen flex items-start md:items-center justify-center p-4 pt-10 pb-10 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 w-full max-w-5xl">
                        <div class="bg-white rounded-2xl border border-gray-300 shadow-sm p-6 md:p-8 h-full">
                            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Create Shop</h2>
                            <form action="createshop.php" method="POST" class="flex flex-col space-y-4">
                                <div class="flex flex-col space-y-2">
                                    <label for="create_shop_name" class="font-semibold text-gray-700">Shop Name</label>
                                    <input type="text" id="create_shop_name" name="shop_name" required 
                                        class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none">
                                </div>
                                <div class="flex flex-col space-y-2">
                                    <label for="description" class="font-semibold text-gray-700">Description</label>
                                    <textarea id="description" name="description" rows="3"
                                            class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none resize-none"></textarea>
                                </div>
                                <button type="submit" 
                                        class="self-center px-12 py-3 bg-gray-300 text-gray-800 font-bold rounded-xl hover:bg-red-600 hover:text-white transition duration-300">
                                    Create Shop
                                </button>
                            </form>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-300 shadow-sm p-6 md:p-8 h-full flex flex-col">
                            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Join Shop</h2>
                            <form action="joinshop.php" method="POST" class="flex flex-col flex-grow justify-center space-y-6">
                                <div class="flex flex-col space-y-2">
                                    <label for="join_shop_name" class="font-semibold text-gray-700">Join Code</label>
                                    <input type="text" id="join_shop_name" name="shop_name" required 
                                        class="w-full p-3 bg-gray-200 rounded-lg border border-transparent focus:border-red-500 focus:bg-white focus:ring-0 transition duration-200 outline-none">
                                </div>
                                <button type="submit" 
                                        class="self-center px-12 py-3 bg-gray-300 text-gray-800 font-bold rounded-xl hover:bg-red-600 hover:text-white transition duration-300">
                                    Join Shop
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

