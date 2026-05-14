<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

define('ADMIN_SESSION_KEY', 'nx_admin_logged_in');
define('ADMIN_ID_KEY',      'nx_admin_id');

function isAdminLoggedIn(): bool {
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) { header('Location: login.php'); exit; }
}

function adminLogin(string $username, string $password): bool {
    $admin = DB::getAdmin($username);
    if (!$admin) return false;

    $stored  = $admin['password'];
    $isHashed = strlen($stored) === 60 && str_starts_with($stored, '$2');

    if ($isHashed) {
        if (!password_verify($password, $stored)) return false;
    } else {
        // Plain text (pertama kali dari SQL seed) — hash langsung
        if ($password !== $stored) return false;
        DB::updateAdminPassword($admin['id'], password_hash($stored, PASSWORD_BCRYPT));
    }

    $_SESSION[ADMIN_SESSION_KEY] = true;
    $_SESSION[ADMIN_ID_KEY]      = $admin['id'];
    $_SESSION['admin_name']      = $admin['name'];
    DB::touchAdminLogin($admin['id']);
    return true;
}

function adminLogout(): void {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
