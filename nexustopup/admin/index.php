<?php
require_once __DIR__ . '/auth.php';
requireAdmin();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel – NexusTopup</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@700;900&family=Rajdhani:wght@600;700&display=swap');
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#08090d;--bg2:#0e1117;--card:#131722;--card2:#1a2030;
  --border:rgba(255,255,255,0.07);--border2:rgba(255,255,255,0.12);
  --accent:#63b3ed;--accent2:#9f7aea;--accent3:#68d391;
  --text:#e8eaed;--muted:#8892a4;--dim:#4a5568;
  --danger:#fc8181;--warning:#f6e05e;--success:#68d391;
  --r:12px;--r2:18px;
  --shadow:0 4px 30px rgba(0,0,0,0.4);
  --glow:0 0 25px rgba(99,179,237,0.15);
}
body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;display:flex;min-height:100vh;}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:var(--bg2)}
::-webkit-scrollbar-thumb{background:var(--dim);border-radius:3px}

/* ── SIDEBAR ── */
.sidebar{width:240px;min-height:100vh;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;flex-shrink:0;z-index:100;}
.sidebar-logo{padding:22px 20px 18px;border-bottom:1px solid var(--border);}
.logo-row{display:flex;align-items:center;gap:10px;}
.logo-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff;box-shadow:0 0 18px rgba(99,179,237,0.35);flex-shrink:0;}
.logo-text{font-family:'Orbitron',sans-serif;font-weight:900;font-size:14px;background:linear-gradient(90deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.sidebar-admin{padding:14px 20px;border-bottom:1px solid var(--border);font-size:12px;color:var(--muted);}
.sidebar-admin strong{display:block;color:var(--text);font-size:13px;margin-bottom:2px;}
.sidebar-nav{flex:1;padding:12px 0;}
.nav-section{padding:8px 20px 4px;font-size:10px;font-weight:700;color:var(--dim);text-transform:uppercase;letter-spacing:1px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 20px;color:var(--muted);font-size:13.5px;cursor:pointer;transition:.2s;border-left:3px solid transparent;position:relative;}
.nav-item:hover{color:var(--text);background:rgba(255,255,255,0.03);}
.nav-item.active{color:var(--accent);background:rgba(99,179,237,0.07);border-left-color:var(--accent);}
.nav-item i{width:16px;text-align:center;font-size:13px;}
.sidebar-footer{padding:16px 20px;border-top:1px solid var(--border);}
.btn-logout{display:flex;align-items:center;gap:8px;width:100%;padding:9px 14px;background:rgba(252,129,129,0.08);border:1px solid rgba(252,129,129,0.2);border-radius:var(--r);color:var(--danger);font-size:13px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;transition:.2s;}
.btn-logout:hover{background:rgba(252,129,129,0.15);}

/* ── MAIN CONTENT ── */
.content{flex:1;display:flex;flex-direction:column;min-width:0;}
.topbar{background:var(--bg2);border-bottom:1px solid var(--border);padding:0 28px;height:58px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
.topbar-title{font-family:'Rajdhani',sans-serif;font-size:20px;font-weight:700;color:var(--text);}
.topbar-right{display:flex;align-items:center;gap:12px;}
.badge-live{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--success);background:rgba(104,211,145,0.1);border:1px solid rgba(104,211,145,0.25);padding:4px 10px;border-radius:50px;}
.badge-live::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--success);animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

.page-content{flex:1;padding:28px;overflow-y:auto;}

/* ── PANELS ── */
.panel{display:none;animation:fadeIn .25s ease;}
.panel.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

/* ── STATS GRID ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:28px;}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r2);padding:20px;transition:.2s;}
.stat-card:hover{border-color:rgba(99,179,237,0.2);box-shadow:var(--glow);}
.stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;margin-bottom:14px;}
.stat-icon.blue{background:rgba(99,179,237,0.12);color:var(--accent);}
.stat-icon.purple{background:rgba(159,122,234,0.12);color:var(--accent2);}
.stat-icon.green{background:rgba(104,211,145,0.12);color:var(--success);}
.stat-icon.yellow{background:rgba(246,224,94,0.12);color:var(--warning);}
.stat-value{font-family:'Rajdhani',sans-serif;font-size:28px;font-weight:700;color:var(--text);line-height:1;}
.stat-label{font-size:12px;color:var(--muted);margin-top:4px;}
.stat-sub{font-size:11px;color:var(--dim);margin-top:6px;}

/* ── SECTION HEADER ── */
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.section-title{font-family:'Rajdhani',sans-serif;font-size:19px;font-weight:700;display:flex;align-items:center;gap:8px;}
.section-title::before{content:'';width:4px;height:20px;background:linear-gradient(to bottom,var(--accent),var(--accent2));border-radius:2px;}

/* ── BTN ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:none;border-radius:var(--r);font-size:13.5px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:.2s;text-decoration:none;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;box-shadow:0 3px 15px rgba(99,179,237,0.25);}
.btn-primary:hover{box-shadow:0 5px 20px rgba(99,179,237,0.4);transform:translateY(-1px);}
.btn-secondary{background:var(--card2);border:1px solid var(--border2);color:var(--muted);}
.btn-secondary:hover{color:var(--text);border-color:var(--accent);}
.btn-danger{background:rgba(252,129,129,0.1);border:1px solid rgba(252,129,129,0.25);color:var(--danger);}
.btn-danger:hover{background:rgba(252,129,129,0.2);}
.btn-success{background:rgba(104,211,145,0.1);border:1px solid rgba(104,211,145,0.25);color:var(--success);}
.btn-success:hover{background:rgba(104,211,145,0.2);}
.btn-sm{padding:6px 12px;font-size:12px;}
.btn-xs{padding:4px 9px;font-size:11px;}

/* ── TABLE ── */
.table-wrap{background:var(--card);border:1px solid var(--border);border-radius:var(--r2);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
thead{background:var(--card2);}
th{padding:11px 16px;text-align:left;font-size:11.5px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);}
td{padding:12px 16px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,255,255,0.02);}
.td-muted{color:var(--muted);font-size:12px;}
.td-mono{font-family:monospace;font-size:12px;color:var(--accent);}

