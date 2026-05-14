<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$payment_methods = DB::getPaymentMethods();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NexusTopup - Top Up Game Terpercaya</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
    const PAYMENTS_DATA = <?php echo json_encode($payment_methods); ?>;
    const API_URL = 'includes/api.php';
</script>
</head>
<body>


<header class="header">
    <div class="header-inner">
        <div class="logo" onclick="navigateTo('home')">
            <div class="logo-icon"><i class="fas fa-bolt"></i></div>
            <span class="logo-text">NEXUS<span style="color:var(--text-secondary);font-weight:400;">TOPUP</span></span>
        </div>
        <nav class="nav">
            <button class="nav-btn active" id="nav-home" onclick="navigateTo('home')">
                <i class="fas fa-home"></i><span>Beranda</span>
            </button>
            <button class="nav-btn" id="nav-history" onclick="navigateTo('history')">
                <i class="fas fa-receipt"></i><span>Transaksi</span>
            </button>
        </nav>
    </div>
</header>

<main class="main">

    <div class="page active" id="page-home">

        <div class="view active" id="view-games">
            <div class="hero">
                <div class="hero-badge"><i class="fas fa-shield-halved"></i> Platform Terpercaya #1</div>
                <h1>Top Up Game<br>Favoritmu</h1>
                <p>Proses instan, harga terbaik, berbagai metode pembayaran.</p>
                <div class="features-strip">
                    <span class="feature-badge"><i class="fas fa-bolt"></i> Proses Cepat</span>
                    <span class="feature-badge"><i class="fas fa-lock"></i> Aman & Terjamin</span>
                    <span class="feature-badge"><i class="fas fa-fingerprint"></i> Tanpa Akun</span>
                    <span class="feature-badge"><i class="fas fa-tag"></i> Harga Terbaik</span>
                </div>
                <div class="hero-stats">
                    <div class="stat-item"><div class="stat-value">50K+</div><div class="stat-label">Transaksi</div></div>
                    <div class="stat-item"><div class="stat-value" id="hero-game-count">8</div><div class="stat-label">Game Tersedia</div></div>
                    <div class="stat-item"><div class="stat-value">99.9%</div><div class="stat-label">Uptime</div></div>
                </div>
            </div>
            <div class="section-header">
                <h2 class="section-title">Pilih Game</h2>
            </div>
            <div class="games-grid" id="games-grid"></div>
        </div>

        <div class="view" id="view-products">
            <div class="back-header">
                <button class="back-btn" onclick="backToGames()"><i class="fas fa-arrow-left"></i></button>
                <div>
                    <h2 id="product-game-name">Game</h2>
                    <p id="product-game-sub">Pilih paket top up</p>
                </div>
            </div>
            <div class="products-grid" id="products-grid"></div>
        </div>

        <div class="view" id="view-order">
            <div class="back-header">
                <button class="back-btn" onclick="backToProducts()"><i class="fas fa-arrow-left"></i></button>
                <div>
                    <h2 id="order-game-name">Detail Pesanan</h2>
                    <p id="order-game-sub">Lengkapi informasi berikut</p>
                </div>
            </div>
            <div class="order-layout">
                <div>
                    <div class="card">
                        <div class="card-title"><i class="fas fa-gamepad"></i> Data Akun Game</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user-id">User ID <span style="color:var(--danger)">*</span></label>
                                <input type="text" id="user-id" placeholder="Masukkan User ID" autocomplete="off">
                            </div>
                            <div class="form-group hidden" id="server-id-group">
                                <label for="server-id">Server / Zone ID</label>
                                <input type="text" id="server-id" placeholder="Masukkan Server ID">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="whatsapp">Nomor WhatsApp <span style="color:var(--danger)">*</span></label>
                            <input type="tel" id="whatsapp" placeholder="08xxxxxxxxxx">
                            <div class="form-hint"><i class="fab fa-whatsapp" style="color:#25D366;margin-right:4px;"></i>Untuk konfirmasi pesanan</div>
                        </div>
                        <div class="form-group">
                            <label for="order-email">Email <span style="color:var(--text-muted);font-weight:400;">(opsional - untuk bukti transaksi)</span></label>
                            <input type="email" id="order-email" placeholder="email@kamu.com">
                            <div class="form-hint"><i class="fas fa-envelope" style="color:var(--accent);margin-right:4px;"></i>Bukti transaksi dikirim ke email ini</div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-title"><i class="fas fa-wallet"></i> Metode Pembayaran</div>
                        <div class="payment-list" id="payment-list"></div>
                    </div>
                </div>
                <div class="order-summary">
                    <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-receipt"></i> Ringkasan Pesanan</div>
                    <div class="summary-row"><span>Game</span><span id="sum-game">-</span></div>
                    <div class="summary-row"><span>Item</span><span id="sum-item">-</span></div>
                    <div class="summary-divider"></div>
                    <div class="summary-row"><span>Harga</span><span id="sum-price">-</span></div>
                    <div class="summary-row hidden" id="sum-fee-row"><span>Biaya Admin</span><span id="sum-fee">-</span></div>
                    <div class="summary-total"><span>Total</span><span id="sum-total">-</span></div>
                    <button class="btn btn-primary" id="submit-order-btn" onclick="showConfirmDialog()" disabled>
                        <i class="fas fa-lock"></i> Buat Pesanan
                    </button>
                    <p class="disclaimer"><i class="fas fa-shield-halved" style="margin-right:4px;"></i>Transaksi aman dan terenkripsi.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="page" id="page-history">
        <div class="page-hero">
            <div class="page-hero-icon"><i class="fas fa-receipt"></i></div>
            <h1>Riwayat Transaksi</h1>
            <p>Semua transaksi perangkat ini</p>
        </div>

        <div style="text-align:center;margin-bottom:22px;">
            <div style="display:inline-flex;align-items:center;gap:8px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:10px 18px;font-size:13px;">
                <i class="fas fa-fingerprint" style="color:var(--accent);"></i>
                <span style="color:var(--text-muted);">Device ID:</span>
                <code id="history-device-id" style="font-family:monospace;color:var(--accent);"></code>
                <button onclick="copyDeviceId()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:12px;padding:2px 6px;" title="Salin">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <div id="email-send-section" style="max-width:520px;margin:0 auto 28px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px 20px;">
            <div style="font-size:14px;font-weight:600;margin-bottom:12px;color:var(--text-primary);">
                <i class="fas fa-envelope" style="color:var(--accent);margin-right:7px;"></i>Kirim Riwayat ke Email
            </div>
            <div style="display:flex;gap:10px;">
                <input type="email" id="send-email-input" placeholder="Masukkan email kamu" style="flex:1;background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;color:var(--text-primary);font-size:13.5px;outline:none;font-family:'Inter',sans-serif;">
                <button onclick="sendHistoryByEmail()" class="btn btn-primary" style="margin-top:0;white-space:nowrap;width:auto;padding:10px 18px;">
                    <i class="fas fa-paper-plane"></i> Kirim
                </button>
            </div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-top:7px;">
                Riwayat transaksi perangkat ini akan dikirim ke email tersebut (fitur simulasi).
            </div>
        </div>

        <div id="history-list"></div>
        <div class="card empty-state hidden" id="history-empty">
            <div class="empty-icon"><i class="fas fa-receipt"></i></div>
            <h3>Belum Ada Transaksi</h3>
            <p>Mulai top up game favoritmu sekarang!</p>
            <button class="btn btn-outline mt-4" onclick="navigateTo('home')" style="margin-top:16px;display:inline-flex;">
                <i class="fas fa-gamepad"></i>&nbsp;Top Up Sekarang
            </button>
        </div>
    </div>

