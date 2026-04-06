<?php
// =============================================================
//  guest/dashboard.php
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';


$db   = getDB();

// Image handling functions ported from vote.php
function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    return strtoupper(($parts[0][0] ?? '') . ($parts[1][0] ?? ''));
}
function photoSrc(?string $path): string {
    if (!$path) return '';
    return '/' . ltrim($path, '/');
}

// Active election
$election = $db->query(
    "SELECT * FROM elections WHERE status='ongoing' LIMIT 1"
)->fetch();

// Election stats for dashboard
$stats = ['voters'=>0,'votes'=>0,'candidates'=>0,'positions'=>0];
if ($election) {
    $stats['voters']     = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='approved'")->fetchColumn();
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
$candidatesByPos = [];
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

    // Fetch all candidates to populate the scroll-down view
    if (!empty($positions)) {
        $cStmt = $db->prepare(
            "SELECT c.*, COUNT(v.id) AS votes
             FROM candidates c
             LEFT JOIN votes v ON v.candidate_id=c.id
             GROUP BY c.id
             ORDER BY votes DESC"
        );
        $cStmt->execute();
        $allCandidates = $cStmt->fetchAll();
        
        foreach ($allCandidates as $c) {
            $candidatesByPos[$c['position_id']][] = $c;
        }
    }
}

