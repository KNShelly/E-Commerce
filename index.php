<?php
// Entry point and basic router
session_start();

require_once __DIR__ . '/config/db.php'; // $pdo may be null if not configured
require_once __DIR__ . '/includes/db_functions.php';

// Basic router
$page = $_GET['page'] ?? 'home';
$allowed = ['home','product','cart','checkout','login','register','logout'];
if (!in_array($page, $allowed, true)) {
    $page = 'home';
}

// Handle logout before any output
if ($page === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: index.php?page=home');
    exit;
}

// Handle auth POST (login/register) before output for proper redirects
$auth_error = null;
if ($page === 'login' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $auth_error = 'Please enter your email and password.';
    } else {
        $user = get_user_by_email($email);
        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'roles' => isset($user['roles']) && $user['roles'] !== null ? explode(', ', $user['roles']) : []
            ];
            header('Location: index.php?page=home');
            exit;
        } else {
            $auth_error = 'Invalid email or password.';
        }
    }
}

if ($page === 'register' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || $email === '' || $password === '') {
        $auth_error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $auth_error = 'Please enter a valid email address.';
    } else {
        $existing = get_user_by_email($email);
        if ($existing) {
            $auth_error = 'An account with this email already exists.';
        } else {
            $user_id = create_user($name, $email, $password);
            if ($user_id) {
                $user = get_user_by_email($email);
                $_SESSION['user'] = [
                    'id' => (int)$user_id,
                    'name' => $name,
                    'email' => $email,
                    'roles' => isset($user['roles']) && $user['roles'] !== null ? explode(', ', $user['roles']) : ['customer']
                ];
                header('Location: index.php?page=home');
                exit;
            } else {
                $auth_error = 'Failed to create account. Please try again.';
            }
        }
    }
}

// Handle add to cart from product page
$cart_error = null;
if ($page === 'product' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if (!isset($_SESSION['user'])) {
        $cart_error = 'Please login to add items to your cart.';
    } elseif ($product_id <= 0) {
        $cart_error = 'Invalid product selection.';
    } else {
        $user_id = (int)$_SESSION['user']['id'];
        if (add_to_cart($user_id, $product_id, $quantity)) {
            header('Location: index.php?page=cart');
            exit;
        } else {
            $cart_error = 'Unable to add item to cart. Please try again.';
        }
    }
}

// Render layout
include __DIR__ . '/includes/header.php';

echo "<main class=\"container\">";
include __DIR__ . "/pages/{$page}.php";
echo "</main>";

include __DIR__ . '/includes/footer.php';