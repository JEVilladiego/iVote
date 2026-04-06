<?php
// =============================================================
//  admin/home.php  —  Admin Homepage
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db   = getDB();
$user = currentUser();

// ── Quick stats ─────────────────────────────────────────────
$totalStudents  = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$approvedVoters = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='approved'")->fetchColumn();
$pendingCount   = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='pending'")->fetchColumn();
$rejectedCount  = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='rejected'")->fetchColumn();
$votesCast      = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND has_voted=1")->fetchColumn();

// Active election
$election = $db->query(
    "SELECT * FROM elections WHERE status='ongoing' ORDER BY start_date DESC LIMIT 1"
)->fetch();

// Voter turnout %
$turnout = $approvedVoters > 0 ? round(($votesCast / $approvedVoters) * 100) : 0;

// ── Recent activity feed ─────────────────────────────────────
// Combines: new registrations, approvals, rejections
// Requires columns: status, verified_at, verified_by, created_at
$activity = $db->query(
    "SELECT u.id, u.first_name, u.last_name, u.student_id, u.course,
            u.status, u.created_at, u.verified_at,
            v.first_name AS verified_by_name
     FROM users u
     LEFT JOIN users v ON u.verified_by = v.id
     WHERE u.role = 'student'
     ORDER BY GREATEST(u.created_at, COALESCE(u.verified_at, '1970-01-01')) DESC
     LIMIT 10"
)->fetchAll();

