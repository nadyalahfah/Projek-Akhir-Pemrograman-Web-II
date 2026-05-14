<?php
// =============================================
// NEXUSTOPUP - Admin API
// =============================================
require_once __DIR__ . '/auth.php';
requireAdmin();

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['success'=>false,'message'=>'Method not allowed'], 405);

$action = $_POST['action'] ?? '';

// ── HELPER: Upload Gambar ─────────────────────
function handleImageUpload(string $gameId): ?string {
    if (empty($_FILES['image']['tmp_name'])) return null;
    $file    = $_FILES['image'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed))        return null;
    if ($file['size'] > 3 * 1024 * 1024) return null;
    if (!getimagesize($file['tmp_name'])) return null;
    $imgDir  = __DIR__ . '/../assets/images/';
    // Hapus gambar lama (kalau ada)
    foreach (glob($imgDir . $gameId . '.*') as $old) {
        if (!str_contains(basename($old), 'default')) @unlink($old);
    }
    $filename = $gameId . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $imgDir . $filename)) return null;
    return 'assets/images/' . $filename;
}

switch ($action) {

    // ── STATS ─────────────────────────────────
    case 'get_stats':
        jsonResponse(['success'=>true,'stats'=>DB::getStats()]);

    // ═══ GAMES ════════════════════════════════
    case 'get_games':
        $games = DB::getGames();
        foreach ($games as &$g) $g['products'] = DB::getProducts($g['id']);
        jsonResponse(['success'=>true,'games'=>$games]);

    case 'add_game':
        $name        = trim($_POST['name']     ?? '');
        $category    = trim($_POST['category'] ?? '');
        $currency    = trim($_POST['currency'] ?? '');
        $needsServer = (int)(($_POST['needs_server_id'] ?? '0') === '1');
        $popular     = (int)(($_POST['popular'] ?? '0') === '1');
        if (!$name||!$category||!$currency)
            jsonResponse(['success'=>false,'message'=>'Nama, kategori, mata uang wajib diisi']);
        $id = generateGameId($name);
        if (DB::getGame($id)) $id .= '-'.substr(uniqid(),-4);
        $imagePath = handleImageUpload($id) ?? 'assets/images/default-game.svg';
        $game = ['id'=>$id,'name'=>$name,'image'=>$imagePath,'category'=>$category,
                 'currency'=>$currency,'needs_server_id'=>$needsServer,'popular'=>$popular,'sort_order'=>0];
        if (DB::addGame($game)) jsonResponse(['success'=>true,'game'=>$game]);
        jsonResponse(['success'=>false,'message'=>'Gagal menyimpan game']);

    case 'update_game':
        $id = trim($_POST['id'] ?? '');
        if (!$id) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);
        $data = [
            'name'           => trim($_POST['name']     ?? ''),
            'category'       => trim($_POST['category'] ?? ''),
            'currency'       => trim($_POST['currency'] ?? ''),
            'needs_server_id'=> (int)(($_POST['needs_server_id'] ?? '0') === '1'),
            'popular'        => (int)(($_POST['popular'] ?? '0') === '1'),
            'active'         => (int)(($_POST['active']  ?? '1') === '1'),
        ];
        $newImage = handleImageUpload($id);
        if ($newImage) $data['image'] = $newImage;
        if (DB::updateGame($id,$data)) jsonResponse(['success'=>true]);
        jsonResponse(['success'=>false,'message'=>'Gagal update game']);

    case 'toggle_game':
        $id     = trim($_POST['id']     ?? '');
        $active = (int)(($_POST['active'] ?? '1') === '1');
        if (!$id) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);
        if (DB::updateGame($id,['active'=>$active])) jsonResponse(['success'=>true]);
        jsonResponse(['success'=>false,'message'=>'Gagal update status']);

    case 'delete_game':
        $id = trim($_POST['id'] ?? '');
        if (!$id) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);
        foreach (glob(__DIR__.'/../assets/images/'.$id.'.*') as $f) {
            if (!str_contains(basename($f),'default')) @unlink($f);
        }
        if (DB::deleteGame($id)) jsonResponse(['success'=>true]);
        jsonResponse(['success'=>false,'message'=>'Gagal hapus game']);

    // ═══ PRODUCTS ═════════════════════════════
    case 'get_products':
        $gameId = trim($_POST['game_id'] ?? '');
        jsonResponse(['success'=>true,'products'=>DB::getProducts($gameId)]);

    case 'add_product':
        $gameId   = trim($_POST['game_id']  ?? '');
        $amount   = trim($_POST['amount']   ?? '');
        $bonus    = trim($_POST['bonus']    ?? '');
        $price    = (int)($_POST['price']   ?? 0);
        $discount = (int)($_POST['discount']?? 0);
        $popular  = (int)(($_POST['popular']?? '0') === '1');
        if (!$gameId||!$amount||!$price) jsonResponse(['success'=>false,'message'=>'Data tidak lengkap']);
        if ($discount<0||$discount>90)   jsonResponse(['success'=>false,'message'=>'Diskon harus 0–90%']);
        $prod = ['id'=>generateProductId($gameId),'game_id'=>$gameId,'amount'=>$amount,
                 'bonus'=>$bonus,'price'=>$price,'discount'=>$discount,'popular'=>$popular,'sort_order'=>0];
        if (DB::addProduct($prod)) jsonResponse(['success'=>true,'product'=>$prod]);
        jsonResponse(['success'=>false,'message'=>'Gagal tambah produk']);

    case 'update_product':
        $id = trim($_POST['product_id'] ?? '');
        if (!$id) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);
        $data = [
            'amount'   => trim($_POST['amount']   ?? ''),
            'bonus'    => trim($_POST['bonus']    ?? ''),
            'price'    => (int)($_POST['price']   ?? 0),
            'discount' => (int)($_POST['discount']?? 0),
            'popular'  => (int)(($_POST['popular']?? '0') === '1'),
            'active'   => (int)(($_POST['active'] ?? '1') === '1'),
        ];
        if (DB::updateProduct($id,$data)) jsonResponse(['success'=>true]);
        jsonResponse(['success'=>false,'message'=>'Gagal update produk']);

    case 'delete_product':
        $id = trim($_POST['product_id'] ?? '');
        if (!$id) jsonResponse(['success'=>false,'message'=>'ID tidak valid']);
        if (DB::deleteProduct($id)) jsonResponse(['success'=>true]);
        jsonResponse(['success'=>false,'message'=>'Gagal hapus produk']);

    // ═══ TRANSACTIONS ═════════════════════════
    case 'get_transactions':
        $res = DB::getTransactionsFiltered(
            trim($_POST['search'] ?? ''),
            trim($_POST['game']   ?? ''),
            trim($_POST['status'] ?? ''),
            (int)($_POST['page']  ?? 1)
        );
        jsonResponse(['success'=>true,'transactions'=>$res['rows'],
                      'total'=>$res['total'],'pages'=>$res['pages'],'page'=>(int)($_POST['page']??1)]);

    case 'update_txn_status':
        $id     = trim($_POST['id']     ?? '');
        $status = trim($_POST['status'] ?? '');
        $notes  = trim($_POST['notes']  ?? '');
        if (!$id||!in_array($status,['pending','processing','success','failed']))
            jsonResponse(['success'=>false,'message'=>'Data tidak valid']);
        if (DB::updateTransaction($id,['status'=>$status,'notes'=>$notes])) jsonResponse(['success'=>true]);
        jsonResponse(['success'=>false,'message'=>'Gagal update transaksi']);

    // ═══ SETTINGS ═════════════════════════════
    case 'change_password':
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if (strlen($new) < 6) jsonResponse(['success'=>false,'message'=>'Password baru minimal 6 karakter']);
        $admin = DB::getAdmin($_SESSION['admin_name'] ?? '');
        if (!$admin) jsonResponse(['success'=>false,'message'=>'Admin tidak ditemukan']);
        $isHashed = strlen($admin['password'])===60 && str_starts_with($admin['password'],'$2');
        $valid    = $isHashed ? password_verify($old,$admin['password']) : ($old===$admin['password']);
        if (!$valid) jsonResponse(['success'=>false,'message'=>'Password lama salah']);
        DB::updateAdminPassword($admin['id'], password_hash($new, PASSWORD_BCRYPT));
        jsonResponse(['success'=>true,'message'=>'Password berhasil diubah']);

    default:
        jsonResponse(['success'=>false,'message'=>'Action tidak dikenal'], 400);
}
