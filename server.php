<?php
session_start();

// initializing variables
$username = "";
$email    = "";
$errors = array();

// connect to the database
$db = mysqli_connect('localhost', 'root', '', 'dealpass_db');

// REGISTER USER
if (isset($_POST['reg_user'])) {
    // recieve all input values from the form
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
    $user_type = mysqli_real_escape_string($db, $_POST['user_type']);

    // form validation: ensure that the form is correctly filled ...
    if (empty($username)) { array_push($errors, "Username is required"); }
    if (empty($email)) { array_push($errors, "Email is required"); }
    if (empty($password_1)) { array_push($errors, "Password is required"); }
    if ($password_1 != $password_2) {
        array_push($errors, "Two passwords don't match");
    }
    if (empty($user_type)) { array_push($errors, "User type is required"); }


    // first check the database to make sure
    // a user does not already exist with the same username and/or email
    $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) { // if user exists
        if ($user['username'] === $username) {
            array_push($errors, "Username already exists");
        }

        if ($user['email'] === $email) {
            array_push($errors, "email already exists");
        }
    }

    // Finally, register user if there are no errors in the form
    // register
	if (count($errors) == 0) {
		$password = md5($password_1);
		$query = "INSERT INTO users (username, email, password, user_type) VALUES('$username', '$email', '$password', '$user_type')";
		mysqli_query($db, $query);
        $_SESSION['id'] = mysqli_insert_id($db);
        $_SESSION['username'] = $username;
		$_SESSION['email'] = $email;
		$_SESSION['user_type'] = $user_type;
		$_SESSION['success'] = "You are now logged in";
		// route based on role
		if ($user_type === 'shopowner') {
			header('location: login-shopowner.php');
		}
        if ($user_type === 'customer') {
            header('location: login-customer.php');
        }
		exit();
	}
}

// LOGIN USER (role-specific)
if (isset($_POST['login_shopowner']) || isset($_POST['login_customer'])) {
	$email = mysqli_real_escape_string($db, $_POST['email']);
	$password = mysqli_real_escape_string($db, $_POST['password']);
	$posted_type = isset($_POST['user_type']) ? mysqli_real_escape_string($db, $_POST['user_type']) : '';

    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        $password = md5($password);
        $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
        $results = mysqli_query($db, $query);
        if (mysqli_num_rows($results) == 1) {
            $user = mysqli_fetch_assoc($results);
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = $posted_type;
            $_SESSION['success'] = "You are now logged in";
            if ($posted_type === 'shopowner') {
                header('location: dashboard-shopowner.php');
            }
            if ($posted_type === 'customer') {
                header('location: dashboard-customer.php');
            }
        } else {
            array_push($errors, "Wrong email or password combination");
        }
    }
}