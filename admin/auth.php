<?php
/**
 * NexusTopup - Admin Auth Helper
 */
session_start();
require_once __DIR__ . '/../includes/db.php';

define('ADMIN_SESSION_KEY', 'nx_admin_logged_in');

function isAdminLoggedIn(): bool {
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function adminLogin(string $username, string $password): bool {
    $admin = DB::getAdmin();
    if (!$admin) return false;
    if ($username !== $admin['username']) return false;

    $stored = $admin['password'];

    // Detect if stored password is a bcrypt hash or plain text
    $isHashed = (strlen($stored) === 60 && str_starts_with($stored, '$2'));

    if ($isHashed) {
        // Normal bcrypt verify
        if (!password_verify($password, $stored)) return false;
    } else {
        // Plain text (first run) — verify then immediately hash & save
        if ($password !== $stored) return false;
        DB::updateAdminPassword(password_hash($stored, PASSWORD_BCRYPT));
    }

    $_SESSION[ADMIN_SESSION_KEY] = true;
    $_SESSION['admin_name'] = $admin['name'] ?? 'Admin';
    DB::touchAdminLogin();
    return true;
}

function adminLogout(): void {
    unset($_SESSION[ADMIN_SESSION_KEY], $_SESSION['admin_name']);
    session_destroy();
    header('Location: login.php');
    exit;
}