$navActive     = 'home';
$sidebarActive = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | iVOTE CS Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Geist', sans-serif;
            background: linear-gradient(135deg, #33553e 0%, #b8d1c0 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding-top: 80px;
        }

        .page-wrap { display: flex; }

        .main {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            min-height: calc(100vh - 80px);
        }

        /* ── Page header ── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 28px;
        }
        .page-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(1.4rem, 2.5vw, 1.9rem);
            font-weight: 900;
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .page-header p { font-size: 0.88rem; color: rgba(255,255,255,0.75); margin-top: 4px; }
        .live-time {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            padding: 8px 18px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
        }

        /* ── Glass base ── */
        .glass {
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.55);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        }

        /* ── Stats grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            padding: 24px 22px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 36px rgba(0,0,0,0.12);
        }
        .stat-icon {
            font-size: 1.6rem;
            margin-bottom: 10px;
            display: block;
        }
        .stat-value {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
            display: block;
        }
        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-top: 4px;
            display: block;
        }
        .stat-card .stat-bg {
            position: absolute;
            right: -8px; bottom: -8px;
            font-size: 5rem;
            opacity: 0.06;
            line-height: 1;
            pointer-events: none;
        }

        /* Turnout bar */
        .turnout-card { padding: 28px 30px; margin-bottom: 24px; }
        .turnout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .turnout-header h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
        }
        .turnout-pct {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.6rem;
            font-weight: 900;
            color: #33553e;
        }
        .bar-track {
            width: 100%;
            height: 14px;
            background: rgba(0,0,0,0.08);
            border-radius: 100px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            background: linear-gradient(to right, #33553e, #6d9078);
            border-radius: 100px;
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: 0%;
        }
        .turnout-sub {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 8px;
        }

        /* Election status pill in turnout */
        .election-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 14px;
            border-radius: 100px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .pill-active   { background: #d1fae5; color: #065f46; }
        .pill-inactive { background: #f1f5f9; color: #64748b; }

        /* ── Bottom two-col ── */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

        /* ── Activity feed ── */
        .feed-card { padding: 28px 30px; }
        .card-heading {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .feed-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .feed-item:last-child { border-bottom: none; padding-bottom: 0; }
        .feed-item:first-child { padding-top: 0; }

        .feed-dot {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0; margin-top: 2px;
        }
        .dot-registered { background: #dbeafe; }
        .dot-approved   { background: #d1fae5; }
        .dot-rejected   { background: #fee2e2; }

        .feed-text strong {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
            display: block;
            margin-bottom: 2px;
        }
        .feed-text span { font-size: 0.78rem; color: #64748b; }
        .feed-time {
            margin-left: auto;
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
            white-space: nowrap;
            padding-top: 4px;
        }

        /* ── Quick links ── */
        .links-card { padding: 28px 30px; }
        .quick-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: rgba(255,255,255,0.4);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 14px;
            text-decoration: none;
            color: #0f172a;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .quick-link:last-child { margin-bottom: 0; }
        .quick-link:hover {
            background: rgba(255,255,255,0.65);
            transform: translateX(4px);
        }
        .ql-left { display: flex; align-items: center; gap: 12px; }
        .ql-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; background: rgba(51,85,62,0.1);
        }
        .ql-text strong {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem; font-weight: 700; color: #0f172a; display: block;
        }
        .ql-text span { font-size: 0.75rem; color: #64748b; }
        .ql-arrow { color: #94a3b8; font-size: 0.85rem; }

        .pending-badge {
            background: #fef3c7; color: #92400e;
            border-radius: 100px; padding: 2px 8px;
            font-size: 0.7rem; font-weight: 800;
        }

        /* Entrance animations */
        .fade-up {
            opacity: 0;
            transform: translateY(18px);
            animation: fadeUp 0.45s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.12s; }
        .delay-3 { animation-delay: 0.19s; }
        .delay-4 { animation-delay: 0.26s; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-wrap">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">

        <!-- Header -->
        <div class="page-header fade-up delay-1">
            <div>
                <h1>Good <?php
                    $h = (int)date('H');
                    echo $h < 12 ? 'Morning' : ($h < 17 ? 'Afternoon' : 'Evening');
                ?>, Admin 👋</h1>
                <p>Here's a live snapshot of the iVOTE CS system.</p>
            </div>
            <div class="live-time" id="liveTime"></div>
        </div>

        <!-- Stats grid -->
        <div class="stats-grid fade-up delay-2">
            <div class="glass stat-card">
                <span class="stat-icon">👥</span>
                <span class="stat-value"><?= $totalStudents ?></span>
                <span class="stat-label">Total Registered</span>
                <span class="stat-bg">👥</span>
            </div>
            <div class="glass stat-card">
                <span class="stat-icon">✅</span>
                <span class="stat-value"><?= $approvedVoters ?></span>
                <span class="stat-label">Approved Voters</span>
                <span class="stat-bg">✅</span>
            </div>
            <div class="glass stat-card">
                <span class="stat-icon">⏳</span>
                <span class="stat-value" style="color:<?= $pendingCount > 0 ? '#92400e' : '#0f172a' ?>"><?= $pendingCount ?></span>
                <span class="stat-label">Pending Approval</span>
                <span class="stat-bg">⏳</span>
            </div>
            <div class="glass stat-card">
                <span class="stat-icon">🗳️</span>
                <span class="stat-value"><?= $votesCast ?></span>
                <span class="stat-label">Votes Cast</span>
                <span class="stat-bg">🗳️</span>
            </div>
            <div class="glass stat-card">
                <span class="stat-icon">❌</span>
                <span class="stat-value"><?= $rejectedCount ?></span>
                <span class="stat-label">Rejected</span>
                <span class="stat-bg">❌</span>
            </div>
        </div>

        <!-- Voter turnout bar -->
        <div class="glass turnout-card fade-up delay-3">
            <div class="turnout-header">
                <div>
                    <h3>Voter Turnout
                        <?php if ($election): ?>
                            <span class="election-pill pill-active" style="margin-left:10px">🟢 <?= htmlspecialchars($election['title']) ?></span>
                        <?php else: ?>
                            <span class="election-pill pill-inactive" style="margin-left:10px">No Active Election</span>
                        <?php endif; ?>
                    </h3>
                </div>
                <span class="turnout-pct"><?= $turnout ?>%</span>
            </div>
            <div class="bar-track">
                <div class="bar-fill" id="turnoutBar" data-width="<?= $turnout ?>"></div>
            </div>
            <div class="turnout-sub">
                <?= $votesCast ?> of <?= $approvedVoters ?> approved voters have cast their ballot.
            </div>
        </div>

        <!-- Activity feed + quick links -->
        <div class="two-col fade-up delay-4">

            <!-- Activity feed -->
            <div class="glass feed-card">
                <div class="card-heading">🕐 Recent Activity</div>
                <?php if (empty($activity)): ?>
                    <p style="color:#94a3b8;font-size:0.85rem;text-align:center;padding:30px 0">No activity yet.</p>
                <?php else: ?>
                    <?php foreach ($activity as $a):
                        $name      = htmlspecialchars($a['last_name'] . ', ' . $a['first_name']);
                        $isVerified = !empty($a['verified_at']);
                        $eventTime  = $isVerified ? $a['verified_at'] : $a['created_at'];
                        $ts         = strtotime($eventTime);
                        $diff       = time() - $ts;
                        $timeAgo    = $diff < 60 ? 'just now'
                                    : ($diff < 3600 ? floor($diff/60).'m ago'
                                    : ($diff < 86400 ? floor($diff/3600).'h ago'
                                    : date('M d', $ts)));
                        if ($isVerified) {
                            $dot  = $a['status'] === 'approved' ? 'dot-approved' : 'dot-rejected';
                            $icon = $a['status'] === 'approved' ? '✅' : '❌';
                            $desc = $a['status'] === 'approved'
                                  ? 'Approved by ' . htmlspecialchars($a['verified_by_name'] ?? 'Admin')
                                  : 'Rejected by ' . htmlspecialchars($a['verified_by_name'] ?? 'Admin');
                        } else {
                            $dot  = 'dot-registered';
                            $icon = '📝';
                            $desc = 'Registered — ' . htmlspecialchars($a['status']);
                        }
                    ?>
                    <div class="feed-item">
                        <div class="feed-dot <?= $dot ?>"><?= $icon ?></div>
                        <div class="feed-text">
                            <strong><?= $name ?></strong>
                            <span><?= htmlspecialchars($a['student_id']) ?> · <?= $desc ?></span>
                        </div>
                        <div class="feed-time"><?= $timeAgo ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Quick links -->
            <div class="glass links-card">
                <div class="card-heading">⚡ Quick Actions</div>

                <a href="/admin/verification.php" class="quick-link">
                    <div class="ql-left">
                        <div class="ql-icon">✅</div>
                        <div class="ql-text">
                            <strong>Validate Accounts</strong>
                            <span>Review pending registrations</span>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <?php if ($pendingCount > 0): ?>
                            <span class="pending-badge"><?= $pendingCount ?> pending</span>
                        <?php endif; ?>
                        <span class="ql-arrow">›</span>
                    </div>
                </a>

                <a href="/admin/accounts.php" class="quick-link">
                    <div class="ql-left">
                        <div class="ql-icon">👥</div>
                        <div class="ql-text">
                            <strong>Manage Accounts</strong>
                            <span>View all student records</span>
                        </div>
                    </div>
                    <span class="ql-arrow">›</span>
                </a>

                <a href="/admin/elections.php" class="quick-link">
                    <div class="ql-left">
                        <div class="ql-icon">🗳️</div>
                        <div class="ql-text">
                            <strong>Manage Elections</strong>
                            <span>Create or edit elections</span>
                        </div>
                    </div>
                    <span class="ql-arrow">›</span>
                </a>

                <a href="/admin/candidates.php" class="quick-link">
                    <div class="ql-left">
                        <div class="ql-icon">🏅</div>
                        <div class="ql-text">
                            <strong>Manage Candidates</strong>
                            <span>Add or update candidates</span>
                        </div>
                    </div>
                    <span class="ql-arrow">›</span>
                </a>
            </div>

        </div>
    </main>
</div>

<script src="/assets/js/shared.js"></script>
<script>
// Live clock
function updateTime() {
    document.getElementById('liveTime').textContent =
        new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}
updateTime();
setInterval(updateTime, 1000);

// Animate turnout bar on load
window.addEventListener('load', () => {
    const bar = document.getElementById('turnoutBar');
    if (bar) {
        setTimeout(() => { bar.style.width = bar.dataset.width + '%'; }, 300);
    }
});
</script>
</body>
</html>