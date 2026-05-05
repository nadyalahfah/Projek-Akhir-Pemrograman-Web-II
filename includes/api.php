<?php
/**
 * NexusTopup - User API Handler
 * Handles AJAX requests from the main storefront
 */
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$action = $_POST['action'] ?? '';

switch ($action) {

    // ── INIT DEVICE ID ──────────────────────────────────
    case 'init_device':
        $deviceId = $_POST['device_id'] ?? '';
        if (!$deviceId || !preg_match('/^DEV[A-Z0-9]{12}$/', $deviceId)) {
            // Invalid or missing – generate a new one server-side
            $deviceId = 'DEV' . strtoupper(substr(md5(uniqid(mt_rand(), true) . $_SERVER['REMOTE_ADDR']), 0, 12));
        }
        jsonResponse(['success' => true, 'device_id' => $deviceId]);

    // ── GET GAMES ────────────────────────────────────────
    case 'get_games':
        $games = DB::getActiveGames();
        // Strip products, only send game meta
        $lite = array_map(function($g) {
            return [
                'id'       => $g['id'],
                'name'     => $g['name'],
                'image'    => $g['image'],
                'category' => $g['category'],
                'popular'  => $g['popular'] ?? false,
            ];
        }, $games);
        jsonResponse(['success' => true, 'games' => $lite]);

    // ── GET PRODUCTS ─────────────────────────────────────
    case 'get_products':
        $gameId = trim($_POST['game_id'] ?? '');
        $game   = DB::getGame($gameId);
        if (!$game || !($game['active'] ?? true)) {
            jsonResponse(['success' => false, 'message' => 'Game tidak ditemukan']);
        }
        $activeProducts = array_values(array_filter(
            $game['products'] ?? [],
            fn($p) => $p['active'] ?? true
        ));
        jsonResponse([
            'success'        => true,
            'game_name'      => $game['name'],
            'currency'       => $game['currency'],
            'needs_server_id'=> $game['needs_server_id'] ?? false,
            'products'       => $activeProducts,
        ]);

    // ── PLACE ORDER ──────────────────────────────────────
    case 'place_order':
        $deviceId  = trim($_POST['device_id'] ?? '');
        $gameId    = trim($_POST['game_id']   ?? '');
        $productId = trim($_POST['product_id'] ?? '');
        $userId    = trim($_POST['user_id']   ?? '');
        $serverId  = trim($_POST['server_id'] ?? '');
        $whatsapp  = trim($_POST['whatsapp']  ?? '');
        $paymentId = trim($_POST['payment_id'] ?? '');
        $email     = trim($_POST['email']     ?? '');

        // Validate required
        if (!$deviceId || !$gameId || !$productId || !$userId || !$whatsapp || !$paymentId) {
            jsonResponse(['success' => false, 'message' => 'Data tidak lengkap']);
        }

        $game = DB::getGame($gameId);
        if (!$game) jsonResponse(['success' => false, 'message' => 'Game tidak valid']);

        // Find product
        $product = null;
        foreach ($game['products'] as $p) {
            if ($p['id'] === $productId) { $product = $p; break; }
        }
        if (!$product) jsonResponse(['success' => false, 'message' => 'Produk tidak valid']);

        // Payment methods (hardcoded, admin can extend later)
        $payments = [
            'gopay'         => ['name' => 'GoPay',         'fee' => 0],
            'ovo'           => ['name' => 'OVO',           'fee' => 0],
            'dana'          => ['name' => 'DANA',          'fee' => 0],
            'bank-transfer' => ['name' => 'Transfer Bank', 'fee' => 0],
            'credit-card'   => ['name' => 'Kartu Kredit',  'fee' => 2500],
        ];
        $payment = $payments[$paymentId] ?? null;
        if (!$payment) jsonResponse(['success' => false, 'message' => 'Metode pembayaran tidak valid']);

        $finalPrice = calcFinalPrice((int)$product['price'], (int)($product['discount'] ?? 0));
        $total      = $finalPrice + (int)$payment['fee'];

        $txn = [
            'id'             => generateTxnId(),
            'date'           => date('Y-m-d H:i:s'),
            'device_id'      => $deviceId,
            'game_id'        => $gameId,
            'game_name'      => $game['name'],
            'currency'       => $game['currency'],
            'item'           => $product['amount'] . ' ' . $game['currency'] . ($product['bonus'] ? ' ' . $product['bonus'] : ''),
            'amount'         => $product['amount'],
            'bonus'          => $product['bonus'] ?? '',
            'user_id'        => $userId,
            'server_id'      => ($game['needs_server_id'] ?? false) ? $serverId : '',
            'whatsapp'       => $whatsapp,
            'email'          => $email,
            'payment_method' => $payment['name'],
            'payment_id'     => $paymentId,
            'base_price'     => $finalPrice,
            'fee'            => (int)$payment['fee'],
            'total'          => $total,
            'status'         => 'success',
            'notes'          => '',
        ];

        if (!DB::addTransaction($txn)) {
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan transaksi']);
        }

        jsonResponse(['success' => true, 'transaction' => $txn]);

    // ── GET DEVICE TRANSACTIONS ──────────────────────────
    case 'get_my_transactions':
        $deviceId = trim($_POST['device_id'] ?? '');
        if (!$deviceId) jsonResponse(['success' => false, 'message' => 'Device ID tidak valid']);
        $txns = DB::getTransactionsByDevice($deviceId);
        jsonResponse(['success' => true, 'transactions' => $txns]);

    default:
        jsonResponse(['success' => false, 'message' => 'Action tidak dikenal'], 400);
}
