<?php include('server.php') ?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Log-In | Customer </title>
    <link rel='stylesheet' href='css/style_two.css'>
    <link rel="icon" type="image/x-icon" href="src/dealpasslogotrans.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="flex flex-col lg:flex-row h-screen">
        <div class="relative lg:w-2/3 w-full bg-gradient-to-br from-purple-600 to-red-500 overflow-hidden">
            <img 
                src="src/background.png" 
                alt="DealPass Visual" 
                class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-60"
            />
        </div>
        <div class="lg:w-1/3 w-full bg-white flex flex-col justify-center px-4 py-6">
            <div class="flex justify-between items-center w-full max-w-md mx-auto mb-6 px-4">
                <div class="grid grid-cols-3 items-center w-full">
                    <!-- Back Button (Left-aligned with Shopowner box) -->
                    <div class="flex justify-start">
                        <a href="login.html" class="inline-block bg-red-100 text-red-600 font-semibold px-4 py-2 rounded hover:bg-red-200 hover:text-red-700 transition">← Back</a>
                    </div>
                    <!-- Centered Title -->
                    <div class="flex justify-center col-span-1">
                        <h1 class="text-2xl font-bold text-gray-800 text-center">Log In</h1>
                    </div>
                    <!-- Spacer (Right) -->
                    <div></div>
                </div>
            </div>
            <form method="post" action="register.php" class="w-full max-w-md mx-auto space-y-6 px-4">
                <?php include('error.php'); ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Enter your email" class="mt-1 w-full border rounded-md p-3 text-sm focus:ring-red-500 focus:border-red-500" required>
                </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="mt-1 w-full border rounded-md p-3 text-sm focus:ring-red-500 focus:border-red-500" placeholder="Enter your password" required>
                    </div>
                <input type="hidden" name="user_type" value="customer">
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm text-gray-600">
                        <label class="flex items-center space-x-2 mb-1">
                            <input type="checkbox" class="accent-red-500">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="text-red-500 hover:underline">Forget Password?</a>
                    </div>
                    <button class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600 transition" type="submit" name="login_customer" value="Log In">Log In</button>
                </div>
                <h3 class="text-sm text-gray-600 leading-tight">
                    Not yet a member? <a href="register.php" class="text-red-500 hover:underline" >Sign up</a>
                </h3>
            </form>
        </div>
    </div>
</body>