/* ── BADGE STATUS ── */
.status{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:50px;text-transform:uppercase;letter-spacing:.4px;}
.status.success{background:rgba(104,211,145,0.12);border:1px solid rgba(104,211,145,0.25);color:var(--success);}
.status.pending{background:rgba(246,224,94,0.12);border:1px solid rgba(246,224,94,0.25);color:var(--warning);}
.status.failed{background:rgba(252,129,129,0.12);border:1px solid rgba(252,129,129,0.25);color:var(--danger);}
.status.active{background:rgba(104,211,145,0.12);border:1px solid rgba(104,211,145,0.25);color:var(--success);}
.status.inactive{background:rgba(100,100,100,0.12);border:1px solid rgba(100,100,100,0.25);color:var(--dim);}

/* ── GAME THUMB ── */
.game-thumb{width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid var(--border);}
.game-info{display:flex;align-items:center;gap:12px;}
.game-info-text strong{display:block;font-size:14px;}
.game-info-text span{font-size:12px;color:var(--muted);}

/* ── FILTERS ── */
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
.filter-input{background:var(--card2);border:1px solid var(--border);border-radius:var(--r);padding:8px 14px;color:var(--text);font-size:13px;font-family:'Inter',sans-serif;outline:none;transition:.2s;}
.filter-input:focus{border-color:var(--accent);}
.filter-input::placeholder{color:var(--dim);}
.search-wrap{position:relative;flex:1;min-width:200px;}
.search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--dim);font-size:13px;}
.search-wrap input{padding-left:36px;}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:.3s;}
.modal-overlay.show{opacity:1;pointer-events:all;}
.modal{background:var(--card);border:1px solid var(--border2);border-radius:var(--r2);width:100%;max-width:560px;max-height:85vh;overflow-y:auto;transform:scale(.93) translateY(16px);transition:transform .3s cubic-bezier(.34,1.56,.64,1);}
.modal-overlay.show .modal{transform:scale(1) translateY(0);}
.modal-header{padding:22px 24px 0;display:flex;align-items:flex-start;justify-content:space-between;}
.modal-header h3{font-family:'Rajdhani',sans-serif;font-size:20px;font-weight:700;}
.modal-close{background:none;border:none;color:var(--muted);font-size:18px;cursor:pointer;padding:4px;transition:.2s;line-height:1;}
.modal-close:hover{color:var(--text);}
.modal-body{padding:20px 24px;}
.modal-footer{padding:0 24px 22px;display:flex;gap:10px;justify-content:flex-end;}

