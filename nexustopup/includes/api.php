<?php
// =============================================
// NEXUSTOPUP - User API
// =============================================
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success'=>false,'message'=>'Method not allowed'], 405);

$action = $_POST['action'] ?? '';

switch ($action) {

    case 'get_games':
        jsonResponse(['success'=>true, 'games'=>DB::getActiveGames()]);

    case 'get_products':
        $gameId = trim($_POST['game_id'] ?? '');
        $game   = DB::getGame($gameId);
        if (!$game || !$game['active']) jsonResponse(['success'=>false,'message'=>'Game tidak ditemukan']);
        jsonResponse([
            'success'         => true,
            'game_name'       => $game['name'],
            'currency'        => $game['currency'],
            'needs_server_id' => (bool)$game['needs_server_id'],
            'products'        => DB::getActiveProducts($gameId),
        ]);

    case 'get_payment_methods':
        jsonResponse(['success'=>true, 'methods'=>DB::getPaymentMethods()]);

    case 'place_order':
        $deviceId  = trim($_POST['device_id']  ?? '');
        $gameId    = trim($_POST['game_id']    ?? '');
        $productId = trim($_POST['product_id'] ?? '');
        $userId    = trim($_POST['user_id']    ?? '');
        $serverId  = trim($_POST['server_id']  ?? '');
        $whatsapp  = trim($_POST['whatsapp']   ?? '');
        $email     = trim($_POST['email']      ?? '');
        $paymentId = trim($_POST['payment_id'] ?? '');

        if (!$deviceId||!$gameId||!$productId||!$userId||!$whatsapp||!$paymentId)
            jsonResponse(['success'=>false,'message'=>'Data tidak lengkap']);

        $game    = DB::getGame($gameId);
        $product = DB::getProduct($productId);
        if (!$game||!$product) jsonResponse(['success'=>false,'message'=>'Data tidak valid']);

        if ($game['needs_server_id'] && empty($serverId))
            jsonResponse(['success'=>false,'message'=>'Server ID wajib diisi untuk game ini']);

        $methods = DB::getPaymentMethods();
        $payment = null;
        foreach ($methods as $m) { if ($m['id']===$paymentId) { $payment=$m; break; } }
        if (!$payment) jsonResponse(['success'=>false,'message'=>'Metode pembayaran tidak valid']);

        $finalPrice = (int)$product['final_price'];
        $fee        = (int)$payment['fee'];
        $total      = $finalPrice + $fee;
        $txnId      = generateTxnId();
        $itemLabel  = $product['amount'].' '.$game['currency'].($product['bonus'] ? ' '.$product['bonus'] : '');

        $txn = [
            'id'             => $txnId,
            'device_id'      => $deviceId,
            'game_id'        => $gameId,
            'game_name'      => $game['name'],
            'product_id'     => $productId,
            'item'           => $itemLabel,
            'user_id'        => $userId,
            'server_id'      => $serverId,
            'whatsapp'       => $whatsapp,
            'email'          => $email,
            'payment_method' => $payment['name'],
            'payment_id'     => $paymentId,
            'base_price'     => $finalPrice,
            'fee'            => $fee,
            'total'          => $total,
            'status'         => 'pending',
        ];

        if (!DB::addTransaction($txn)) jsonResponse(['success'=>false,'message'=>'Gagal menyimpan transaksi']);
        $txn['created_at'] = date('Y-m-d H:i:s');
        jsonResponse(['success'=>true,'transaction'=>$txn]);

    case 'get_my_transactions':
        $deviceId = trim($_POST['device_id'] ?? '');
        if (!$deviceId) jsonResponse(['success'=>false,'message'=>'Device ID tidak valid']);
        jsonResponse(['success'=>true,'transactions'=>DB::getTransactionsByDevice($deviceId)]);

    default:
        jsonResponse(['success'=>false,'message'=>'Action tidak dikenal'], 400);
}