</main>

<div class="dialog-overlay" id="confirm-dialog">
    <div class="dialog">
        <div class="dialog-header">
            <h2><i class="fas fa-receipt" style="color:var(--accent);"></i> Konfirmasi Pesanan</h2>
            <p>Pastikan data di bawah sudah benar</p>
        </div>
        <div class="dialog-body">
            <div class="confirm-row"><span>Game</span><span id="conf-game">-</span></div>
            <div class="confirm-row"><span>Item</span><span id="conf-item">-</span></div>
            <div class="confirm-row"><span>User ID</span><span id="conf-userid">-</span></div>
            <div class="confirm-row hidden" id="conf-server-row"><span>Server ID</span><span id="conf-server">-</span></div>
            <div class="confirm-row"><span>WhatsApp</span><span id="conf-wa">-</span></div>
            <div class="confirm-row" id="conf-email-row" style="display:none;"><span>Email</span><span id="conf-email">-</span></div>
            <div class="confirm-row"><span>Pembayaran</span><span id="conf-payment">-</span></div>
            <div class="confirm-row" style="border-bottom:none;padding-top:14px;">
                <span style="font-weight:600;color:var(--text-primary);">Total</span>
                <span class="confirm-total" id="conf-total">-</span>
            </div>
        </div>
        <div class="dialog-footer">
            <button class="btn btn-secondary" onclick="closeConfirmDialog()" style="margin-top:0;"><i class="fas fa-times"></i> Batal</button>
            <button class="btn btn-primary" onclick="confirmOrder()" style="margin-top:0;" id="confirm-btn"><i class="fas fa-check-circle"></i> Konfirmasi & Bayar</button>
        </div>
    </div>
</div>

<div class="dialog-overlay" id="success-dialog">
    <div class="dialog">
        <div class="success-icon-wrap">
            <div class="success-icon-ring"><i class="fas fa-check"></i></div>
        </div>
        <div class="dialog-header">
            <h2 style="color:var(--neon-green);">Pesanan Berhasil!</h2>
            <p>Transaksi Anda sedang diproses.</p>
        </div>
        <div class="dialog-body">
            <div class="confirm-row"><span>ID Transaksi</span><span id="suc-id" style="font-family:monospace;font-size:12px;color:var(--accent);">-</span></div>
            <div class="confirm-row"><span>Game</span><span id="suc-game">-</span></div>
            <div class="confirm-row"><span>Item</span><span id="suc-item">-</span></div>
            <div class="confirm-row"><span>Pembayaran</span><span id="suc-payment">-</span></div>
            <div class="confirm-row" style="border-bottom:none;padding-top:14px;">
                <span style="font-weight:600;color:var(--text-primary);">Total</span>
                <span class="confirm-total" id="suc-total">-</span>
            </div>
        </div>
        <div class="dialog-footer">
            <button class="btn btn-secondary" onclick="goToHistory()" style="margin-top:0;"><i class="fas fa-receipt"></i> Lihat Riwayat</button>
            <button class="btn btn-primary" onclick="closeSuccessDialog()" style="margin-top:0;"><i class="fas fa-home"></i> Beranda</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
<script src="assets/js/app.js"></script>
</body>
</html>
