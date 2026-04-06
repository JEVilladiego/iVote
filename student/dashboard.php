<?php
// =============================================================
//  student/dashboard.php
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireStudent();

// ---- AJAX: return candidates for a position (must be before HTML output) ----
if (isset($_GET['ajax']) && isset($_GET['pos'])) {
    $db    = getDB();
    $posId = intval($_GET['pos']);
    $cStmt = $db->prepare(
        "SELECT c.name, c.course, c.partylist, COUNT(v.id) AS votes
         FROM candidates c LEFT JOIN votes v ON v.candidate_id=c.id
         WHERE c.position_id=? GROUP BY c.id ORDER BY votes DESC"
    );
    $cStmt->execute([$posId]);
    $pTitle = $db->prepare("SELECT title FROM positions WHERE id=?");
    $pTitle->execute([$posId]);
    header('Content-Type: application/json');
    echo json_encode(['title'=>$pTitle->fetchColumn(),'candidates'=>$cStmt->fetchAll()]);
    exit;
}

$db   = getDB();
$user = currentUser();

// Active election
$election = $db->query(
    "SELECT * FROM elections WHERE status='ongoing' LIMIT 1"
)->fetch();

// Has this student voted in the active election?
$hasVoted = false;
$votedPositions = [];
if ($election) {
    // Primary check: use has_voted flag (set on finalize, covers abstentions too)
    $hvStmt = $db->prepare("SELECT has_voted FROM users WHERE id=?");
    $hvStmt->execute([$user['id']]);
    $hasVoted = (bool)$hvStmt->fetchColumn();

    // Always fetch voted position IDs for the sidebar indicators
    $vStmt = $db->prepare(
        "SELECT position_id FROM votes WHERE voter_id=? AND election_id=?"
    );
    $vStmt->execute([$user['id'], $election['id']]);
    $votedPositions = array_column($vStmt->fetchAll(), 'position_id');
}

// Election stats for dashboard
$stats = ['voters'=>0,'votes'=>0,'candidates'=>0,'positions'=>0];
if ($election) {
    $stats['voters']     = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='approved'")->fetchColumn();
    $stats['votes']      = $db->prepare("SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id=?")->execute([$election['id']]) ? 0 : 0;
    $sv = $db->prepare("SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id=?");
    $sv->execute([$election['id']]);
    $stats['votes']     = $sv->fetchColumn();
    $sc = $db->prepare("SELECT COUNT(*) FROM candidates WHERE election_id=?");
    $sc->execute([$election['id']]);
    $stats['candidates'] = $sc->fetchColumn();
    $sp = $db->prepare("SELECT COUNT(*) FROM positions WHERE election_id=?");
    $sp->execute([$election['id']]);
    $stats['positions']  = $sp->fetchColumn();
}

// Positions + live vote counts for the chart
$positions = [];
if ($election) {
    $pStmt = $db->prepare(
        "SELECT p.id, p.title,
                COUNT(v.id) AS vote_count
         FROM positions p
         LEFT JOIN votes v ON v.position_id=p.id AND v.election_id=?
         GROUP BY p.id
         ORDER BY p.sort_order"
    );
    $pStmt->execute([$election['id']]);
    $positions = $pStmt->fetchAll();
}

// Candidates for default first position
$firstPos = $positions[0] ?? null;
$displayCandidates = [];
if ($firstPos) {
    $cStmt = $db->prepare(
        "SELECT c.*, COUNT(v.id) AS votes
         FROM candidates c
         LEFT JOIN votes v ON v.candidate_id=c.id
         WHERE c.position_id=?
         GROUP BY c.id
         ORDER BY votes DESC"
    );
    $cStmt->execute([$firstPos['id']]);
    $displayCandidates = $cStmt->fetchAll();
}

