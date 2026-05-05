// =============================================
// NEXUS TOPUP - Main App JS
// Device ID system + JSON API integration
// =============================================

// ── DEVICE ID ─────────────────────────────────────────
const DEVICE_KEY = 'nx_device_id';

function generateDeviceId() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let id = 'DEV';
    for (let i = 0; i < 12; i++) id += chars[Math.floor(Math.random() * chars.length)];
    return id;
}

function getDeviceId() {
    let id = localStorage.getItem(DEVICE_KEY);
    if (!id || !/^DEV[A-Z0-9]{12}$/.test(id)) {
        id = generateDeviceId();
        localStorage.setItem(DEVICE_KEY, id);
    }
    return id;
}

const DEVICE_ID = getDeviceId();

// ── STATE ─────────────────────────────────────────────
const State = {
    page: 'home',
    view: 'games',
    selectedGame: null,         // { id, name, currency, needs_server_id }
    selectedProduct: null,      // full product object
    selectedPayment: null,      // payment id string
    productsData: null,         // { game_name, currency, needs_server_id, products[] }
    gamesCache: [],
};

// ── UTILS ─────────────────────────────────────────────
function formatPrice(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}

function formatDate(dateStr) {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(new Date(dateStr));
}

function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    el.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
    el.className = `toast ${type} show`;
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.remove('show'), 3200);
}

async function apiPost(data) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    const r = await fetch(API_URL, { method: 'POST', body: fd });
    return r.json();
}

// ── NAVIGATION ────────────────────────────────────────
function navigateTo(page) {
    State.page = page;
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(`page-${page}`).classList.add('active');
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(`nav-${page}`).classList.add('active');

    if (page === 'home') showView('games');
    if (page === 'history') loadHistory();
}

function showView(view) {
    State.view = view;
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.getElementById(`view-${view}`).classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── GAMES ─────────────────────────────────────────────
async function loadGames() {
    try {
        const r = await apiPost({ action: 'get_games' });
        if (!r.success) { renderGamesError(); return; }
        State.gamesCache = r.games;
        document.getElementById('hero-game-count').textContent = r.games.length;
        renderGames(r.games);
    } catch {
        renderGamesError();
    }
}

function renderGames(games) {
    const grid = document.getElementById('games-grid');
    if (!games.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:48px 20px;color:var(--text-muted);">
            <i class="fas fa-gamepad" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px;"></i>
            Belum ada game tersedia
        </div>`;
        return;
    }
    grid.innerHTML = games.map(g => `
        <div class="game-card" onclick="selectGame('${g.id}')">
            <img src="${g.image}" alt="${g.name}" loading="lazy" onerror="this.src='assets/images/moba.webp'">
            <div class="game-card-overlay"></div>
            ${g.popular ? `<div class="game-card-badge"><i class="fas fa-bolt"></i> Populer</div>` : ''}
            <div class="game-card-info">
                <div class="game-card-category">${g.category}</div>
                <div class="game-card-name">${g.name}</div>
                <button class="game-card-btn"><i class="fas fa-coins"></i> Top Up Sekarang</button>
            </div>
        </div>
    `).join('');
}

function renderGamesError() {
    document.getElementById('games-grid').innerHTML = `
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);">
            <i class="fas fa-exclamation-triangle" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px;"></i>
            Gagal memuat game. <button onclick="loadGames()" style="background:none;border:none;color:var(--accent);cursor:pointer;font-size:14px;">Coba lagi</button>
        </div>`;
}

// ── SELECT GAME → PRODUCTS ────────────────────────────
async function selectGame(gameId) {
    const game = State.gamesCache.find(g => g.id === gameId);
    if (!game) return;

    // show loading state
    document.getElementById('product-game-name').textContent = game.name;
    document.getElementById('product-game-sub').textContent = 'Memuat paket...';
    document.getElementById('products-grid').innerHTML = `
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);">
            <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
        </div>`;
    showView('products');

    try {
        const r = await apiPost({ action: 'get_products', game_id: gameId });
        if (!r.success) { toast('Gagal memuat produk', 'error'); return; }

        State.selectedGame = {
            id: gameId,
            name: r.game_name,
            currency: r.currency,
            needs_server_id: r.needs_server_id,
        };
        State.productsData = r;

        document.getElementById('product-game-sub').textContent = `Pilih paket ${r.currency}`;
        renderProducts(r);
    } catch {
        toast('Gagal memuat produk', 'error');
        backToGames();
    }
}

function renderProducts(data) {
    const grid = document.getElementById('products-grid');
    if (!data.products.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:48px 20px;color:var(--text-muted);">
            Belum ada produk tersedia untuk game ini.
        </div>`;
        return;
    }
    grid.innerHTML = data.products.map(p => {
        const finalPrice = p.discount
            ? Math.round(p.price - (p.price * p.discount / 100))
            : p.price;
        return `
        <div class="product-card" id="prod-${p.id}" onclick="selectProduct('${p.id}')">
            ${p.popular ? `<span class="product-badge popular"><i class="fas fa-star"></i> Terlaris</span>` : ''}
            ${p.discount ? `<span class="product-badge discount"><i class="fas fa-tag"></i> Hemat ${p.discount}%</span>` : ''}
            <div class="product-amount-val">${p.amount}</div>
            ${p.bonus ? `<div class="product-bonus">${p.bonus} Bonus</div>` : ''}
            <div class="product-currency">${data.currency}</div>
            ${p.discount ? `<div class="product-price-original">${formatPrice(p.price)}</div>` : ''}
            <div class="product-price-final">${formatPrice(finalPrice)}</div>
            <button class="product-select-btn">Pilih Paket</button>
        </div>`;
    }).join('');
}

