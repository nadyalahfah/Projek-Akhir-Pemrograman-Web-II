<?php
/**
 * NexusTopup - Admin API
 * All admin CRUD actions via AJAX
 */
require_once __DIR__ . '/auth.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$action = $_POST['action'] ?? '';

switch ($action) {

    // ── DASHBOARD STATS ──────────────────────────────────
    case 'get_stats':
        jsonResponse(['success' => true, 'stats' => DB::getStats()]);

    // ═══════════════════════════════════════════════════
    // GAME MANAGEMENT
    // ═══════════════════════════════════════════════════

    case 'get_games':
        jsonResponse(['success' => true, 'games' => DB::getGames()]);

    case 'add_game':
        $name      = trim($_POST['name']     ?? '');
        $category  = trim($_POST['category'] ?? '');
        $currency  = trim($_POST['currency'] ?? '');
        $needsServer = ($_POST['needs_server_id'] ?? '0') === '1';
        $popular   = ($_POST['popular'] ?? '0') === '1';

        if (!$name || !$category || !$currency) {
            jsonResponse(['success' => false, 'message' => 'Nama, kategori, dan mata uang wajib diisi']);
        }

        $id = generateGameId($name);
        // Avoid duplicate ID
        if (DB::getGame($id)) {
            $id .= '-' . substr(uniqid(), -4);
        }

        // Handle image upload
        $imagePath = 'assets/images/default-game.png';
        if (!empty($_FILES['image']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $filename = $id . '.' . $ext;
                $dest = __DIR__ . '/../assets/images/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imagePath = 'assets/images/' . $filename;
                }
            }
        }

        $game = [
            'id'             => $id,
            'name'           => $name,
            'image'          => $imagePath,
            'category'       => $category,
            'popular'        => $popular,
            'active'         => true,
            'currency'       => $currency,
            'needs_server_id'=> $needsServer,
            'products'       => [],
        ];

        if (DB::addGame($game)) {
            jsonResponse(['success' => true, 'game' => $game]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal menyimpan game']);

    case 'update_game':
        $id       = trim($_POST['id']       ?? '');
        $name     = trim($_POST['name']     ?? '');
        $category = trim($_POST['category'] ?? '');
        $currency = trim($_POST['currency'] ?? '');
        $needsServer = ($_POST['needs_server_id'] ?? '0') === '1';
        $popular  = ($_POST['popular']  ?? '0') === '1';
        $active   = ($_POST['active']   ?? '1') === '1';

        if (!$id) jsonResponse(['success' => false, 'message' => 'ID game tidak valid']);

        $data = [
            'name'           => $name,
            'category'       => $category,
            'currency'       => $currency,
            'needs_server_id'=> $needsServer,
            'popular'        => $popular,
            'active'         => $active,
        ];

        // Handle image upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $filename = $id . '.' . $ext;
                $dest = __DIR__ . '/../assets/images/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $data['image'] = 'assets/images/' . $filename;
                }
            }
        }

        if (DB::updateGame($id, $data)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal mengupdate game']);

    case 'toggle_game':
        $id     = trim($_POST['id']     ?? '');
        $active = ($_POST['active'] ?? '1') === '1';
        if (!$id) jsonResponse(['success' => false, 'message' => 'ID tidak valid']);
        if (DB::updateGame($id, ['active' => $active])) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal update status']);

    case 'delete_game':
        $id = trim($_POST['id'] ?? '');
        if (!$id) jsonResponse(['success' => false, 'message' => 'ID tidak valid']);
        if (DB::deleteGame($id)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal hapus game']);

    // ═══════════════════════════════════════════════════
    // PRODUCT MANAGEMENT
    // ═══════════════════════════════════════════════════

    case 'add_product':
        $gameId   = trim($_POST['game_id']  ?? '');
        $amount   = trim($_POST['amount']   ?? '');
        $bonus    = trim($_POST['bonus']    ?? '');
        $price    = (int)($_POST['price']   ?? 0);
        $discount = (int)($_POST['discount'] ?? 0);
        $popular  = ($_POST['popular'] ?? '0') === '1';

        if (!$gameId || !$amount || !$price) {
            jsonResponse(['success' => false, 'message' => 'Jumlah dan harga wajib diisi']);
        }
        if ($discount < 0 || $discount > 90) {
            jsonResponse(['success' => false, 'message' => 'Diskon harus 0-90%']);
        }

        $product = [
            'id'       => generateProductId($gameId),
            'amount'   => $amount,
            'bonus'    => $bonus,
            'price'    => $price,
            'discount' => $discount,
            'popular'  => $popular,
            'active'   => true,
        ];

        if (DB::addProduct($gameId, $product)) {
            jsonResponse(['success' => true, 'product' => $product]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal menambah produk']);

    case 'update_product':
        $gameId    = trim($_POST['game_id']    ?? '');
        $productId = trim($_POST['product_id'] ?? '');
        $amount    = trim($_POST['amount']     ?? '');
        $bonus     = trim($_POST['bonus']      ?? '');
        $price     = (int)($_POST['price']     ?? 0);
        $discount  = (int)($_POST['discount']  ?? 0);
        $popular   = ($_POST['popular']  ?? '0') === '1';
        $active    = ($_POST['active']   ?? '1') === '1';

        if (!$gameId || !$productId) jsonResponse(['success' => false, 'message' => 'ID tidak valid']);

        $data = compact('amount', 'bonus', 'price', 'discount', 'popular', 'active');
        if (DB::updateProduct($gameId, $productId, $data)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal update produk']);

    case 'delete_product':
        $gameId    = trim($_POST['game_id']    ?? '');
        $productId = trim($_POST['product_id'] ?? '');
        if (!$gameId || !$productId) jsonResponse(['success' => false, 'message' => 'ID tidak valid']);
        if (DB::deleteProduct($gameId, $productId)) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal hapus produk']);

    // ═══════════════════════════════════════════════════
    // TRANSACTION MANAGEMENT
    // ═══════════════════════════════════════════════════

    case 'get_transactions':
        $all    = DB::getTransactions();
        $search = trim($_POST['search']  ?? '');
        $game   = trim($_POST['game']    ?? '');
        $status = trim($_POST['status']  ?? '');
        $page   = max(1, (int)($_POST['page'] ?? 1));
        $limit  = 20;

        if ($search) {
            $all = array_filter($all, function($t) use ($search) {
                $s = strtolower($search);
                return str_contains(strtolower($t['id'] ?? ''), $s)
                    || str_contains(strtolower($t['user_id'] ?? ''), $s)
                    || str_contains(strtolower($t['whatsapp'] ?? ''), $s)
                    || str_contains(strtolower($t['device_id'] ?? ''), $s)
                    || str_contains(strtolower($t['email'] ?? ''), $s);
            });
        }
        if ($game) {
            $all = array_filter($all, fn($t) => ($t['game_id'] ?? '') === $game);
        }
        if ($status) {
            $all = array_filter($all, fn($t) => ($t['status'] ?? '') === $status);
        }

        $all   = array_values($all);
        $total = count($all);
        $pages = (int)ceil($total / $limit);
        $slice = array_slice($all, ($page - 1) * $limit, $limit);

        jsonResponse([
            'success'      => true,
            'transactions' => $slice,
            'total'        => $total,
            'pages'        => $pages,
            'page'         => $page,
        ]);

    case 'update_txn_status':
        $id     = trim($_POST['id']     ?? '');
        $status = trim($_POST['status'] ?? '');
        $notes  = trim($_POST['notes']  ?? '');
        if (!$id || !in_array($status, ['success','pending','failed'])) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid']);
        }
        if (DB::updateTransaction($id, ['status' => $status, 'notes' => $notes])) {
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Gagal update transaksi']);

    // ── CHANGE ADMIN PASSWORD ────────────────────────────
    case 'change_password':
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if (strlen($new) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password baru minimal 6 karakter']);
        }
        $admin = DB::getAdmin();
        if (!password_verify($old, $admin['password'])) {
            jsonResponse(['success' => false, 'message' => 'Password lama salah']);
        }
        DB::updateAdminPassword(password_hash($new, PASSWORD_BCRYPT));
        jsonResponse(['success' => true, 'message' => 'Password berhasil diubah']);

    default:
        jsonResponse(['success' => false, 'message' => 'Action tidak dikenal'], 400);
}