$navActive = 'dashboard';
$flash     = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | iVOTE CS</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        :root{ --dark:#12341d;--soft:#33553e;--mid:#6d9078;--light:#a4c1ad;--tint:#d5e8db; }
        body { background:var(--tint); padding-top:80px; min-height:100vh; }
        .dashboard { max-width:1400px; margin:0 auto; padding:28px 20px; display:grid; gap:22px; }

        /* Summary cards */
        .summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        @media(max-width:900px){ .summary-grid{ grid-template-columns:repeat(2,1fr); } }
        .stat-card {
            background:rgba(255,255,255,0.85); border-radius:20px; padding:22px;
            border:1px solid var(--light); box-shadow:0 8px 24px rgba(18,52,29,0.08);
            backdrop-filter:blur(6px); transition:transform 0.25s;
        }
        .stat-card:hover { transform:translateY(-4px); }
        .stat-label { font-size:0.78rem; font-weight:700; color:var(--soft); text-transform:uppercase; letter-spacing:0.5px; }
        .stat-value { font-family:'Montserrat',sans-serif; font-size:2rem; font-weight:900; color:var(--dark); margin-top:8px; }
        .stat-sub   { font-size:0.82rem; color:var(--soft); margin-top:4px; }

        /* Main content grid */
        .main-grid { display:grid; grid-template-columns:320px 1fr; gap:22px; align-items:start; }
        @media(max-width:1100px){ .main-grid{ grid-template-columns:1fr; } }

        .card {
            background:rgba(255,255,255,0.85); border-radius:22px;
            border:1px solid var(--light); box-shadow:0 8px 24px rgba(18,52,29,0.08);
            backdrop-filter:blur(6px);
        }
        .card-header { padding:20px 24px 0; }
        .card-title { font-family:'Montserrat',sans-serif; font-size:1.05rem; font-weight:800; color:var(--dark); }
        .card-sub   { font-size:0.82rem; color:var(--soft); margin-top:4px; }

        /* Position list */
        .pos-list { padding:16px; display:flex; flex-direction:column; gap:10px; }
        .pos-item {
            background:var(--tint); border-radius:14px; padding:14px 16px;
            cursor:pointer; border:2px solid transparent; transition:all 0.25s;
        }
        .pos-item:hover { border-color:var(--mid); background:#e8f2eb; }
        .pos-item.active { border-color:var(--soft); background:#e8f2eb; }
        .pos-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
        .pos-name    { font-weight:700; color:var(--dark); font-size:0.9rem; }
        .pos-pct     { font-weight:800; font-size:0.85rem; color:var(--soft); }
        .pos-voted   { font-size:0.7rem; color:#10b981; font-weight:700; }
        .pos-track   { height:8px; background:rgba(18,52,29,0.1); border-radius:999px; overflow:hidden; }
        .pos-fill    { height:100%; border-radius:999px; background:linear-gradient(to right,var(--dark),var(--mid)); width:0; transition:width 0.8s ease; }

        /* Candidate panel */
        .cand-panel { padding:20px 24px; }
        .cand-grid  { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-top:16px; }
        .cand-card  {
            background:var(--tint); border-radius:18px; padding:18px 14px;
            text-align:center; border:1px solid rgba(18,52,29,0.08);
            transition:all 0.25s;
        }
        .cand-card:hover { transform:translateY(-4px); box-shadow:0 12px 24px rgba(18,52,29,0.12); }
        .cand-avatar {
            width:72px; height:72px; border-radius:50%; margin:0 auto 12px;
            background:linear-gradient(135deg,#fff,var(--tint));
            border:3px solid var(--soft); display:flex; align-items:center;
            justify-content:center; font-weight:900; font-size:1.2rem; color:var(--dark);
        }
        .cand-name   { font-weight:700; color:var(--dark); font-size:0.88rem; line-height:1.3; }
        .cand-course { font-size:0.72rem; color:var(--soft); margin-top:3px; }
        .cand-party  { font-size:0.68rem; background:rgba(18,52,29,0.08); color:var(--dark); padding:3px 8px; border-radius:100px; margin-top:6px; display:inline-block; font-weight:600; }
        .cand-votes  { font-family:'Montserrat',sans-serif; font-size:1.1rem; font-weight:900; color:var(--dark); margin-top:10px; }
        .cand-pct    { font-size:0.75rem; color:var(--soft); }
        .cand-bar-wrap { height:8px; background:rgba(18,52,29,0.1); border-radius:999px; overflow:hidden; margin-top:8px; }
        .cand-bar    { height:100%; border-radius:999px; background:linear-gradient(to right,var(--dark),var(--mid)); width:0; transition:width 0.8s ease; }

        /* Vote / status banner */
        .vote-banner {
            border-radius:20px; padding:20px 28px; margin-bottom:0;
            display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
        }
        .banner-voted    { background:linear-gradient(135deg,#d1fae5,#a7f3d0); border:1px solid #6ee7b7; }
        .banner-can-vote { background:linear-gradient(135deg,var(--dark),var(--soft)); color:#fff; }
        .banner-no-elec  { background:#f1f5f9; border:1px solid #e2e8f0; }
        .banner-text h3  { font-family:'Montserrat',sans-serif; font-weight:800; font-size:1.05rem; margin-bottom:4px; }
        .banner-text p   { font-size:0.85rem; opacity:0.85; }
        .btn-vote {
            font-family:'Montserrat',sans-serif; background:#fff; color:var(--dark);
            padding:12px 28px; border-radius:50px; border:none; font-weight:800;
            font-size:14px; cursor:pointer; white-space:nowrap; text-decoration:none;
            transition:all 0.2s; display:inline-block;
        }
        .btn-vote:hover { background:var(--tint); transform:translateY(-2px); }

        .no-election-msg { text-align:center; padding:60px 20px; color:var(--soft); }
        .no-election-msg .icon { font-size:3rem; margin-bottom:14px; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard">
    <?php if ($flash): ?>
        <div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Vote status banner -->
    <?php if (!$election): ?>
    <div class="vote-banner banner-no-elec">
        <div class="banner-text">
            <h3>No Active Election</h3>
            <p>There is currently no ongoing election. Check back later.</p>
        </div>
    </div>
    <?php elseif ($hasVoted): ?>
    <div class="vote-banner banner-voted">
        <div class="banner-text" style="color:#065f46">
            <h3>✅ You've Cast Your Ballot!</h3>
            <p>Thank you for participating in the <?= htmlspecialchars($election['title']) ?>. Your vote has been recorded.</p>
        </div>
    </div>
    <?php else: ?>
    <div class="vote-banner banner-can-vote">
        <div class="banner-text">
            <h3>🗳️ Election is Now Open!</h3>
            <p><?= htmlspecialchars($election['title']) ?> — Cast your vote before it ends.</p>
        </div>
        <a href="/student/vote.php" class="btn-vote">Vote Now →</a>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <?php if ($election): ?>
    <div class="summary-grid">
        <div class="stat-card">
            <div class="stat-label">Registered Voters</div>
            <div class="stat-value"><?= number_format($stats['voters']) ?></div>
            <div class="stat-sub">Eligible voters</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Votes Cast</div>
            <div class="stat-value"><?= number_format($stats['votes']) ?></div>
            <div class="stat-sub"><?= $stats['voters'] > 0 ? round($stats['votes']/$stats['voters']*100,1) : 0 ?>% turnout</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Positions</div>
            <div class="stat-value"><?= $stats['positions'] ?></div>
            <div class="stat-sub">Open positions</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Candidates</div>
            <div class="stat-value"><?= $stats['candidates'] ?></div>
            <div class="stat-sub">Running this election</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Position overview + candidates -->
    <?php if ($election && !empty($positions)): ?>
    <div class="main-grid">
        <!-- Position list -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Position Overview</div>
                <div class="card-sub">Click a position to see candidates</div>
            </div>
            <div class="pos-list" id="positionList">
                <?php
                $totalVotesAll = array_sum(array_column($positions, 'vote_count'));
                foreach ($positions as $i => $pos):
                    $pct = $stats['voters'] > 0 ? round($pos['vote_count']/$stats['voters']*100,1) : 0;
                    $voted = in_array($pos['id'], $votedPositions);
                ?>
                <div class="pos-item <?= $i===0?'active':'' ?>" data-pos-id="<?= $pos['id'] ?>"
                     onclick="loadPosition(<?= $pos['id'] ?>, this)">
                    <div class="pos-top">
                        <span class="pos-name"><?= htmlspecialchars($pos['title']) ?></span>
                        <?php if ($voted): ?><span class="pos-voted">✅ Voted</span><?php endif; ?>
                        <span class="pos-pct"><?= $pct ?>%</span>
                    </div>
                    <div class="pos-track">
                        <div class="pos-fill" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Candidate display panel -->
        <div class="card" id="candidateCard">
            <div class="cand-panel" id="candidatePanel">
                <div class="card-title" id="panelTitle"><?= htmlspecialchars($firstPos['title'] ?? '') ?> — Candidates</div>
                <div class="card-sub" id="panelSub">Live vote counts. Results update in real time.</div>
                <div class="cand-grid" id="candidateGrid">
                    <?php
                    $posTotal = array_sum(array_column($displayCandidates, 'votes'));
                    foreach ($displayCandidates as $c):
                        $pct = $posTotal > 0 ? round($c['votes']/$posTotal*100,1) : 0;
                        $initials = implode('', array_map(fn($p)=>strtoupper($p[0]??''), explode(' ', $c['name'])));
                        $initials = substr($initials,0,2);
                    ?>
                    <div class="cand-card">
                        <div class="cand-avatar"><?= htmlspecialchars($initials) ?></div>
                        <div class="cand-name"><?= htmlspecialchars($c['name']) ?></div>
                        <div class="cand-course"><?= htmlspecialchars($c['course']) ?></div>
                        <?php if ($c['partylist']): ?>
                            <span class="cand-party"><?= htmlspecialchars($c['partylist']) ?></span>
                        <?php endif; ?>
                        <div class="cand-votes"><?= $c['votes'] ?> <span style="font-size:0.7rem;font-weight:400">votes</span></div>
                        <div class="cand-pct"><?= $pct ?>%</div>
                        <div class="cand-bar-wrap"><div class="cand-bar" style="width:<?= $pct ?>%"></div></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($displayCandidates)): ?>
                        <p style="color:#94a3b8;grid-column:1/-1;text-align:center;padding:30px">No candidates registered for this position yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php elseif (!$election): ?>
    <div class="no-election-msg">
        <div class="icon">📭</div>
        <p>No election is currently active. The dashboard will update once an election begins.</p>
    </div>
    <?php endif; ?>
</div>

<script src="/assets/js/shared.js"></script>
<script>
async function loadPosition(posId, el) {
    // Update active state
    document.querySelectorAll('.pos-item').forEach(p => p.classList.remove('active'));
    el.classList.add('active');

    const res  = await fetch(`/student/dashboard.php?pos=${posId}&ajax=1`);
    const data = await res.json();

    document.getElementById('panelTitle').textContent = data.title + ' — Candidates';
    const grid = document.getElementById('candidateGrid');
    grid.innerHTML = '';

    if (!data.candidates.length) {
        grid.innerHTML = '<p style="color:#94a3b8;grid-column:1/-1;text-align:center;padding:30px">No candidates yet.</p>';
        return;
    }

    const total = data.candidates.reduce((s,c) => s + parseInt(c.votes), 0);
    data.candidates.forEach(c => {
        const pct = total > 0 ? (c.votes/total*100).toFixed(1) : 0;
        const init = c.name.split(' ').map(p=>p[0]||'').slice(0,2).join('').toUpperCase();
        grid.innerHTML += `
            <div class="cand-card">
                <div class="cand-avatar">${init}</div>
                <div class="cand-name">${c.name}</div>
                <div class="cand-course">${c.course}</div>
                ${c.partylist ? `<span class="cand-party">${c.partylist}</span>` : ''}
                <div class="cand-votes">${c.votes} <span style="font-size:0.7rem;font-weight:400">votes</span></div>
                <div class="cand-pct">${pct}%</div>
                <div class="cand-bar-wrap"><div class="cand-bar" style="width:${pct}%"></div></div>
            </div>`;
    });
}

// AJAX handled at top of file
</script>

</body>
</html>