function selectProduct(productId) {
    const product = State.productsData?.products.find(p => p.id === productId);
    if (!product) return;

    State.selectedProduct = product;
    State.selectedPayment = null;

    document.querySelectorAll('.product-card').forEach(c => c.classList.remove('selected'));
    document.getElementById(`prod-${productId}`)?.classList.add('selected');

    renderOrderForm();
    showView('order');
}

function backToGames() {
    State.selectedGame = null;
    State.selectedProduct = null;
    State.selectedPayment = null;
    showView('games');
}

function backToProducts() {
    State.selectedProduct = null;
    State.selectedPayment = null;
    showView('products');
}

// ── ORDER FORM ────────────────────────────────────────
function renderOrderForm() {
    const game = State.selectedGame;
    const product = State.selectedProduct;
    const data = State.productsData;
    if (!game || !product || !data) return;

    const finalPrice = product.discount
        ? Math.round(product.price - (product.price * product.discount / 100))
        : product.price;
    const itemLabel = product.amount + ' ' + data.currency + (product.bonus ? ' ' + product.bonus : '');

    document.getElementById('order-game-name').textContent = game.name;
    document.getElementById('order-game-sub').textContent = itemLabel;

    // Server ID toggle
    const serverGroup = document.getElementById('server-id-group');
    const serverInput = document.getElementById('server-id');
    if (data.needs_server_id) {
        serverGroup.classList.remove('hidden');
        serverInput.required = true;
    } else {
        serverGroup.classList.add('hidden');
        serverInput.required = false;
        serverInput.value = '';
    }

    // Summary
    document.getElementById('sum-game').textContent = game.name;
    document.getElementById('sum-item').textContent = itemLabel;
    document.getElementById('sum-price').textContent = formatPrice(finalPrice);
    document.getElementById('sum-total').textContent = formatPrice(finalPrice);
    document.getElementById('sum-fee-row').classList.add('hidden');

    renderPaymentMethods(finalPrice);
    validateForm();
}

function renderPaymentMethods(basePrice) {
    const list = document.getElementById('payment-list');
    list.innerHTML = PAYMENTS_DATA.map(pm => `
        <div class="payment-item" id="pm-${pm.id}" onclick="selectPayment('${pm.id}', ${basePrice})">
            <div class="payment-item-left">
                <div class="payment-radio"></div>
                <div class="payment-icon"><i class="fas ${pm.icon}"></i></div>
                <span class="payment-name">${pm.name}</span>
            </div>
            ${pm.fee > 0
                ? `<span class="payment-fee">+${formatPrice(pm.fee)}</span>`
                : `<span style="font-size:12px;color:var(--neon-green);font-weight:600;">Gratis</span>`}
        </div>
    `).join('');
}

function selectPayment(pmId, basePrice) {
    State.selectedPayment = pmId;
    const pm = PAYMENTS_DATA.find(p => p.id === pmId);
    if (!pm) return;

    document.querySelectorAll('.payment-item').forEach(el => el.classList.remove('selected'));
    document.getElementById(`pm-${pmId}`)?.classList.add('selected');

    const feeRow = document.getElementById('sum-fee-row');
    if (pm.fee > 0) {
        feeRow.classList.remove('hidden');
        document.getElementById('sum-fee').textContent = formatPrice(pm.fee);
    } else {
        feeRow.classList.add('hidden');
    }
    document.getElementById('sum-total').textContent = formatPrice(basePrice + pm.fee);
    validateForm();
}

