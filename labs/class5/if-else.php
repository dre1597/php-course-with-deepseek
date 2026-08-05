<?php

$age = 20;

if ($age >= 18) {
    echo "Adult";
}


$temperature = 15;

if ($temperature >= 30) {
    echo "Very hot";
} else {
    echo "Pleasant temperature";
}


$hour = 14;

if ($hour < 6) {
    echo "Early morning";
} elseif ($hour < 12) {
    echo "Morning";
} elseif ($hour < 18) {
    echo "Afternoon";
} elseif ($hour < 24) {
    echo "Night";
} else {
    echo "Invalid time";
}


$isLoggedIn = true;
$hasPermission = false;

if ($isLoggedIn && $hasPermission) {
    echo "Access granted: admin panel";
} elseif ($isLoggedIn && !$hasPermission) {
    echo "Logged in but no admin permission. Redirecting...";
} else {
    echo "Please log in first";
}


$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$errors = [];

if ($email === '') {
    $errors[] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format.';
}

if ($password === '') {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}

if ($errors === []) {
    echo "All good! Proceeding...";
} else {
    foreach ($errors as $error) {
        echo "Error: {$error}\n";
    }
}
