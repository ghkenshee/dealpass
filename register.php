<!--
- make an aesthetic frame (Gamlanga)
-->
<?php include('server.php') ?>
<!doctype html>
<html lang='en' dir='ltr'>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Register</title>
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
            <!-- Right: Registration Form -->
            <div class="lg:w-1/3 w-full bg-white flex flex-col justify-center px-4 py-6">
                <div class="flex justify-between items-center w-full max-w-sm mx-auto mb-4">
                    <div class="grid grid-cols-3 items-center w-full">
                        <!-- Back Button (Left-aligned with Shopowner box) -->
                        <div class="flex justify-start">
                            <button class="bg-red-100 text-red-600 font-semibold px-4 py-2 rounded hover:bg-red-200 hover:text-red-700 transition"><a href="index.html">← Back</a></button>
                        </div>
                        <!-- Centered Title -->
                        <div class="flex justify-center col-span-1">
                            <h1 class="text-2xl font-bold text-gray-800 text-center">Register</h1>
                        </div>
                        <!-- Spacer (Right) -->
                        <div></div>
                    </div>
                </div>
                <form method="post" action="register.php" class="w-full max-w-sm mx-auto space-y-4">
                    <?php include('error.php'); ?>
                    <!-- Full Name -->
                    <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" placeholder="Enter your username" name="username" class="mt-1 w-full border rounded-md p-2 text-sm" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    </div>
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Enter your email" class="mt-1 w-full border rounded-md p-2 text-sm" required>
                    </div>
                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password_1" name="password_1" class="mt-1 w-full border rounded-md p-2 text-sm" placeholder="Enter your password" required>
                    </div>
                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" id="password_2" name="password_2" class="mt-1 w-full border rounded-md p-2 text-sm" placeholder="Confirm your password" required>
                    </div>
                    <!-- User Type Selection -->
                    <div class="w-full">
                        <label for="user_type" class="block text-sm font-medium text-gray-700">Select user type</label>
                        <select name="user_type" id="user_type"
                            class="mt-1 w-full border rounded-md p-2 text-sm focus:ring-red-500 focus:border-red-500 bg-white text-gray-700">
                            <option value="">Select user type</option>
                            <option value="customer" <?php if (($user_type ?? '') === 'customer') echo 'selected'; ?>>Customer</option>
                            <option value="shopowner" <?php if (($user_type ?? '') === 'shopowner') echo 'selected'; ?>>Shopowner</option>
                        </select>
                    </div>
                    <!-- Terms Checkbox -->
                    <div class="flex items-start">
                        <input type="checkbox" id="terms" name="terms" class="mt-1 mr-2">
                        <label for="terms" class="text-sm text-gray-600 leading-tight">
                        I agree to all the statements in Terms of Service
                        </label>
                    </div>
                    <!-- Sign Up Button -->
                    <button class="w-full bg-red-500 text-white py-2 rounded text-sm hover:bg-red-600" value="Register" name="reg_user">
                        Sign Up
                    </button>
                    <h3 class="text-sm text-gray-600 leading-tight">
                        Already logged-in? <a href="login.html">Sign in</a>
                    </h3>
                </form>
            </div>
        </div>
</body>
</html>