$navActive = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | iVOTE CS</title>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/shared.css">
  <style>
    :root {
      --dark: #12341d;
      --dark-soft: #33553e;
      --mid: #6d9078;
      --light-mid: #a4c1ad;
      --light: #d5e8db;
      --white: #ffffff;
      --shadow: 0 10px 30px rgba(18, 52, 29, 0.10);
      --radius-xl: 24px;
      --radius-lg: 18px;
      --radius-md: 14px;
      --transition: 0.25s ease;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: Arial, Helvetica, sans-serif;
      background: var(--light);
      color: var(--dark);
      min-height: 100vh;
      padding-top: 80px;
    }

    .dashboard {
      max-width: 1100px;
      margin: 0 auto;
      padding: 18px;
      display: grid;
      gap: 18px;
    }

    .card {
      background: rgba(255, 255, 255, 0.8);
      border: 1px solid var(--light-mid);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow);
      backdrop-filter: blur(8px);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: stretch;
      gap: 18px;
      padding: 20px;
      flex-wrap: wrap;
    }

    .header-left h1 { font-size: 2rem; margin-top: 6px; }
    .eyebrow { font-size: 0.8rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--dark-soft); font-weight: bold; }
    .subtext { margin-top: 10px; color: var(--dark-soft); line-height: 1.5; max-width: 760px; font-size: 0.95rem; }

    .header-right { display: grid; grid-template-columns: repeat(2, minmax(150px, 1fr)); gap: 12px; min-width: 320px; flex: 1; max-width: 430px; }
    .status-box { border-radius: var(--radius-lg); padding: 16px; color: var(--white); box-shadow: var(--shadow); }
    .status-box.primary { background: var(--dark); }
    .status-box.secondary { background: var(--mid); }
    .status-box .label { font-size: 0.74rem; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.75; }
    .status-box .value { font-size: 1rem; font-weight: bold; margin-top: 10px; line-height: 1.35; }

    .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    .summary-card { padding: 18px; transition: transform var(--transition), box-shadow var(--transition); }
    .summary-card:hover { transform: translateY(-4px); box-shadow: 0 18px 35px rgba(18, 52, 29, 0.14); }
    .summary-title { color: var(--dark-soft); font-size: 0.9rem; font-weight: bold; }
    .summary-main { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; margin-top: 14px; }
    .summary-number { font-size: 1.8rem; font-weight: bold; }
    .badge { background: var(--light); color: var(--dark); border-radius: 999px; padding: 6px 12px; font-size: 0.72rem; font-weight: bold; }
    .summary-foot { margin-top: 10px; font-size: 0.86rem; color: var(--dark-soft); }

    .section { padding: 18px; }
    .section-head { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 16px; flex-wrap: wrap; }
    .section-head h2 { font-size: 1.2rem; }
    .section-head p { color: var(--dark-soft); font-size: 0.88rem; margin-top: 5px; }

    .position-track { height: 13px; border-radius: 999px; overflow: hidden; background: #c9ddd0; margin-top: 8px; }
    .position-fill { height: 100%; border-radius: 999px; background: linear-gradient(to right, var(--dark), var(--mid)); transition: width 0.8s ease; }

    .position-panel { background: linear-gradient(135deg, #eef6f0, #dcece1); border-radius: var(--radius-xl); padding: 16px; border: 1px solid var(--light-mid); display: flex; flex-direction: column; margin-bottom: 24px; }
    .position-panel h3 { font-size: 1.1rem; }
    .position-panel p { margin-top: 5px; color: var(--dark-soft); font-size: 0.85rem; line-height: 1.45; }

    .candidate-grid-wrap { margin-top: 16px; background: var(--light); border-radius: var(--radius-xl); padding: 16px; overflow-x: auto; flex: 1; }
    .candidate-grid { min-height: 440px; display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 220px)); gap: 16px; align-items: stretch; justify-content: start; }
    
    .candidate-column { background: rgba(255,255,255,0.55); border: 1px solid #c5d8cc; border-radius: 22px; padding: 14px 12px 0; display: grid; grid-template-rows: auto 1fr; min-height: 408px; overflow: hidden; }
    .candidate-top { display: grid; justify-items: center; text-align: center; gap: 8px; min-height: 140px; }
    .candidate-profile { width: 92px; height: 92px; border-radius: 50%; border: 5px solid var(--dark-soft); background: linear-gradient(135deg, #ffffff, #c7dacc); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold; color: var(--dark); box-shadow: 0 8px 20px rgba(18, 52, 29, 0.12); overflow: hidden; flex-shrink: 0; }
    .candidate-profile img { width: 100%; height: 100%; object-fit: cover; }
    
    .candidate-details { text-align: center; display: grid; gap: 3px; }
    .candidate-name { font-size: 0.95rem; font-weight: bold; line-height: 1.25; }
    .candidate-course { font-size: 0.77rem; color: var(--dark-soft); line-height: 1.25; }
    .candidate-running { font-size: 0.77rem; color: var(--dark-soft); line-height: 1.25; font-style: italic; }
    
    .candidate-bar-zone { display: flex; align-items: end; justify-content: center; height: 240px; padding-top: 14px; }
    .candidate-bar-area { width: 100%; max-width: 92px; height: 220px; display: flex; align-items: flex-end; justify-content: center; }
    .candidate-bar { width: 100%; border-radius: 20px 20px 0 0; background: linear-gradient(to top, var(--dark), var(--mid)); transition: height 0.8s ease; box-shadow: 0 8px 20px rgba(18, 52, 29, 0.14); display: flex; align-items: center; justify-content: center; position: relative; padding: 8px 4px; }
    .bar-label { font-size: 0.72rem; color: white; text-align: center; line-height: 1.2; pointer-events: none; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.15); }
    .bar-label strong { font-size: 0.92rem; display: block; }

    @media (max-width: 980px) { .summary-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .dashboard { padding: 14px; } .summary-grid, .header-right { grid-template-columns: 1fr; } .header-left h1 { font-size: 1.65rem; } }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="dashboard">

    <?php if (!$election): ?>
    <section class="card header">
      <div class="header-left">
        <div class="eyebrow">Student Organization Voting System</div>
        <h1>No Active Election</h1>
        <p class="subtext">There is currently no ongoing election. The dashboard will update once an election begins. Check back later.</p>
      </div>
      <div class="header-right">
        <div class="status-box primary">
          <div class="label">Status</div>
          <div class="value">Pending</div>
        </div>
        <div class="status-box secondary">
          <div class="label">Current View</div>
          <div class="value">Guest View</div>
        </div>
      </div>
    </section>

    <?php else: ?>
    <section class="card header">
      <div class="header-left">
        <div class="eyebrow">Student Organization Voting System</div>
        <h1>Election is Now Open!</h1>
        <p class="subtext"><?= htmlspecialchars($election['title']) ?> — View the live results below.</p>
      </div>

      <div class="header-right">
        <div class="status-box primary">
          <div class="label">Current Election</div>
          <div class="value"><?= htmlspecialchars($election['title']) ?></div>
        </div>
        <div class="status-box secondary">
          <div class="label">Current View</div>
          <div class="value">Guest View</div>
        </div>
      </div>
    </section>

    <section class="summary-grid" id="summaryGrid">
      <div class="card summary-card">
        <div class="summary-title">Registered Voters</div>
        <div class="summary-main">
          <div class="summary-number"><?= number_format($stats['voters']) ?></div>
          <span class="badge">Live</span>
        </div>
        <div class="summary-foot">Eligible voters</div>
      </div>

      <div class="card summary-card">
        <div class="summary-title">Votes Cast</div>
        <div class="summary-main">
          <div class="summary-number"><?= number_format($stats['votes']) ?></div>
          <span class="badge">Live</span>
        </div>
        <div class="summary-foot"><?= $stats['voters'] > 0 ? round($stats['votes']/$stats['voters']*100,1) : 0 ?>% turnout</div>
      </div>

      <div class="card summary-card">
        <div class="summary-title">Positions</div>
        <div class="summary-main">
          <div class="summary-number"><?= $stats['positions'] ?></div>
          <span class="badge">Open</span>
        </div>
        <div class="summary-foot">Positions available</div>
      </div>

      <div class="card summary-card">
        <div class="summary-title">Candidates</div>
        <div class="summary-main">
          <div class="summary-number"><?= $stats['candidates'] ?></div>
          <span class="badge">Total</span>
        </div>
        <div class="summary-foot">Running this election</div>
      </div>
    </section>

    <?php if (!empty($positions)): ?>
      <div class="section-head" style="margin-top: 12px;">
        <div>
          <h2>Position Voting Overview</h2>
          <p>Scroll down to view the overall voting percentages and compare candidates per position.</p>
        </div>
      </div>

      <?php 
      foreach ($positions as $pos):
          $cands = $candidatesByPos[$pos['id']] ?? [];
          $posTotal = array_sum(array_column($cands, 'votes'));
          $pct = $stats['voters'] > 0 ? round($pos['vote_count']/$stats['voters']*100,1) : 0;
      ?>
      <div class="position-panel card">
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
          <div>
            <h3><?= htmlspecialchars($pos['title']) ?> Candidates</h3>
            <p><?= $pos['vote_count'] ?> total votes</p>
          </div>
          <div style="font-weight: bold; font-size: 0.95rem; color: var(--dark);"><?= $pct ?>% Turnout</div>
        </div>
        <div class="position-track">
          <div class="position-fill" style="width:<?= $pct ?>%"></div>
        </div>

        <div class="candidate-grid-wrap">
          <div class="candidate-grid">
            <?php foreach ($cands as $c):
                $cpct = $posTotal > 0 ? round($c['votes']/$posTotal*100,1) : 0;
                $photo = photoSrc($c['photo'] ?? '');
                
                // Adjusted calculation so that the bar height maxes out at the container height (220px) 
                // preventing overlap with the top card details.
                $barHeight = max(48, $cpct * 2.2); 
            ?>
            <div class="candidate-column">
              <div class="candidate-top">
                <div class="candidate-profile">
                  <?php if ($photo): ?>
                    <img src="<?= htmlspecialchars($photo) ?>" 
                         alt="<?= htmlspecialchars($c['name']) ?>" 
                         onerror="this.parentNode.innerHTML='<span><?= htmlspecialchars(initials($c['name'])) ?></span>'">
                  <?php else: ?>
                    <span><?= htmlspecialchars(initials($c['name'])) ?></span>
                  <?php endif; ?>
                </div>
                <div class="candidate-details">
                  <div class="candidate-name"><?= htmlspecialchars($c['name']) ?></div>
                  <div class="candidate-course"><?= htmlspecialchars($c['course']) ?></div>
                  <?php if ($c['partylist']): ?>
                      <div class="candidate-running"><?= htmlspecialchars($c['partylist']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="candidate-bar-zone">
                <div class="candidate-bar-area">
                  <div class="candidate-bar" style="height:<?= $barHeight ?>px">
                    <span class="bar-label"><strong><?= $c['votes'] ?></strong><?= $cpct ?>%</span>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($cands)): ?>
                <p style="color:var(--dark-soft); padding:30px; text-align:center; grid-column: 1 / -1;">No candidates registered for this position yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

    <?php endif; ?>

    <?php endif; ?>
    
  </main>

  <!-- GUEST AUTH MODAL -->
<div class="modal-overlay" id="authModal">
    <div class="modal-card">
        <h2>Access iVOTE CS</h2>
        <p>Choose how you want to proceed</p>
        <div class="modal-btns">
            <a href="/login.php" class="m-btn m-login">Log In</a>
            <a href="/login.php?view=register" class="m-btn m-register" onclick="sessionStorage.setItem('showRegister','1')">Register New Account</a>
        </div>
        <span class="close-link" onclick="closeAuthModal()">Close</span>
    </div>
</div>
  <script src="/assets/js/shared.js"></script>
</body>
</html>