<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
if ($_SESSION['status'] === 'approved') {
    header('Location: /student/dashboard.php'); exit;
}
$user = currentUser();
$navActive = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awaiting Verification | iVOTE CS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Geist:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        body { background: linear-gradient(135deg,#33553e 0%,#b8d1c0 100%); min-height:100vh; padding-top:80px; display:flex; align-items:center; justify-content:center; }
        .card {
            background:rgba(255,255,255,0.35); backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,0.25); border-radius:28px;
            padding:50px 40px; text-align:center; max-width:480px; width:90%;
        }
        .icon { font-size:3.5rem; margin-bottom:20px; }
        h2 { font-family:'Montserrat',sans-serif; color:#fff; font-size:1.8rem; font-weight:900; margin-bottom:12px; }
        p  { color:rgba(255,255,255,0.85); line-height:1.7; margin-bottom:24px; }
        .status-pill {
            display:inline-block; padding:8px 22px; border-radius:100px;
            font-family:'Montserrat',sans-serif; font-weight:800; font-size:12px;
            letter-spacing:1px; text-transform:uppercase;
        }
        .pending  { background:#fef3c7; color:#92400e; }
        .rejected { background:#fee2e2; color:#991b1b; }
        .logout-link { display:block; margin-top:30px; color:rgba(255,255,255,0.7); font-size:13px; text-decoration:underline; cursor:pointer; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>
<div class="card">
    <?php if ($_SESSION['status'] === 'rejected'): ?>
        <div class="icon">❌</div>
        <h2>Verification Rejected</h2>
        <p>Your registration was not approved. Please visit the admin office or re-register with the correct documents.</p>
        <span class="status-pill rejected">Rejected</span>
    <?php else: ?>
        <div class="icon">⏳</div>
        <h2>Awaiting Verification</h2>
        <p>Hi <strong><?= htmlspecialchars($user['first_name']) ?></strong>, your account is currently under review. An administrator will verify your registration shortly. Please check back later.</p>
        <span class="status-pill pending">Pending Approval</span>
    <?php endif; ?>
    <form method="POST" action="/logout.php">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <button type="submit" style="background:none;border:none;cursor:pointer;" class="logout-link">Log Out</button>
    </form>
</div>
<script src="/assets/js/shared.js"></script>
</body>
</html>
