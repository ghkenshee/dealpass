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

    $username = $_SESSION['username'];
?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Account | Customer </title>
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
                    <a href="deals-shopowner.php" class="flex items-center w-full p-2 rounded-xl text-gray-500 hover:text-red-600 font-medium text-lg transition duration-150 justify-center md:justify-start">
                        <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="hidden md:inline">Deals</span>
                    </a>
                    <a href="account-shopowner.php" class="flex items-center w-full p-2 rounded-xl font-bold text-lg text-red-600 transition duration-150 justify-center md:justify-start">
                        <svg class="h-7 w-7 md:mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden md:inline">Account</span>
                    </a>
                </nav>
            </div>
            <div class="flex-grow bg-white flex items-start md:ml-64 overflow-auto flex-col h-screen">
                <div class="w-full max-w-3xl p-10 md:p-16 space-y-12">
                    <h1 class="text-4xl font-extrabold text-gray-900">
                        Account Details
                    </h1>
                        <div class="space-y-10"> 
                            <div>
                                <p class="text-base text-gray-500 font-medium mb-1">Name</p>
                                <p class="text-3xl font-bold text-gray-900 tracking-tight">
                                    <?php echo htmlspecialchars($username); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-base text-gray-500 font-medium mb-1">Email</p>
                                <p class="text-3xl font-bold text-gray-900 tracking-tight">
                                    <?php echo htmlspecialchars($_SESSION['email'] ?? 'No email found'); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-base text-gray-500 font-medium mb-1">User Type</p>
                                <p class="text-3xl font-bold text-gray-900 tracking-tight capitalize">
                                    <?php echo htmlspecialchars($_SESSION['user_type'] ?? 'Customer'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 pt-4">
                            <a href="login-shopowner.php?logout=true" class="bg-[#D30000] hover:bg-red-800 text-white text-lg font-bold py-3 px-10 rounded-full transition duration-300">
                                Log Out
                            </a>
                            <button class="bg-gray-200 hover:bg-gray-300 text-black p-3.5 rounded-full transition duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>