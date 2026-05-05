<?php
/**
 * NexusTopup - Database Helper (JSON File-based)
 * Handles all read/write operations to JSON files
 */

define('DATA_DIR', __DIR__ . '/../data/');
define('GAMES_FILE',        DATA_DIR . 'games.json');
define('TRANSACTIONS_FILE', DATA_DIR . 'transactions.json');
define('ADMIN_FILE',        DATA_DIR . 'admin.json');

class DB {

    // ── READ ──────────────────────────────────────────
    public static function getGames(): array {
        return self::readJson(GAMES_FILE) ?? [];
    }

    public static function getGame(string $id): ?array {
        foreach (self::getGames() as $g) {
            if ($g['id'] === $id) return $g;
        }
        return null;
    }

    public static function getActiveGames(): array {
        return array_values(array_filter(self::getGames(), fn($g) => $g['active'] ?? true));
    }

    public static function getTransactions(): array {
        return self::readJson(TRANSACTIONS_FILE) ?? [];
    }

    public static function getTransactionsByDevice(string $deviceId): array {
        return array_values(array_filter(
            self::getTransactions(),
            fn($t) => ($t['device_id'] ?? '') === $deviceId
        ));
    }

    public static function getAdmin(): ?array {
        return self::readJson(ADMIN_FILE);
    }

    // ── WRITE ─────────────────────────────────────────
    public static function saveGames(array $games): bool {
        return self::writeJson(GAMES_FILE, array_values($games));
    }

    public static function addTransaction(array $txn): bool {
        $all = self::getTransactions();
        array_unshift($all, $txn);
        return self::writeJson(TRANSACTIONS_FILE, $all);
    }

    public static function updateTransaction(string $id, array $data): bool {
        $all = self::getTransactions();
        foreach ($all as &$t) {
            if ($t['id'] === $id) {
                $t = array_merge($t, $data);
                break;
            }
        }
        return self::writeJson(TRANSACTIONS_FILE, $all);
    }

    public static function updateAdminPassword(string $newHash): bool {
        $admin = self::getAdmin();
        $admin['password'] = $newHash;
        return self::writeJson(ADMIN_FILE, $admin);
    }

    public static function touchAdminLogin(): void {
        $admin = self::getAdmin();
        $admin['last_login'] = date('Y-m-d H:i:s');
        self::writeJson(ADMIN_FILE, $admin);
    }

    // ── GAME MANAGEMENT ──────────────────────────────
    public static function addGame(array $game): bool {
        $games = self::getGames();
        $games[] = $game;
        return self::saveGames($games);
    }

    public static function updateGame(string $id, array $data): bool {
        $games = self::getGames();
        foreach ($games as &$g) {
            if ($g['id'] === $id) {
                // Preserve products unless explicitly passed
                if (!isset($data['products'])) {
                    $data['products'] = $g['products'] ?? [];
                }
                $g = array_merge($g, $data);
                break;
            }
        }
        return self::saveGames($games);
    }

    public static function deleteGame(string $id): bool {
        $games = array_filter(self::getGames(), fn($g) => $g['id'] !== $id);
        return self::saveGames(array_values($games));
    }

    // ── PRODUCT MANAGEMENT ───────────────────────────
    public static function addProduct(string $gameId, array $product): bool {
        $games = self::getGames();
        foreach ($games as &$g) {
            if ($g['id'] === $gameId) {
                $g['products'][] = $product;
                break;
            }
        }
        return self::saveGames($games);
    }

    public static function updateProduct(string $gameId, string $productId, array $data): bool {
        $games = self::getGames();
        foreach ($games as &$g) {
            if ($g['id'] === $gameId) {
                foreach ($g['products'] as &$p) {
                    if ($p['id'] === $productId) {
                        $p = array_merge($p, $data);
                        break;
                    }
                }
                break;
            }
        }
        return self::saveGames($games);
    }

    public static function deleteProduct(string $gameId, string $productId): bool {
        $games = self::getGames();
        foreach ($games as &$g) {
            if ($g['id'] === $gameId) {
                $g['products'] = array_values(array_filter(
                    $g['products'],
                    fn($p) => $p['id'] !== $productId
                ));
                break;
            }
        }
        return self::saveGames($games);
    }

    // ── STATS ─────────────────────────────────────────
    public static function getStats(): array {
        $txns = self::getTransactions();
        $devices = array_unique(array_column($txns, 'device_id'));
        $total_rev = array_sum(array_column($txns, 'total'));
        $today = date('Y-m-d');
        $today_txns = array_filter($txns, fn($t) => str_starts_with($t['date'] ?? '', $today));
        $today_rev = array_sum(array_column(array_values($today_txns), 'total'));

        return [
            'total_transactions' => count($txns),
            'total_revenue'      => $total_rev,
            'total_devices'      => count($devices),
            'total_games'        => count(self::getActiveGames()),
            'today_transactions' => count($today_txns),
            'today_revenue'      => $today_rev,
        ];
    }

    // ── INTERNAL ──────────────────────────────────────
    private static function readJson(string $file): mixed {
        if (!file_exists($file)) return null;
        $content = file_get_contents($file);
        return json_decode($content, true);
    }

    private static function writeJson(string $file, mixed $data): bool {
        $dir = dirname($file);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $result = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $result !== false;
    }
}

// ── HELPERS ───────────────────────────────────────────
function formatPrice(int $price): string {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function calcFinalPrice(int $price, int $discount = 0): int {
    if (!$discount) return $price;
    return (int)round($price - ($price * $discount / 100));
}

function generateTxnId(): string {
    return 'TRX' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
}

function generateGameId(string $name): string {
    return strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
}

function generateProductId(string $gameId): string {
    return $gameId . '-' . substr(uniqid(), -6);
}

function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect(string $url): never {
    header("Location: $url");
    exit;
}