function validateForm() {
    const userId = document.getElementById('user-id').value.trim();
    const wa = document.getElementById('whatsapp').value.trim();
    const serverId = document.getElementById('server-id').value.trim();
    const needsServer = State.productsData?.needs_server_id;
    const valid = userId && wa && State.selectedPayment && (!needsServer || serverId);
    document.getElementById('submit-order-btn').disabled = !valid;
}

// ── CONFIRM DIALOG ────────────────────────────────────
function showConfirmDialog() {
    const userId = document.getElementById('user-id').value.trim();
    const wa = document.getElementById('whatsapp').value.trim();
    const serverId = document.getElementById('server-id').value.trim();
    const email = document.getElementById('order-email').value.trim();
    const needsServer = State.productsData?.needs_server_id;

    if (!userId || !wa || !State.selectedPayment || (needsServer && !serverId)) {
        toast('Mohon lengkapi semua data yang wajib diisi', 'error');
        return;
    }

    const product = State.selectedProduct;
    const game = State.selectedGame;
    const data = State.productsData;
    const pm = PAYMENTS_DATA.find(p => p.id === State.selectedPayment);
    const finalPrice = product.discount ? Math.round(product.price - (product.price * product.discount / 100)) : product.price;
    const total = finalPrice + (pm?.fee || 0);
    const itemLabel = product.amount + ' ' + data.currency + (product.bonus ? ' ' + product.bonus : '');

    document.getElementById('conf-game').textContent = game.name;
    document.getElementById('conf-item').textContent = itemLabel;
    document.getElementById('conf-userid').textContent = userId;
    document.getElementById('conf-wa').textContent = wa;
    document.getElementById('conf-payment').textContent = pm?.name || '-';
    document.getElementById('conf-total').textContent = formatPrice(total);

    const serverRow = document.getElementById('conf-server-row');
    if (needsServer && serverId) {
        serverRow.classList.remove('hidden');
        document.getElementById('conf-server').textContent = serverId;
    } else {
        serverRow.classList.add('hidden');
    }

    const emailRow = document.getElementById('conf-email-row');
    if (email) {
        emailRow.style.display = 'flex';
        document.getElementById('conf-email').textContent = email;
    } else {
        emailRow.style.display = 'none';
    }

    document.getElementById('confirm-dialog').classList.add('show');
}

function closeConfirmDialog() {
    document.getElementById('confirm-dialog').classList.remove('show');
}

