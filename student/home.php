<?php
// =============================================================
//  student/home.php  —  Student Homepage
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireStudent();

$db   = getDB();
$user = currentUser();

// ---- Fetch active election --------------------------------
$election = $db->query(
    "SELECT * FROM elections WHERE status='ongoing' ORDER BY start_date DESC LIMIT 1"
)->fetch();

// ---- Fetch upcoming election (if none active) ------------
$upcoming = null;
if (!$election) {
    $upcoming = $db->query(
        "SELECT * FROM elections WHERE status='upcoming' ORDER BY start_date ASC LIMIT 1"
    )->fetch();
}

// ---- Fetch announcements ---------------------------------
// Assumes an `announcements` table: id, title, body, created_at, posted_by
// Gracefully skips if table doesn't exist yet
$announcements = [];
try {
    $announcements = $db->query(
        "SELECT a.*, u.first_name, u.last_name
         FROM announcements a
         LEFT JOIN users u ON a.posted_by = u.id
         ORDER BY a.created_at DESC LIMIT 5"
    )->fetchAll();
} catch (Exception $e) {
    // Table may not exist yet — show placeholder
}

$navActive = 'home';
$targetElection = $election ?? $upcoming;
$endDateJs = $targetElection ? $targetElection['end_date'] : null;
$startDateJs = $targetElection ? $targetElection['start_date'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | iVOTE CS</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Geist', sans-serif;
            background: linear-gradient(135deg, #33553e 0%, #b8d1c0 100%);
            background-attachment: fixed;
            min-height: 100vh;
            /* padding: 100px 24px 60px; */
            margin-top: 90px;
        }

        .page-wrap {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ── Welcome strip ── */
        .welcome-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .welcome-strip h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 900;
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .welcome-strip h1 span { color: #d5e8db; }
        .welcome-strip p {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.75);
            margin-top: 4px;
        }
        .date-badge {
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

        /* ── Glass card base ── */
        .glass {
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.55);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        }

        /* ── Election hero card ── */
        .election-card {
            padding: 36px 40px;
            position: relative;
            overflow: hidden;
        }
        .election-card::before {
            content: '🗳️';
            position: absolute;
            right: 32px; top: 50%;
            transform: translateY(-50%);
            font-size: 7rem;
            opacity: 0.08;
            pointer-events: none;
            line-height: 1;
        }
        .election-label {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 100px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .label-active   { background: #d1fae5; color: #065f46; }
        .label-upcoming { background: #fef3c7; color: #92400e; }
        .label-none     { background: #f1f5f9; color: #64748b; }

        .election-title {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(1.2rem, 2.5vw, 1.7rem);
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .election-sub {
            font-size: 0.88rem;
            color: #475569;
            margin-bottom: 28px;
        }

        /* Countdown */
        .countdown-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .countdown-block {
            background: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.7);
            border-radius: 16px;
            padding: 16px 22px;
            text-align: center;
            min-width: 80px;
            flex: 1;
        }
        .countdown-number {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            color: #12341d;
            line-height: 1;
            display: block;
        }
        .countdown-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-top: 4px;
            display: block;
        }

        .voted-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            border-radius: 12px;
            padding: 12px 20px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            color: #065f46;
        }

        .btn-vote {
            display: inline-block;
            margin-top: 24px;
            padding: 14px 32px;
            background: #33553e;
            color: #fff;
            border: none;
            border-radius: 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 6px 18px rgba(51,85,62,0.3);
        }
        .btn-vote:hover { background: #12341d; transform: translateY(-2px); }

        /* ── Two column layout ── */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 700px) { .two-col { grid-template-columns: 1fr; } }

        /* ── Info card ── */
        .info-card {
            padding: 28px 30px;
        }
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

        /* ── Announcements ── */
        .announcement-item {
            padding: 14px 0;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .announcement-item:last-child { border-bottom: none; padding-bottom: 0; }
        .announcement-item:first-child { padding-top: 0; }
        .ann-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .ann-body {
            font-size: 0.82rem;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .ann-meta {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
        }
        .ann-placeholder {
            text-align: center;
            padding: 30px 0;
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .ann-placeholder .ph-icon { font-size: 2rem; display: block; margin-bottom: 8px; }

        /* ── Status card ── */
        .status-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .status-row:last-child { border-bottom: none; }
        .status-row:first-child { padding-top: 0; }
        .status-icon {
            width: 40px; height: 40px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .si-green { background: #d1fae5; }
        .si-yellow { background: #fef3c7; }
        .si-blue   { background: #dbeafe; }
        .status-row-text strong {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem; font-weight: 700; color: #0f172a; display: block;
        }
        .status-row-text span { font-size: 0.78rem; color: #64748b; }

        /* Entrance animations */
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up:nth-child(1) { animation-delay: 0.05s; }
        .fade-up:nth-child(2) { animation-delay: 0.12s; }
        .fade-up:nth-child(3) { animation-delay: 0.19s; }
        .fade-up:nth-child(4) { animation-delay: 0.26s; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-wrap">

    <!-- Welcome strip -->
    <div class="welcome-strip fade-up">
        <div>
            <h1>Welcome back, <span><?= htmlspecialchars($user['first_name']) ?></span> 👋</h1>
            <p>Here's what's happening in your College of Science election.</p>
        </div>
        <div class="date-badge" id="liveDateBadge"></div>
    </div>

    <!-- Election hero card -->
    <div class="glass election-card fade-up">
        <?php if ($election): ?>
            <span class="election-label label-active">🟢 Election Live</span>
            <div class="election-title"><?= htmlspecialchars($election['title']) ?></div>
            <div class="election-sub">
                <?= date('F d, Y g:i A', strtotime($election['start_date'])) ?>
                &nbsp;→&nbsp;
                <?= date('F d, Y g:i A', strtotime($election['end_date'])) ?>
            </div>

            <?php if ($user['has_voted']): ?>
                <div class="voted-badge">✅ You have already cast your vote. Thank you for participating!</div>
            </br></br>
                <div class="countdown-row" id="countdown"></div> 
            <?php else: ?>
                <div class="countdown-row" id="countdown"></div>
                <a href="/student/vote.php" class="btn-vote">Cast Your Vote →</a>
            <?php endif; ?>

        <?php elseif ($upcoming): ?>
            <span class="election-label label-upcoming">⏳ Upcoming Election</span>
            <div class="election-title"><?= htmlspecialchars($upcoming['title']) ?></div>
            <div class="election-sub">
                Starts <?= date('F d, Y g:i A', strtotime($upcoming['start_date'])) ?>
            </div>
            <p style="color:#475569;font-size:0.88rem;margin-bottom:16px">Voting has not started yet. The countdown below shows time until the election opens.</p>
            <div class="countdown-row" id="countdown"></div>

        <?php else: ?>
            <span class="election-label label-none">No Active Election</span>
            <div class="election-title">No election is currently scheduled.</div>
            <div class="election-sub">Check back later or watch the announcements below for updates.</div>
        <?php endif; ?>
    </div>

    <!-- Two column: announcements + account status -->
    <div class="two-col">

        <!-- Announcements -->
        <div class="glass info-card fade-up">
            <div class="card-heading">📢 Announcements</div>
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $ann): ?>
                <div class="announcement-item">
                    <div class="ann-title"><?= htmlspecialchars($ann['title']) ?></div>
                    <div class="ann-body"><?= htmlspecialchars($ann['body']) ?></div>
                    <div class="ann-meta">
                        Posted by <?= htmlspecialchars($ann['first_name'] ?? 'Admin') ?>
                        · <?= date('M d, Y', strtotime($ann['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="ann-placeholder">
                    <span class="ph-icon">📭</span>
                    No announcements yet. Check back soon!
                </div>
            <?php endif; ?>
        </div>

        <!-- Account & election status at-a-glance -->
        <div class="glass info-card fade-up">
            <div class="card-heading">📋 Your Status</div>

            <div class="status-row">
                <div class="status-icon si-green">🎓</div>
                <div class="status-row-text">
                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                    <span><?= htmlspecialchars($user['student_id']) ?></span>
                </div>
            </div>

            <div class="status-row">
                <div class="status-icon <?= $user['status'] === 'approved' ? 'si-green' : 'si-yellow' ?>">
                    <?= $user['status'] === 'approved' ? '✅' : '⏳' ?>
                </div>
                <div class="status-row-text">
                    <strong>Account <?= ucfirst(htmlspecialchars($user['status'])) ?></strong>
                    <span><?= $user['status'] === 'approved' ? 'You are cleared to vote.' : 'Awaiting admin verification.' ?></span>
                </div>
            </div>

            <div class="status-row">
                <div class="status-icon <?= $election ? 'si-green' : 'si-yellow' ?>">
                    <?= $election ? '🗳️' : '🕐' ?>
                </div>
                <div class="status-row-text">
                    <strong><?= $election ? 'Election Open' : ($upcoming ? 'Election Upcoming' : 'No Election') ?></strong>
                    <span>
                        <?php if ($election): ?>
                            Ends <?= date('M d, Y', strtotime($election['end_date'])) ?>
                        <?php elseif ($upcoming): ?>
                            Starts <?= date('M d, Y', strtotime($upcoming['start_date'])) ?>
                        <?php else: ?>
                            Nothing scheduled yet
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="status-row">
                <div class="status-icon <?= $user['has_voted'] ? 'si-green' : 'si-blue' ?>">
                    <?= $user['has_voted'] ? '🏅' : '📝' ?>
                </div>
                <div class="status-row-text">
                    <strong><?= $user['has_voted'] ? 'Vote Cast' : 'Not Yet Voted' ?></strong>
                    <span><?= $user['has_voted'] ? 'Thank you for participating!' : ($election ? 'Head to the vote page to cast your ballot.' : 'Wait for an active election.') ?></span>
                </div>
            </div>
        </div>

    </div>
</div><!-- /page-wrap -->

<script src="/assets/js/shared.js"></script>
<script>
// Live date badge
function updateDate() {
    const now = new Date();
    document.getElementById('liveDateBadge').textContent =
        now.toLocaleDateString('en-PH', { weekday:'short', month:'long', day:'numeric', year:'numeric' });
}
updateDate();

// Countdown timer
<?php if ($targetElection): ?>
(function() {
    let target, label;

    <?php if ($election): ?>
        target = new Date("<?= addslashes($election['end_date']) ?>").getTime();
        label  = "Election closes in";
    <?php else: ?>
        target = new Date("<?= addslashes($upcoming['start_date']) ?>").getTime();
        label  = "Election opens in";
    <?php endif; ?>

    const container = document.getElementById('countdown');
    if (!container) return;

    function tick() {
        const now  = Date.now();
        const diff = target - now;

        if (diff <= 0) {
            container.innerHTML = '<p style="font-weight:700;color:#33553e">⏰ Time is up! Refresh the page.</p>';
            return;
        }

        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000)  / 60000);
        const s = Math.floor((diff % 60000)    / 1000);

        // 2. Add a header to show the label (Election opens/closes in)
        container.innerHTML = `
            <div style="width:100%; margin-bottom:10px; font-weight:700; color:#475569; font-size:0.8rem; text-transform:uppercase;">
                ${label}
            </div>
            ${[['Days',d],['Hours',h],['Mins',m],['Secs',s]].map(([lbl, val]) => `
            <div class="countdown-block">
                <span class="countdown-number">${String(val).padStart(2,'0')}</span>
                <span class="countdown-label">${lbl}</span>
            </div>`).join('')}
        `;
    }
    tick();
    setInterval(tick, 1000);
})();
<?php endif; ?>
</script>
</body>
</html>