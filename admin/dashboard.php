<?php
// =============================================================
//  admin/dashboard.php  (replaces dashboard.html — Admin view)
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Pull live stats
$totalVoters    = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='approved'")->fetchColumn();
$pendingVoters  = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='pending'")->fetchColumn();
$totalCandidates= $db->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$votesCast      = $db->query("SELECT COUNT(DISTINCT voter_id) FROM votes")->fetchColumn();

// Active election
$election = $db->query("SELECT * FROM elections WHERE status='ongoing' LIMIT 1")->fetch();
if (!$election) {
    $election = $db->query("SELECT * FROM elections ORDER BY start_date DESC LIMIT 1")->fetch();
}

$navActive     = 'dashboard';
$sidebarActive = 'dashboard';

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | iVOTE CS</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        body { background:#f8fafc; padding-top:80px; }
        .page-wrap { display:flex; }
        .main { flex:1; margin-left:280px; padding:40px; min-height:calc(100vh - 80px); }
        h1 { font-family:'Montserrat',sans-serif; color:#12341d; font-size:1.8rem; font-weight:900; margin-bottom:6px; }
        .sub { color:#33553e; margin-bottom:30px; font-size:0.95rem; }

        /* Stats grid */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:30px; }
        .stat-card {
            background:#fff; border-radius:18px; padding:24px;
            border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.05);
            transition:transform 0.25s;
        }
        .stat-card:hover { transform:translateY(-4px); }
        .stat-label { font-size:0.8rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px; }
        .stat-value { font-family:'Montserrat',sans-serif; font-size:2rem; font-weight:900; color:#12341d; }
        .stat-sub   { font-size:0.82rem; color:#33553e; margin-top:6px; }

        /* Quick actions */
        .section-title { font-family:'Montserrat',sans-serif; color:#12341d; font-size:1.1rem; font-weight:800; margin-bottom:16px; }
        .actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:30px; }
        .action-card {
            background:#12341d; color:#fff; border-radius:16px; padding:22px;
            cursor:pointer; border:none; text-align:left; font-family:'Geist',sans-serif;
            transition:all 0.25s; text-decoration:none; display:block;
        }
        .action-card:hover { background:#33553e; transform:translateY(-3px); box-shadow:0 12px 24px rgba(18,52,29,0.2); }
        .action-icon { font-size:1.6rem; margin-bottom:12px; }
        .action-name { font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.95rem; }
        .action-desc { font-size:0.78rem; opacity:0.75; margin-top:4px; }

        /* Election status card */
        .election-card {
            background:#fff; border-radius:18px; padding:28px;
            border:1px solid #e2e8f0; margin-bottom:30px;
        }
        .election-title { font-family:'Montserrat',sans-serif; font-size:1.3rem; font-weight:800; color:#12341d; }
        .election-meta  { color:#33553e; font-size:0.9rem; margin-top:6px; }
        .status-pill {
            display:inline-block; padding:5px 16px; border-radius:100px;
            font-family:'Montserrat',sans-serif; font-weight:700; font-size:0.75rem;
            letter-spacing:0.5px; text-transform:uppercase; margin-left:12px;
        }
        .pill-ongoing  { background:#d1fae5; color:#065f46; }
        .pill-upcoming { background:#dbeafe; color:#1e40af; }
        .pill-ended    { background:#f1f5f9; color:#64748b; }
    </style>
</head>
<body>
<?php $navActive='dashboard'; require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-wrap">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="main">
        <?php if ($flash): ?>
            <div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <h1>Admin Control Panel</h1>
        <p class="sub">Welcome back, <?= htmlspecialchars(currentUser()['name']) ?>. Here's the system overview.</p>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Approved Voters</div>
                <div class="stat-value"><?= number_format($totalVoters) ?></div>
                <div class="stat-sub">Eligible to vote</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Votes Cast</div>
                <div class="stat-value"><?= number_format($votesCast) ?></div>
                <div class="stat-sub"><?= $totalVoters > 0 ? round($votesCast/$totalVoters*100,1) : 0 ?>% turnout</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Approvals</div>
                <div class="stat-value" style="color:<?= $pendingVoters>0?'#d97706':'#12341d' ?>"><?= $pendingVoters ?></div>
                <div class="stat-sub"><?= $pendingVoters>0?'Needs review':'All clear' ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Candidates</div>
                <div class="stat-value"><?= $totalCandidates ?></div>
                <div class="stat-sub">Registered candidates</div>
            </div>
        </div>

        <!-- Active Election -->
        <?php if ($election): ?>
        <div class="election-card">
            <div>
                <span class="election-title"><?= htmlspecialchars($election['title']) ?></span>
                <span class="status-pill pill-<?= $election['status'] ?>"><?= ucfirst($election['status']) ?></span>
            </div>
            <p class="election-meta">
                <?= date('M d, Y g:ia', strtotime($election['start_date'])) ?> —
                <?= date('M d, Y g:ia', strtotime($election['end_date']))   ?>
            </p>
        </div>
        <?php else: ?>
        <div class="election-card">
            <span class="election-title">No active election</span>
            <p class="election-meta">Create one from the Election Manager.</p>
        </div>
        <?php endif; ?>

        <!-- Quick actions -->
        <div class="section-title">Quick Actions</div>
        <div class="actions-grid">
            <a href="/admin/verification.php" class="action-card">
                <div class="action-icon">✅</div>
                <div class="action-name">Validate Accounts</div>
                <div class="action-desc"><?= $pendingVoters ?> pending review</div>
            </a>
            <a href="/admin/accounts.php" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-name">Manage Accounts</div>
                <div class="action-desc">View &amp; delete voter accounts</div>
            </a>
            <a href="/admin/elections.php" class="action-card">
                <div class="action-icon">🗳️</div>
                <div class="action-name">Manage Elections</div>
                <div class="action-desc">Create or close elections</div>
            </a>
            <a href="/admin/candidates.php" class="action-card">
                <div class="action-icon">🏅</div>
                <div class="action-name">Add Candidates</div>
                <div class="action-desc">Register candidates per position</div>
            </a>
        </div>
    </main>
</div>

<script src="/assets/js/shared.js"></script>
</body>
</html>