// ── PLACE ORDER ───────────────────────────────────────
async function confirmOrder() {
    const btn = document.getElementById('confirm-btn');
    const userId   = document.getElementById('user-id').value.trim();
    const wa       = document.getElementById('whatsapp').value.trim();
    const serverId = document.getElementById('server-id').value.trim();
    const email    = document.getElementById('order-email').value.trim();

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Memproses...';

    try {
        const r = await apiPost({
            action:     'place_order',
            device_id:  DEVICE_ID,
            game_id:    State.selectedGame.id,
            product_id: State.selectedProduct.id,
            user_id:    userId,
            server_id:  serverId,
            whatsapp:   wa,
            email:      email,
            payment_id: State.selectedPayment,
        });

        closeConfirmDialog();

        if (r.success) {
            showSuccessDialog(r.transaction);
            resetOrderForm();
        } else {
            toast(r.message || 'Gagal membuat pesanan', 'error');
        }
    } catch (e) {
        toast('Koneksi gagal, coba lagi.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Konfirmasi & Bayar';
    }
}

function resetOrderForm() {
    document.getElementById('user-id').value = '';
    document.getElementById('server-id').value = '';
    document.getElementById('whatsapp').value = '';
    document.getElementById('order-email').value = '';
    State.selectedPayment = null;
}

// ── SUCCESS DIALOG ────────────────────────────────────
function showSuccessDialog(txn) {
    document.getElementById('suc-id').textContent = txn.id;
    document.getElementById('suc-game').textContent = txn.game_name;
    document.getElementById('suc-item').textContent = txn.item;
    document.getElementById('suc-payment').textContent = txn.payment_method;
    document.getElementById('suc-total').textContent = formatPrice(txn.total);
    document.getElementById('success-dialog').classList.add('show');
}

function closeSuccessDialog() {
    document.getElementById('success-dialog').classList.remove('show');
    backToGames();
}

function goToHistory() {
    document.getElementById('success-dialog').classList.remove('show');
    navigateTo('history');
}

// ── HISTORY ───────────────────────────────────────────
async function loadHistory() {
    const list  = document.getElementById('history-list');
    const empty = document.getElementById('history-empty');

    // Show device ID
    document.getElementById('history-device-id').textContent = DEVICE_ID;

    list.innerHTML = `<div style="text-align:center;padding:40px;color:var(--text-muted);">
        <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
    </div>`;
    empty.classList.add('hidden');

    try {
        const r = await apiPost({ action: 'get_my_transactions', device_id: DEVICE_ID });
        const txns = r.success ? r.transactions : [];

        if (!txns.length) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        list.innerHTML = txns.map(t => renderTransactionCard(t)).join('');
    } catch {
        list.innerHTML = `<div style="text-align:center;padding:40px;color:var(--text-muted);">
            Gagal memuat riwayat. <button onclick="loadHistory()" style="background:none;border:none;color:var(--accent);cursor:pointer;">Coba lagi</button>
        </div>`;
    }
}

function renderTransactionCard(t) {
    const statusLabel = { success: 'Berhasil', pending: 'Menunggu', failed: 'Gagal' };
    return `
    <div class="transaction-card">
        <div class="txn-header">
            <div>
                <div class="txn-game">
                    <i class="fas fa-gamepad" style="color:var(--accent);margin-right:8px;font-size:14px;"></i>${t.game_name}
                </div>
                <div class="txn-item">${t.item}</div>
            </div>
            <span class="txn-status ${t.status}">${statusLabel[t.status] || t.status}</span>
        </div>
        <div class="txn-meta">
            <span><i class="fas fa-calendar-alt"></i>${formatDate(t.date)}</span>
            <span><i class="fas fa-credit-card"></i>${t.payment_method}</span>
        </div>
        <div class="txn-info-chips">
            <span class="chip"><i class="fas fa-user" style="margin-right:4px;font-size:10px;"></i>UID: ${t.user_id}</span>
            ${t.server_id ? `<span class="chip"><i class="fas fa-server" style="margin-right:4px;font-size:10px;"></i>Server: ${t.server_id}</span>` : ''}
            <span class="chip"><i class="fab fa-whatsapp" style="margin-right:4px;font-size:10px;color:#25D366;"></i>${t.whatsapp}</span>
            ${t.email ? `<span class="chip"><i class="fas fa-envelope" style="margin-right:4px;font-size:10px;color:var(--accent);"></i>${t.email}</span>` : ''}
        </div>
        ${t.notes ? `<div style="font-size:12px;color:var(--text-muted);padding:8px 12px;background:var(--bg-secondary);border-radius:var(--radius-sm);margin-bottom:12px;">
            <i class="fas fa-info-circle" style="margin-right:5px;"></i>${t.notes}
        </div>` : ''}
        <div class="txn-footer">
            <div>
                <div class="txn-total">${formatPrice(t.total)}</div>
                <div class="txn-id">ID: ${t.id}</div>
            </div>
        </div>
    </div>`;
}

// ── COPY DEVICE ID ────────────────────────────────────
function copyDeviceId() {
    navigator.clipboard.writeText(DEVICE_ID).then(() => {
        toast('Device ID disalin!');
    }).catch(() => {
        toast('Gagal menyalin', 'error');
    });
}

// ── SEND HISTORY BY EMAIL (simulasi) ─────────────────
async function sendHistoryByEmail() {
    const email = document.getElementById('send-email-input').value.trim();
    if (!email || !email.includes('@')) {
        toast('Masukkan email yang valid', 'error');
        return;
    }

    // Ambil riwayat dulu
    const r = await apiPost({ action: 'get_my_transactions', device_id: DEVICE_ID });
    const txns = r.success ? r.transactions : [];

    if (!txns.length) {
        toast('Belum ada riwayat untuk dikirim', 'error');
        return;
    }

    // Simulasi pengiriman (tampilkan sukses, karena butuh SMTP di production)
    toast(`Riwayat ${txns.length} transaksi akan dikirim ke ${email} ✓`);
    document.getElementById('send-email-input').value = '';

    // Catatan: Untuk implementasi nyata, buat endpoint PHP dengan PHPMailer/Mailer
    // dan kirim summary transaksi device ini ke email yang diminta.
}

// ── DEVICE BANNER ─────────────────────────────────────
function showDeviceBanner() {
    const banner = document.getElementById('device-banner');
    document.getElementById('banner-device-id').textContent = DEVICE_ID;
    banner.style.display = 'block';
}

// ── INIT ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Tampilkan device banner
    showDeviceBanner();

    // Validasi form live
    ['user-id', 'whatsapp', 'server-id'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', validateForm);
    });

    // Close dialog on backdrop click
    document.getElementById('confirm-dialog').addEventListener('click', e => {
        if (e.target.id === 'confirm-dialog') closeConfirmDialog();
    });
    document.getElementById('success-dialog').addEventListener('click', e => {
        if (e.target.id === 'success-dialog') closeSuccessDialog();
    });

    // Load games
    loadGames();
    navigateTo('home');
});
