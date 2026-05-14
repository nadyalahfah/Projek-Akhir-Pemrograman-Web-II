<?php
// =============================================
// NEXUSTOPUP - Database Helper (PDO MySQL)
// =============================================

require_once __DIR__ . '/../config.php';

class DB {
    private static ?PDO $pdo = null;

    public static function conn(): PDO {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $e->getMessage()]));
            }
        }
        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): PDOStatement {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ── GAMES ────────────────────────────────
    public static function getGames(): array {
        return self::run('SELECT * FROM games ORDER BY sort_order ASC, name ASC')->fetchAll();
    }

    public static function getActiveGames(): array {
        return self::run('SELECT * FROM games WHERE active=1 ORDER BY sort_order ASC, name ASC')->fetchAll();
    }

    public static function getGame(string $id): ?array {
        $r = self::run('SELECT * FROM games WHERE id=?', [$id])->fetch();
        return $r ?: null;
    }

    public static function addGame(array $d): bool {
        self::run(
            'INSERT INTO games (id,name,image,category,currency,needs_server_id,popular,active,sort_order) VALUES (?,?,?,?,?,?,?,1,?)',
            [$d['id'],$d['name'],$d['image'],$d['category'],$d['currency'],(int)$d['needs_server_id'],(int)$d['popular'],$d['sort_order']??0]
        );
        return true;
    }

    public static function updateGame(string $id, array $d): bool {
        $sets=[]; $params=[];
        foreach (['name','image','category','currency','needs_server_id','popular','active','sort_order'] as $c) {
            if (array_key_exists($c,$d)) { $sets[]="$c=?"; $params[]=$d[$c]; }
        }
        if (!$sets) return false;
        $params[]=$id;
        self::run('UPDATE games SET '.implode(',',$sets).' WHERE id=?', $params);
        return true;
    }

    public static function deleteGame(string $id): bool {
        self::run('DELETE FROM games WHERE id=?', [$id]);
        return true;
    }

    // ── PRODUCTS ─────────────────────────────
    public static function getProducts(string $gameId): array {
        return self::run('SELECT * FROM products WHERE game_id=? ORDER BY sort_order ASC, price ASC', [$gameId])->fetchAll();
    }

    public static function getActiveProducts(string $gameId): array {
        return self::run('SELECT * FROM products WHERE game_id=? AND active=1 ORDER BY sort_order ASC, price ASC', [$gameId])->fetchAll();
    }

    public static function getProduct(string $id): ?array {
        $r = self::run('SELECT * FROM products WHERE id=?', [$id])->fetch();
        return $r ?: null;
    }

    public static function addProduct(array $d): bool {
        $final = (int)$d['discount'] > 0 ? (int)round($d['price'] - ($d['price'] * $d['discount'] / 100)) : (int)$d['price'];
        self::run(
            'INSERT INTO products (id,game_id,amount,bonus,price,discount,final_price,popular,active,sort_order) VALUES (?,?,?,?,?,?,?,?,1,?)',
            [$d['id'],$d['game_id'],$d['amount'],$d['bonus']??'',(int)$d['price'],(int)($d['discount']??0),$final,(int)($d['popular']??0),$d['sort_order']??0]
        );
        return true;
    }

    public static function updateProduct(string $id, array $d): bool {
        if (isset($d['price']) || isset($d['discount'])) {
            $prod = self::getProduct($id);
            $price    = (int)($d['price']    ?? $prod['price']);
            $discount = (int)($d['discount'] ?? $prod['discount']);
            $d['final_price'] = $discount > 0 ? (int)round($price - ($price * $discount / 100)) : $price;
        }
        $sets=[]; $params=[];
        foreach (['amount','bonus','price','discount','final_price','popular','active','sort_order'] as $c) {
            if (array_key_exists($c,$d)) { $sets[]="$c=?"; $params[]=$d[$c]; }
        }
        if (!$sets) return false;
        $params[]=$id;
        self::run('UPDATE products SET '.implode(',',$sets).' WHERE id=?', $params);
        return true;
    }

    public static function deleteProduct(string $id): bool {
        self::run('DELETE FROM products WHERE id=?', [$id]);
        return true;
    }

    // ── TRANSACTIONS ─────────────────────────
    public static function addTransaction(array $d): bool {
        self::run(
            'INSERT INTO transactions (id,device_id,game_id,game_name,product_id,item,user_id,server_id,whatsapp,email,payment_method,payment_id,base_price,fee,total,status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$d['id'],$d['device_id'],$d['game_id'],$d['game_name'],$d['product_id'],$d['item'],
             $d['user_id'],$d['server_id']??'',$d['whatsapp'],$d['email']??'',
             $d['payment_method'],$d['payment_id'],(int)$d['base_price'],(int)$d['fee'],(int)$d['total'],$d['status']??'pending']
        );
        self::run(
            'INSERT INTO devices (id,txn_count) VALUES (?,1) ON DUPLICATE KEY UPDATE txn_count=txn_count+1, last_seen=NOW()',
            [$d['device_id']]
        );
        return true;
    }

    public static function getTransaction(string $id): ?array {
        $r = self::run('SELECT * FROM transactions WHERE id=?', [$id])->fetch();
        return $r ?: null;
    }

    public static function getTransactionsByDevice(string $deviceId): array {
        return self::run('SELECT * FROM transactions WHERE device_id=? ORDER BY created_at DESC', [$deviceId])->fetchAll();
    }

    public static function updateTransaction(string $id, array $d): bool {
        $sets=[]; $params=[];
        foreach (['status','digiflazz_ref','notes'] as $c) {
            if (array_key_exists($c,$d)) { $sets[]="$c=?"; $params[]=$d[$c]; }
        }
        if (!$sets) return false;
        $params[]=$id;
        self::run('UPDATE transactions SET '.implode(',',$sets).' WHERE id=?', $params);
        return true;
    }

    public static function getTransactionsFiltered(string $search='', string $game='', string $status='', int $page=1, int $limit=20): array {
        $where=['1=1']; $params=[];
        if ($search) {
            $where[]='(id LIKE ? OR user_id LIKE ? OR whatsapp LIKE ? OR device_id LIKE ? OR email LIKE ?)';
            $s="%$search%"; array_push($params,$s,$s,$s,$s,$s);
        }
        if ($game)   { $where[]='game_id=?';  $params[]=$game; }
        if ($status) { $where[]='status=?';   $params[]=$status; }
        $sql='WHERE '.implode(' AND ',$where);
        $total=(int)self::run("SELECT COUNT(*) FROM transactions $sql",$params)->fetchColumn();
        $pages=(int)ceil($total/$limit);
        $offset=($page-1)*$limit;
        $rows=self::run("SELECT * FROM transactions $sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset",$params)->fetchAll();
        return ['rows'=>$rows,'total'=>$total,'pages'=>max(1,$pages)];
    }

    // ── PAYMENT METHODS ──────────────────────
    public static function getPaymentMethods(): array {
        return self::run('SELECT * FROM payment_methods WHERE active=1 ORDER BY sort_order ASC')->fetchAll();
    }

    // ── ADMIN ────────────────────────────────
    public static function getAdmin(string $username): ?array {
        $r = self::run('SELECT * FROM admins WHERE username=?', [$username])->fetch();
        return $r ?: null;
    }

    public static function updateAdminPassword(int $id, string $hash): void {
        self::run('UPDATE admins SET password=? WHERE id=?', [$hash, $id]);
    }

    public static function touchAdminLogin(int $id): void {
        self::run('UPDATE admins SET last_login=NOW() WHERE id=?', [$id]);
    }

    // ── STATS ────────────────────────────────
    public static function getStats(): array {
        $c = self::conn();
        $today = date('Y-m-d');
        return [
            'total_transactions' => (int)$c->query('SELECT COUNT(*) FROM transactions')->fetchColumn(),
            'total_revenue'      => (int)$c->query("SELECT COALESCE(SUM(total),0) FROM transactions WHERE status='success'")->fetchColumn(),
            'total_devices'      => (int)$c->query('SELECT COUNT(*) FROM devices')->fetchColumn(),
            'total_games'        => (int)$c->query('SELECT COUNT(*) FROM games WHERE active=1')->fetchColumn(),
            'today_transactions' => (int)$c->query("SELECT COUNT(*) FROM transactions WHERE DATE(created_at)='$today'")->fetchColumn(),
            'today_revenue'      => (int)$c->query("SELECT COALESCE(SUM(total),0) FROM transactions WHERE status='success' AND DATE(created_at)='$today'")->fetchColumn(),
            'pending_count'      => (int)$c->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn(),
        ];
    }
}

// ── HELPERS ──────────────────────────────────
function formatPrice(int $price): string { return 'Rp '.number_format($price,0,',','.'); }
function calcFinalPrice(int $price, int $discount=0): int { return $discount>0?(int)round($price-($price*$discount/100)):$price; }
function generateTxnId(): string { return 'TRX'.strtoupper(substr(md5(uniqid(mt_rand(),true)),0,10)); }
function generateGameId(string $name): string { return strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$name),'-')); }
function generateProductId(string $gameId): string { return $gameId.'-'.substr(uniqid(),-6); }
function jsonResponse(array $data, int $code=200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
