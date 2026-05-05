<?php
require_once __DIR__ . '/auth.php';

// Already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if (adminLogin($u, $p)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – NexusTopup</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Orbitron:wght@700;900&display=swap');
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#08090d;--card:#131722;--border:rgba(255,255,255,0.07);
  --accent:#63b3ed;--accent2:#9f7aea;--text:#e8eaed;--muted:#8892a4;
  --danger:#fc8181;
}
body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse at 50% 0%,rgba(99,179,237,0.06) 0%,transparent 60%);pointer-events:none;}
.card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:40px;width:100%;max-width:400px;position:relative;z-index:1;}
.logo{text-align:center;margin-bottom:32px;}
.logo-icon{width:52px;height:52px;background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:22px;color:#fff;margin-bottom:12px;box-shadow:0 0 30px rgba(99,179,237,0.35);}
.logo-text{font-family:'Orbitron',sans-serif;font-weight:900;font-size:20px;background:linear-gradient(90deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.subtitle{font-size:13px;color:var(--muted);margin-top:4px;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:13px;font-weight:500;color:var(--muted);margin-bottom:7px;}
.input-wrap{position:relative;}
.input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px;}
.form-group input{width:100%;padding:12px 14px 12px 40px;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:.2s;}
.form-group input:focus{border-color:var(--accent);background:rgba(99,179,237,0.05);box-shadow:0 0 0 3px rgba(99,179,237,0.1);}
.error{background:rgba(252,129,129,0.1);border:1px solid rgba(252,129,129,0.3);color:var(--danger);border-radius:10px;padding:11px 14px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;border-radius:10px;color:#fff;font-size:15px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:.2s;margin-top:4px;}
.btn:hover{box-shadow:0 6px 25px rgba(99,179,237,0.4);transform:translateY(-1px);}
.back-link{text-align:center;margin-top:20px;font-size:13px;color:var(--muted);}
.back-link a{color:var(--accent);text-decoration:none;}
.back-link a:hover{text-decoration:underline;}
.hint{background:rgba(99,179,237,0.06);border:1px solid rgba(99,179,237,0.15);border-radius:10px;padding:10px 14px;font-size:12px;color:var(--muted);margin-top:18px;text-align:center;}
.hint strong{color:var(--accent);}
</style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
        <div class="logo-text">NEXUSTOPUP</div>
        <div class="subtitle">Panel Administrator</div>
    </div>

    <?php if ($error): ?>
    <div class="error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <div class="input-wrap">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="admin" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>
        </div>
        <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Masuk ke Panel</button>
    </form>

    <div class="hint">Default: <strong>admin</strong> / <strong>admin123</strong><br>Ganti password setelah login pertama.</div>

    <div class="back-link"><a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke Toko</a></div>
</div>
</body>
</html>