/* ── FORM ── */
.form-group{margin-bottom:16px;}
.form-group:last-child{margin-bottom:0;}
.form-group label{display:block;font-size:12.5px;font-weight:500;color:var(--muted);margin-bottom:6px;}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--r);color:var(--text);font-size:13.5px;font-family:'Inter',sans-serif;outline:none;transition:.2s;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--accent);background:rgba(99,179,237,0.04);box-shadow:0 0 0 3px rgba(99,179,237,0.1);}
.form-group textarea{resize:vertical;min-height:80px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.form-check{display:flex;align-items:center;gap:8px;cursor:pointer;}
.form-check input[type=checkbox]{width:16px;height:16px;accent-color:var(--accent);cursor:pointer;}
.form-check span{font-size:13px;color:var(--text);}
.form-hint{font-size:11.5px;color:var(--dim);margin-top:4px;}

/* ── PAGINATION ── */
.pagination{display:flex;align-items:center;gap:6px;justify-content:center;margin-top:20px;}
.page-btn{padding:6px 12px;background:var(--card2);border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:13px;cursor:pointer;transition:.2s;}
.page-btn:hover{border-color:var(--accent);color:var(--accent);}
.page-btn.active{background:var(--accent);border-color:var(--accent);color:#fff;}
.page-btn:disabled{opacity:.4;cursor:not-allowed;}

/* ── TOAST ── */
.toast{position:fixed;bottom:24px;right:24px;padding:12px 18px;border-radius:var(--r);font-size:13.5px;font-weight:500;z-index:9999;transform:translateY(20px);opacity:0;pointer-events:none;transition:.3s;display:flex;align-items:center;gap:8px;max-width:320px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast.success{background:rgba(104,211,145,.15);border:1px solid rgba(104,211,145,.3);color:var(--success);}
.toast.error{background:rgba(252,129,129,.15);border:1px solid rgba(252,129,129,.3);color:var(--danger);}

/* ── PRODUCT SUBPANEL ── */
.product-list{display:flex;flex-direction:column;gap:8px;margin-top:16px;}
.product-row{background:var(--bg2);border:1px solid var(--border);border-radius:var(--r);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
.product-row-info{flex:1;}
.product-row-amount{font-family:'Rajdhani',sans-serif;font-size:18px;font-weight:700;}
.product-row-price{font-size:13px;color:var(--accent);}
.product-row-meta{font-size:11px;color:var(--muted);margin-top:2px;}
.product-actions{display:flex;gap:6px;}

/* ── TABS ── */
.tabs{display:flex;gap:4px;background:var(--card2);border:1px solid var(--border);border-radius:var(--r);padding:4px;margin-bottom:20px;width:fit-content;}
.tab{padding:7px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;color:var(--muted);transition:.2s;border:none;background:none;font-family:'Inter',sans-serif;}
.tab.active{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;box-shadow:0 2px 12px rgba(99,179,237,.3);}
.tab:not(.active):hover{color:var(--text);}

/* ── MISC ── */
.empty{text-align:center;padding:48px 20px;color:var(--dim);}
.empty i{font-size:36px;margin-bottom:12px;opacity:.4;}
.empty p{font-size:14px;}
.tag{display:inline-flex;align-items:center;gap:4px;background:rgba(99,179,237,.1);border:1px solid rgba(99,179,237,.2);border-radius:50px;padding:2px 9px;font-size:11px;color:var(--accent);}
.tag.popular{background:rgba(246,173,85,.1);border-color:rgba(246,173,85,.25);color:#f6ad55;}
.actions-col{display:flex;gap:6px;flex-wrap:wrap;}
.price-cell{font-weight:600;color:var(--accent);font-family:'Rajdhani',sans-serif;font-size:15px;}
.device-id{font-family:monospace;font-size:11.5px;color:var(--muted);background:var(--bg2);border:1px solid var(--border);border-radius:5px;padding:2px 7px;}
.spinner{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

@media(max-width:900px){
  .sidebar{display:none;}
  .form-row{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- ═══════════ SIDEBAR ═══════════ -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-row">
            <div class="logo-icon"><i class="fas fa-bolt"></i></div>
            <div class="logo-text">NEXUS</div>
        </div>
    </div>
    <div class="sidebar-admin">
        <strong><?= htmlspecialchars($adminName) ?></strong>
        Administrator
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu Utama</div>
        <div class="nav-item active" data-panel="dashboard" onclick="switchPanel('dashboard',this)">
            <i class="fas fa-chart-line"></i> Dashboard
        </div>
        <div class="nav-section">Manajemen</div>
        <div class="nav-item" data-panel="games" onclick="switchPanel('games',this)">
            <i class="fas fa-gamepad"></i> Game & Produk
        </div>
        <div class="nav-item" data-panel="transactions" onclick="switchPanel('transactions',this)">
            <i class="fas fa-receipt"></i> Semua Transaksi
        </div>
        <div class="nav-section">Pengaturan</div>
        <div class="nav-item" data-panel="settings" onclick="switchPanel('settings',this)">
            <i class="fas fa-cog"></i> Pengaturan
        </div>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;margin-bottom:8px;">
            <i class="fas fa-external-link-alt"></i> Lihat Toko
        </a>
        <button class="btn-logout" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </button>
    </div>
</aside>

<!-- ═══════════ MAIN ═══════════ -->
<div class="content">
    <div class="topbar">
        <div class="topbar-title" id="topbar-title">Dashboard</div>
        <div class="topbar-right">
            <div class="badge-live">Live</div>
            <span style="font-size:12px;color:var(--muted)"><?= date('d M Y, H:i') ?></span>
        </div>
    </div>

    <div class="page-content">

        <!-- ══ DASHBOARD PANEL ══ -->
        <div class="panel active" id="panel-dashboard">
            <div class="stats-grid" id="stats-grid">
                <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-receipt"></i></div><div class="stat-value" id="st-total-txn">–</div><div class="stat-label">Total Transaksi</div></div>
                <div class="stat-card"><div class="stat-icon green"><i class="fas fa-coins"></i></div><div class="stat-value" id="st-revenue">–</div><div class="stat-label">Total Revenue</div></div>
                <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-mobile-screen-button"></i></div><div class="stat-value" id="st-devices">–</div><div class="stat-label">Unique Devices</div></div>
                <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-gamepad"></i></div><div class="stat-value" id="st-games">–</div><div class="stat-label">Game Aktif</div></div>
            </div>

            <div class="section-header" style="margin-bottom:14px;">
                <div class="section-title">Transaksi Terbaru</div>
                <button class="btn btn-secondary btn-sm" onclick="switchPanel('transactions',document.querySelector('[data-panel=transactions]'))">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>ID</th><th>Game</th><th>User ID</th><th>Device</th><th>Total</th><th>Status</th><th>Waktu</th>
                    </tr></thead>
                    <tbody id="recent-txns"><tr><td colspan="7" class="empty"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- ══ GAMES & PRODUCTS PANEL ══ -->
        <div class="panel" id="panel-games">
            <div class="tabs">
                <button class="tab active" onclick="switchGameTab('game-list',this)">Daftar Game</button>
                <button class="tab" onclick="switchGameTab('product-manager',this)">Kelola Produk</button>
            </div>

            <!-- Game List Tab -->
            <div id="game-list">
                <div class="section-header">
                    <div class="section-title">Semua Game</div>
                    <button class="btn btn-primary" onclick="openAddGameModal()">
                        <i class="fas fa-plus"></i> Tambah Game
                    </button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Game</th><th>Kategori</th><th>Mata Uang</th><th>Produk</th><th>Status</th><th>Aksi</th>
                        </tr></thead>
                        <tbody id="games-table-body"><tr><td colspan="6" class="empty"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
                    </table>
                </div>
            </div>

            <!-- Product Manager Tab -->
            <div id="product-manager" style="display:none;">
                <div class="section-header">
                    <div class="section-title">Kelola Produk</div>
                </div>
                <div class="form-group" style="max-width:320px;margin-bottom:20px;">
                    <label>Pilih Game</label>
                    <select id="pm-game-select" class="filter-input" style="width:100%;" onchange="loadProductsFor(this.value)">
                        <option value="">— Pilih Game —</option>
                    </select>
                </div>
                <div id="pm-products-area">
                    <div class="empty"><i class="fas fa-boxes-stacked"></i><p>Pilih game untuk melihat produk</p></div>
                </div>
            </div>
        </div>

        <!-- ══ TRANSACTIONS PANEL ══ -->
        <div class="panel" id="panel-transactions">
            <div class="section-header">
                <div class="section-title">Semua Transaksi</div>
                <div id="txn-count-badge" style="font-size:13px;color:var(--muted);"></div>
            </div>
            <div class="filters">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" class="filter-input" id="txn-search" placeholder="Cari ID, User ID, WhatsApp, Device..." style="width:100%;" oninput="debounceLoadTxns()">
                </div>
                <select class="filter-input" id="txn-game-filter" onchange="loadTransactions(1)">
                    <option value="">Semua Game</option>
                </select>
                <select class="filter-input" id="txn-status-filter" onchange="loadTransactions(1)">
                    <option value="">Semua Status</option>
                    <option value="success">Berhasil</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Gagal</option>
                </select>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>ID Transaksi</th><th>Device</th><th>Game</th><th>Item</th><th>User ID</th><th>WhatsApp</th><th>Total</th><th>Status</th><th>Waktu</th><th>Aksi</th>
                    </tr></thead>
                    <tbody id="txn-table-body"><tr><td colspan="10" class="empty"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
                </table>
            </div>
            <div id="txn-pagination" class="pagination"></div>
        </div>

        <!-- ══ SETTINGS PANEL ══ -->
        <div class="panel" id="panel-settings">
            <div class="section-title" style="margin-bottom:22px;">Pengaturan</div>
            <div style="max-width:480px;">
                <div class="table-wrap" style="padding:22px;margin-bottom:18px;">
                    <h3 style="font-family:'Rajdhani',sans-serif;font-size:17px;margin-bottom:18px;"><i class="fas fa-lock" style="color:var(--accent);margin-right:6px;"></i>Ganti Password Admin</h3>
                    <div class="form-group">
                        <label>Password Lama</label>
                        <input type="password" id="old-pw" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" id="new-pw" placeholder="Minimal 6 karakter">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" id="confirm-pw" placeholder="Ulangi password baru">
                    </div>
                    <button class="btn btn-primary" onclick="changePassword()">
                        <i class="fas fa-save"></i> Simpan Password
                    </button>
                </div>

                <div class="table-wrap" style="padding:22px;">
                    <h3 style="font-family:'Rajdhani',sans-serif;font-size:17px;margin-bottom:14px;"><i class="fas fa-info-circle" style="color:var(--accent);margin-right:6px;"></i>Info Sistem</h3>
                    <div style="font-size:13px;color:var(--muted);line-height:2;">
                        <div>Versi: <span style="color:var(--text)">NexusTopup v2.0.0</span></div>
                        <div>Storage: <span style="color:var(--text)">JSON File (No Database)</span></div>
                        <div>PHP: <span style="color:var(--text)"><?= PHP_VERSION ?></span></div>
                        <div>Server: <span style="color:var(--text)"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Local' ?></span></div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /page-content -->
</div><!-- /content -->

<!-- ════════ MODALS ════════ -->

<!-- Add/Edit Game Modal -->
<div class="modal-overlay" id="modal-game">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-game-title">Tambah Game</h3>
            <button class="modal-close" onclick="closeModal('modal-game')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="game-edit-id">
            <div class="form-group">
                <label>Nama Game *</label>
                <input type="text" id="game-name" placeholder="Contoh: Mobile Legends">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Kategori *</label>
                    <select id="game-category">
                        <option>MOBA</option><option>Battle Royale</option><option>FPS</option>
                        <option>RPG</option><option>Strategy</option><option>Sports</option><option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mata Uang *</label>
                    <input type="text" id="game-currency" placeholder="Diamond, UC, VP, Gems...">
                </div>
            </div>
            <div class="form-group">
                <label>Gambar / Cover</label>
                <input type="file" id="game-image" accept="image/*" style="padding:8px;">
                <div class="form-hint">Format: JPG, PNG, WEBP. Gambar lama tetap dipakai jika tidak diupload baru.</div>
            </div>
            <div style="display:flex;gap:20px;margin-top:4px;">
                <label class="form-check">
                    <input type="checkbox" id="game-needs-server"> <span>Butuh Server ID / Zone ID</span>
                </label>
                <label class="form-check">
                    <input type="checkbox" id="game-popular"> <span>Tandai sebagai Populer</span>
                </label>
                <label class="form-check" id="game-active-wrap" style="display:none;">
                    <input type="checkbox" id="game-active" checked> <span>Aktif</span>
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-game')">Batal</button>
            <button class="btn btn-primary" id="save-game-btn" onclick="saveGame()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal-overlay" id="modal-product">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-product-title">Tambah Produk</h3>
            <button class="modal-close" onclick="closeModal('modal-product')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="prod-edit-id">
            <input type="hidden" id="prod-game-id">
            <div class="form-row">
                <div class="form-group">
                    <label>Jumlah *</label>
                    <input type="text" id="prod-amount" placeholder="86, 172, 1000...">
                </div>
                <div class="form-group">
                    <label>Bonus</label>
                    <input type="text" id="prod-bonus" placeholder="+10, +92 (kosongkan jika tidak ada)">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Harga Normal (Rp) *</label>
                    <input type="number" id="prod-price" placeholder="20000" min="0">
                </div>
                <div class="form-group">
                    <label>Diskon (%)</label>
                    <input type="number" id="prod-discount" placeholder="0" min="0" max="90" value="0">
                </div>
            </div>
            <div style="display:flex;gap:20px;margin-top:4px;">
                <label class="form-check">
                    <input type="checkbox" id="prod-popular"> <span>Terlaris / Populer</span>
                </label>
                <label class="form-check" id="prod-active-wrap" style="display:none;">
                    <input type="checkbox" id="prod-active" checked> <span>Aktif</span>
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-product')">Batal</button>
            <button class="btn btn-primary" id="save-product-btn" onclick="saveProduct()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- Edit Transaction Status Modal -->
<div class="modal-overlay" id="modal-txn">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3>Update Status Transaksi</h3>
            <button class="modal-close" onclick="closeModal('modal-txn')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="txn-edit-id">
            <div class="form-group">
                <label>ID Transaksi</label>
                <input type="text" id="txn-edit-id-display" readonly style="opacity:.6;">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="txn-edit-status">
                    <option value="success">Berhasil</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Gagal</option>
                </select>
            </div>
            <div class="form-group">
                <label>Catatan Admin (opsional)</label>
                <textarea id="txn-edit-notes" placeholder="Catatan internal untuk transaksi ini..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-txn')">Batal</button>
            <button class="btn btn-primary" onclick="saveTxnStatus()"><i class="fas fa-save"></i> Update</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
// ══════════════════════════════════════════════
// ADMIN PANEL JAVASCRIPT
// ══════════════════════════════════════════════

const API = 'api.php';
let gamesCache = [];
let txnPage = 1;
let txnDebounce = null;

// ── UTILS ────────────────────────────────────
function toast(msg, type='success') {
    const el = document.getElementById('toast');
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    el.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
    el.className = `toast ${type} show`;
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.remove('show'), 3500);
}

function fmt(n) { return 'Rp ' + parseInt(n).toLocaleString('id-ID'); }

function fmtDate(d) {
    return new Intl.DateTimeFormat('id-ID', {
        day:'numeric', month:'short', year:'numeric',
        hour:'2-digit', minute:'2-digit'
    }).format(new Date(d));
}

function post(endpoint, data) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    return fetch(endpoint, { method:'POST', body: fd }).then(r => r.json());
}

function postFile(endpoint, formData) {
    return fetch(endpoint, { method:'POST', body: formData }).then(r => r.json());
}

function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

// ── NAVIGATION ───────────────────────────────
function switchPanel(name, el) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    if (el) el.classList.add('active');
    document.getElementById('topbar-title').textContent = el ? el.textContent.trim() : name;

    if (name === 'dashboard')    loadDashboard();
    if (name === 'games')       loadGames();
    if (name === 'transactions') { populateTxnGameFilter(); loadTransactions(1); }
}

function switchGameTab(tab, el) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('game-list').style.display = tab === 'game-list' ? 'block' : 'none';
    document.getElementById('product-manager').style.display = tab === 'product-manager' ? 'block' : 'none';
    if (tab === 'product-manager') populatePmSelect();
}

// ── DASHBOARD ────────────────────────────────
async function loadDashboard() {
    const r = await post(API, { action: 'get_stats' });
    if (r.success) {
        const s = r.stats;
        document.getElementById('st-total-txn').textContent = s.total_transactions.toLocaleString();
        document.getElementById('st-revenue').textContent = (s.total_revenue / 1000000).toFixed(1) + 'jt';
        document.getElementById('st-devices').textContent = s.total_devices.toLocaleString();
        document.getElementById('st-games').textContent = s.total_games;
    }
    // Recent txns
    const rt = await post(API, { action: 'get_transactions', page: 1 });
    if (rt.success) renderRecentTxns(rt.transactions.slice(0, 8));
}

function renderRecentTxns(txns) {
    const tbody = document.getElementById('recent-txns');
    if (!txns.length) { tbody.innerHTML = '<tr><td colspan="7" class="empty"><i class="fas fa-inbox"></i><p>Belum ada transaksi</p></td></tr>'; return; }
    tbody.innerHTML = txns.map(t => `
    <tr>
        <td class="td-mono">${t.id}</td>
        <td>${t.game_name}</td>
        <td>${t.user_id}</td>
        <td><span class="device-id">${t.device_id?.slice(0,10)}...</span></td>
        <td class="price-cell">${fmt(t.total)}</td>
        <td><span class="status ${t.status}">${statusLabel(t.status)}</span></td>
        <td class="td-muted">${fmtDate(t.date)}</td>
    </tr>`).join('');
}

// ── GAMES ────────────────────────────────────
async function loadGames() {
    const r = await post(API, { action: 'get_games' });
    if (!r.success) return;
    gamesCache = r.games;
    renderGamesTable(r.games);
    populatePmSelect();
    populateTxnGameFilter();
}

function renderGamesTable(games) {
    const tbody = document.getElementById('games-table-body');
    if (!games.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty"><i class="fas fa-gamepad"></i><p>Belum ada game</p></td></tr>'; return; }
    tbody.innerHTML = games.map(g => `
    <tr>
        <td>
            <div class="game-info">
                <img src="../${g.image}" class="game-thumb" onerror="this.src='../assets/images/moba.webp'">
                <div class="game-info-text">
                    <strong>${g.name}</strong>
                    <span>${g.currency}</span>
                </div>
            </div>
        </td>
        <td>${g.category} ${g.popular ? '<span class="tag popular"><i class="fas fa-star"></i> Populer</span>' : ''}</td>
        <td>${g.currency}${g.needs_server_id ? ' <span class="tag"><i class="fas fa-server"></i> Server ID</span>' : ''}</td>
        <td>${(g.products || []).filter(p => p.active !== false).length} aktif / ${(g.products || []).length} total</td>
        <td><span class="status ${g.active !== false ? 'active' : 'inactive'}">${g.active !== false ? 'Aktif' : 'Nonaktif'}</span></td>
        <td>
            <div class="actions-col">
                <button class="btn btn-secondary btn-xs" onclick='openEditGameModal(${JSON.stringify(g)})'><i class="fas fa-pen"></i></button>
                <button class="btn btn-xs ${g.active !== false ? 'btn-danger' : 'btn-success'}" onclick="toggleGame('${g.id}', ${g.active !== false ? 'false' : 'true'})">
                    ${g.active !== false ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>'}
                </button>
                <button class="btn btn-danger btn-xs" onclick="deleteGame('${g.id}','${g.name}')"><i class="fas fa-trash"></i></button>
            </div>
        </td>
    </tr>`).join('');
}

function openAddGameModal() {
    document.getElementById('modal-game-title').textContent = 'Tambah Game Baru';
    document.getElementById('game-edit-id').value = '';
    document.getElementById('game-name').value = '';
    document.getElementById('game-currency').value = '';
    document.getElementById('game-needs-server').checked = false;
    document.getElementById('game-popular').checked = false;
    document.getElementById('game-active-wrap').style.display = 'none';
    document.getElementById('game-image').value = '';
    openModal('modal-game');
}

function openEditGameModal(g) {
    document.getElementById('modal-game-title').textContent = 'Edit Game';
    document.getElementById('game-edit-id').value = g.id;
    document.getElementById('game-name').value = g.name;
    document.getElementById('game-category').value = g.category;
    document.getElementById('game-currency').value = g.currency;
    document.getElementById('game-needs-server').checked = g.needs_server_id;
    document.getElementById('game-popular').checked = g.popular;
    document.getElementById('game-active').checked = g.active !== false;
    document.getElementById('game-active-wrap').style.display = 'flex';
    document.getElementById('game-image').value = '';
    openModal('modal-game');
}

async function saveGame() {
    const btn = document.getElementById('save-game-btn');
    const editId = document.getElementById('game-edit-id').value;
    const fd = new FormData();
    fd.append('action', editId ? 'update_game' : 'add_game');
    if (editId) fd.append('id', editId);
    fd.append('name',            document.getElementById('game-name').value.trim());
    fd.append('category',        document.getElementById('game-category').value);
    fd.append('currency',        document.getElementById('game-currency').value.trim());
    fd.append('needs_server_id', document.getElementById('game-needs-server').checked ? '1' : '0');
    fd.append('popular',         document.getElementById('game-popular').checked ? '1' : '0');
    if (editId) fd.append('active', document.getElementById('game-active').checked ? '1' : '0');
    const imgFile = document.getElementById('game-image').files[0];
    if (imgFile) fd.append('image', imgFile);

    btn.innerHTML = '<span class="spinner"></span>';
    btn.disabled = true;
    const r = await postFile(API, fd);
    btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
    btn.disabled = false;

    if (r.success) {
        toast(editId ? 'Game berhasil diupdate!' : 'Game berhasil ditambah!');
        closeModal('modal-game');
        loadGames();
    } else {
        toast(r.message || 'Gagal menyimpan', 'error');
    }
}

async function toggleGame(id, active) {
    const r = await post(API, { action: 'toggle_game', id, active });
    if (r.success) { toast('Status game diupdate!'); loadGames(); }
    else toast(r.message, 'error');
}

async function deleteGame(id, name) {
    if (!confirm(`Hapus game "${name}"? Semua produknya akan ikut terhapus!`)) return;
    const r = await post(API, { action: 'delete_game', id });
    if (r.success) { toast('Game berhasil dihapus!'); loadGames(); }
    else toast(r.message, 'error');
}

// ── PRODUCTS ─────────────────────────────────
function populatePmSelect() {
    const sel = document.getElementById('pm-game-select');
    const cur = sel.value;
    sel.innerHTML = '<option value="">— Pilih Game —</option>' +
        gamesCache.map(g => `<option value="${g.id}" ${g.id===cur?'selected':''}>${g.name}</option>`).join('');
}

async function loadProductsFor(gameId) {
    const area = document.getElementById('pm-products-area');
    if (!gameId) { area.innerHTML = '<div class="empty"><i class="fas fa-boxes-stacked"></i><p>Pilih game untuk melihat produk</p></div>'; return; }
    const game = gamesCache.find(g => g.id === gameId);
    if (!game) return;

    const products = game.products || [];
    let html = `
    <div class="section-header">
        <span style="color:var(--muted);font-size:13px;">${products.length} produk untuk <strong style="color:var(--text)">${game.name}</strong></span>
        <button class="btn btn-primary btn-sm" onclick="openAddProductModal('${game.id}','${game.currency}')">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>`;

    if (!products.length) {
        html += '<div class="empty"><i class="fas fa-box-open"></i><p>Belum ada produk. Tambah sekarang!</p></div>';
    } else {
        html += '<div class="table-wrap"><table><thead><tr><th>Jumlah</th><th>Bonus</th><th>Harga Normal</th><th>Diskon</th><th>Harga Final</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
        html += products.map(p => {
            const final = p.discount ? Math.round(p.price - (p.price * p.discount / 100)) : p.price;
            return `<tr>
                <td><strong>${p.amount} ${game.currency}</strong>${p.popular ? ' <span class="tag popular"><i class="fas fa-star"></i></span>' : ''}</td>
                <td>${p.bonus || '<span class="td-muted">—</span>'}</td>
                <td>${p.discount ? `<span style="text-decoration:line-through;color:var(--muted)">${fmt(p.price)}</span>` : fmt(p.price)}</td>
                <td>${p.discount ? `<span style="color:var(--success)">${p.discount}%</span>` : '<span class="td-muted">—</span>'}</td>
                <td class="price-cell">${fmt(final)}</td>
                <td><span class="status ${p.active !== false ? 'active' : 'inactive'}">${p.active !== false ? 'Aktif' : 'Nonaktif'}</span></td>
                <td>
                    <div class="actions-col">
                        <button class="btn btn-secondary btn-xs" onclick='openEditProductModal("${gameId}","${game.currency}",${JSON.stringify(p)})'><i class="fas fa-pen"></i></button>
                        <button class="btn btn-danger btn-xs" onclick="deleteProduct('${gameId}','${p.id}')"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');
        html += '</tbody></table></div>';
    }
    area.innerHTML = html;
}

function openAddProductModal(gameId, currency) {
    document.getElementById('modal-product-title').textContent = `Tambah Produk – ${currency}`;
    document.getElementById('prod-edit-id').value = '';
    document.getElementById('prod-game-id').value = gameId;
    document.getElementById('prod-amount').value = '';
    document.getElementById('prod-bonus').value = '';
    document.getElementById('prod-price').value = '';
    document.getElementById('prod-discount').value = '0';
    document.getElementById('prod-popular').checked = false;
    document.getElementById('prod-active-wrap').style.display = 'none';
    openModal('modal-product');
}

function openEditProductModal(gameId, currency, p) {
    document.getElementById('modal-product-title').textContent = `Edit Produk – ${currency}`;
    document.getElementById('prod-edit-id').value = p.id;
    document.getElementById('prod-game-id').value = gameId;
    document.getElementById('prod-amount').value = p.amount;
    document.getElementById('prod-bonus').value = p.bonus || '';
    document.getElementById('prod-price').value = p.price;
    document.getElementById('prod-discount').value = p.discount || 0;
    document.getElementById('prod-popular').checked = p.popular || false;
    document.getElementById('prod-active').checked = p.active !== false;
    document.getElementById('prod-active-wrap').style.display = 'flex';
    openModal('modal-product');
}

async function saveProduct() {
    const btn = document.getElementById('save-product-btn');
    const editId = document.getElementById('prod-edit-id').value;
    const gameId = document.getElementById('prod-game-id').value;
    const data = {
        action:    editId ? 'update_product' : 'add_product',
        game_id:   gameId,
        amount:    document.getElementById('prod-amount').value.trim(),
        bonus:     document.getElementById('prod-bonus').value.trim(),
        price:     document.getElementById('prod-price').value,
        discount:  document.getElementById('prod-discount').value,
        popular:   document.getElementById('prod-popular').checked ? '1' : '0',
    };
    if (editId) { data.product_id = editId; data.active = document.getElementById('prod-active').checked ? '1' : '0'; }

    btn.innerHTML = '<span class="spinner"></span>';
    btn.disabled = true;
    const r = await post(API, data);
    btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
    btn.disabled = false;

    if (r.success) {
        toast(editId ? 'Produk diupdate!' : 'Produk ditambah!');
        closeModal('modal-product');
        await loadGames();
        loadProductsFor(gameId);
    } else {
        toast(r.message || 'Gagal', 'error');
    }
}

async function deleteProduct(gameId, productId) {
    if (!confirm('Hapus produk ini?')) return;
    const r = await post(API, { action: 'delete_product', game_id: gameId, product_id: productId });
    if (r.success) { toast('Produk dihapus!'); await loadGames(); loadProductsFor(gameId); }
    else toast(r.message, 'error');
}

// ── TRANSACTIONS ─────────────────────────────
function populateTxnGameFilter() {
    const sel = document.getElementById('txn-game-filter');
    if (!sel) return;
    const cur = sel.value;
    sel.innerHTML = '<option value="">Semua Game</option>' +
        gamesCache.map(g => `<option value="${g.id}" ${g.id===cur?'selected':''}>${g.name}</option>`).join('');
}

function debounceLoadTxns() {
    clearTimeout(txnDebounce);
    txnDebounce = setTimeout(() => loadTransactions(1), 400);
}

async function loadTransactions(page = 1) {
    txnPage = page;
    const r = await post(API, {
        action: 'get_transactions',
        page,
        search: document.getElementById('txn-search')?.value || '',
        game:   document.getElementById('txn-game-filter')?.value || '',
        status: document.getElementById('txn-status-filter')?.value || '',
    });
    if (!r.success) return;
    document.getElementById('txn-count-badge').textContent = `${r.total} transaksi ditemukan`;
    renderTxnTable(r.transactions);
    renderPagination(r.page, r.pages);
}

function renderTxnTable(txns) {
    const tbody = document.getElementById('txn-table-body');
    if (!txns.length) {
        tbody.innerHTML = '<tr><td colspan="10" class="empty"><i class="fas fa-inbox"></i><p>Tidak ada transaksi</p></td></tr>';
        return;
    }
    tbody.innerHTML = txns.map(t => `
    <tr>
        <td class="td-mono" style="font-size:11px;">${t.id}</td>
        <td><span class="device-id">${(t.device_id||'').slice(0,14)}</span></td>
        <td>${t.game_name}</td>
        <td style="font-size:12px;">${t.item}</td>
        <td>${t.user_id}${t.server_id ? `<br><span class="td-muted">Server: ${t.server_id}</span>` : ''}</td>
        <td>${t.whatsapp}${t.email ? `<br><span class="td-muted">${t.email}</span>` : ''}</td>
        <td class="price-cell">${fmt(t.total)}</td>
        <td><span class="status ${t.status}">${statusLabel(t.status)}</span>${t.notes ? `<br><span class="td-muted" style="font-size:11px;">${t.notes}</span>` : ''}</td>
        <td class="td-muted" style="font-size:11px;">${fmtDate(t.date)}</td>
        <td><button class="btn btn-secondary btn-xs" onclick='openTxnModal(${JSON.stringify(t)})'><i class="fas fa-pen"></i></button></td>
    </tr>`).join('');
}

function renderPagination(cur, total) {
    const el = document.getElementById('txn-pagination');
    if (total <= 1) { el.innerHTML = ''; return; }
    let html = `<button class="page-btn" onclick="loadTransactions(${cur-1})" ${cur===1?'disabled':''}>‹</button>`;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= cur-1 && i <= cur+1)) {
            html += `<button class="page-btn ${i===cur?'active':''}" onclick="loadTransactions(${i})">${i}</button>`;
        } else if (i === cur-2 || i === cur+2) {
            html += '<span style="color:var(--dim);padding:0 4px;">•••</span>';
        }
    }
    html += `<button class="page-btn" onclick="loadTransactions(${cur+1})" ${cur===total?'disabled':''}>›</button>`;
    el.innerHTML = html;
}

function openTxnModal(t) {
    document.getElementById('txn-edit-id').value = t.id;
    document.getElementById('txn-edit-id-display').value = t.id;
    document.getElementById('txn-edit-status').value = t.status;
    document.getElementById('txn-edit-notes').value = t.notes || '';
    openModal('modal-txn');
}

async function saveTxnStatus() {
    const r = await post(API, {
        action: 'update_txn_status',
        id:     document.getElementById('txn-edit-id').value,
        status: document.getElementById('txn-edit-status').value,
        notes:  document.getElementById('txn-edit-notes').value,
    });
    if (r.success) { toast('Status transaksi diupdate!'); closeModal('modal-txn'); loadTransactions(txnPage); }
    else toast(r.message, 'error');
}

function statusLabel(s) {
    return s === 'success' ? 'Berhasil' : s === 'pending' ? 'Pending' : 'Gagal';
}

// ── SETTINGS ─────────────────────────────────
async function changePassword() {
    const old = document.getElementById('old-pw').value;
    const nw  = document.getElementById('new-pw').value;
    const cf  = document.getElementById('confirm-pw').value;
    if (!old || !nw) { toast('Semua field wajib diisi', 'error'); return; }
    if (nw !== cf) { toast('Password baru tidak cocok', 'error'); return; }
    const r = await post(API, { action: 'change_password', old_password: old, new_password: nw });
    if (r.success) { toast('Password berhasil diubah!'); document.getElementById('old-pw').value=''; document.getElementById('new-pw').value=''; document.getElementById('confirm-pw').value=''; }
    else toast(r.message, 'error');
}

function confirmLogout() {
    if (confirm('Keluar dari panel admin?')) window.location.href = 'logout.php';
}

// Close modal on backdrop click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeModal(overlay.id);
    });
});

// ── INIT ─────────────────────────────────────
loadDashboard();
loadGames();
</script>
</body>
</html>
