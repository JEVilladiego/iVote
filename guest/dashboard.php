<?php
// =============================================================
//  guest/dashboard.php  — updated with print & unified design
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    return strtoupper(($parts[0][0] ?? '') . ($parts[1][0] ?? ''));
}
function photoSrc(?string $path): string {
    if (!$path) return '';
    return '/' . ltrim($path, '/');
}

// Active election (ongoing first, fallback to most recent)
$election = $db->query("SELECT * FROM elections WHERE status='ongoing' LIMIT 1")->fetch();
if (!$election) {
    $election = $db->query("SELECT * FROM elections ORDER BY start_date DESC LIMIT 1")->fetch();
}

$stats = ['voters' => 0, 'votes' => 0, 'candidates' => 0, 'positions' => 0];
$positions = [];
$candidatesByPos = [];

if ($election) {
    $stats['voters'] = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='approved'")->fetchColumn();
    $sv = $db->prepare("SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id=?");
    $sv->execute([$election['id']]);
    $stats['votes'] = $sv->fetchColumn();
    $sc = $db->prepare("SELECT COUNT(*) FROM candidates WHERE election_id=?");
    $sc->execute([$election['id']]);
    $stats['candidates'] = $sc->fetchColumn();
    $sp = $db->prepare("SELECT COUNT(*) FROM positions WHERE election_id=?");
    $sp->execute([$election['id']]);
    $stats['positions'] = $sp->fetchColumn();

    if (in_array($election['status'], ['ongoing', 'ended'])) {
        $pStmt = $db->prepare(
            "SELECT p.id, p.title, p.sort_order,
                    COUNT(v.id) AS vote_count
             FROM positions p
             LEFT JOIN votes v ON v.position_id = p.id AND v.election_id = ?
             GROUP BY p.id
             ORDER BY p.sort_order"
        );
        $pStmt->execute([$election['id']]);
        $positions = $pStmt->fetchAll();

        if (!empty($positions)) {
            $cStmt = $db->prepare(
                "SELECT c.*, COUNT(v.id) AS votes
                 FROM candidates c
                 LEFT JOIN votes v ON v.candidate_id = c.id
                 WHERE c.election_id = ?
                 GROUP BY c.id
                 ORDER BY c.position_id, votes DESC"
            );
            $cStmt->execute([$election['id']]);
            foreach ($cStmt->fetchAll() as $c) {
                $candidatesByPos[$c['position_id']][] = $c;
            }
        }
    }
}

$isOngoing = $election && $election['status'] === 'ongoing';
$isEnded   = $election && $election['status'] === 'ended';
$turnout   = $stats['voters'] > 0 ? round($stats['votes'] / $stats['voters'] * 100, 1) : 0;
$donutAngle = round($turnout / 100 * 360);

$navActive = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | iVOTE CS</title>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Montserrat:wght@500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/shared.css">
  <style>
    :root {
      --deep:        #12341d;
      --forest:      #33553e;
      --sage:        #6d9078;
      --mist:        #a4c1ad;
      --bg:          #d5e8db;
      --white:       #f7fbf8;
      --shadow:      0 18px 40px rgba(18,52,29,0.12);
      --shadow-soft: 0 8px 24px rgba(18,52,29,0.08);
      --radius-xl:   28px;
      --radius-lg:   20px;
      --radius-md:   14px;
      --transition:  180ms ease;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Geist', Arial, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(255,255,255,0.65), transparent 34%),
        linear-gradient(160deg, var(--bg), #cfe0d5 48%, #dcebe0 100%);
      color: var(--deep);
      min-height: 100vh;
      padding-top: 80px;
    }

    .page-shell {
      width: min(1200px, calc(100% - 32px));
      margin: 0 auto;
      padding: 28px 0 56px;
      display: grid;
      gap: 20px;
    }

    /* ── Hero banner ── */
    .hero-banner {
      background: rgba(247,251,248,0.78);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-soft);
      padding: 28px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      flex-wrap: wrap;
    }

    .hero-left { flex: 1; min-width: 260px; }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(109,144,120,0.14);
      color: var(--forest);
      border-radius: 999px;
      padding: 7px 14px;
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      margin-bottom: 14px;
    }

    .hero-left h1 {
      font-family: 'Montserrat', sans-serif;
      font-size: clamp(1.5rem, 1rem + 1.8vw, 2.2rem);
      font-weight: 900;
      line-height: 1.2;
      margin-bottom: 10px;
    }

    .hero-left p {
      color: rgba(18,52,29,0.72);
      font-size: 0.95rem;
      line-height: 1.65;
      max-width: 560px;
    }

    .hero-right {
      display: grid;
      grid-template-columns: repeat(2, minmax(130px, 1fr));
      gap: 12px;
      min-width: 290px;
    }

    .status-chip {
      background: rgba(255,255,255,0.78);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: var(--radius-md);
      padding: 16px;
      box-shadow: var(--shadow-soft);
    }

    .status-chip .chip-label {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: rgba(18,52,29,0.6);
      margin-bottom: 8px;
    }

    .status-chip .chip-value {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.92rem;
      font-weight: 800;
      color: var(--deep);
      line-height: 1.3;
    }

    .status-chip.accent {
      background: linear-gradient(145deg, var(--deep), var(--forest));
      border-color: transparent;
    }

    .status-chip.accent .chip-label { color: rgba(255,255,255,0.65); }
    .status-chip.accent .chip-value { color: #fff; }

    /* ── Countdown ── */
    .countdown-card {
      background: rgba(247,251,248,0.82);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-soft);
      padding: 24px 28px;
    }

    .countdown-card .section-label {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.88rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--forest);
      margin-bottom: 16px;
    }

    .cd-row { display: flex; gap: 12px; flex-wrap: wrap; }

    .cd-block {
      flex: 1; min-width: 72px;
      background: rgba(255,255,255,0.7);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: var(--radius-md);
      padding: 14px 10px;
      text-align: center;
      box-shadow: var(--shadow-soft);
    }

    .cd-num {
      font-family: 'Montserrat', sans-serif;
      font-size: 2rem; font-weight: 900;
      color: var(--deep); display: block; line-height: 1;
    }

    .cd-lbl {
      font-size: 0.65rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.1em;
      color: var(--sage); margin-top: 6px; display: block;
    }

    /* ── Stats row ── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
    }

    .stat-tile {
      background: rgba(247,251,248,0.82);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: var(--radius-lg);
      padding: 20px;
      box-shadow: var(--shadow-soft);
      transition: transform var(--transition), box-shadow var(--transition);
    }

    .stat-tile:hover { transform: translateY(-4px); box-shadow: var(--shadow); }

    .stat-tile .tile-label {
      font-size: 0.78rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.07em;
      color: var(--sage); margin-bottom: 10px;
    }

    .stat-tile .tile-value {
      font-family: 'Montserrat', sans-serif;
      font-size: 2rem; font-weight: 900;
      color: var(--deep); line-height: 1; margin-bottom: 6px;
    }

    .stat-tile .tile-sub { font-size: 0.8rem; color: rgba(18,52,29,0.6); }

    /* ── Ongoing donut card ── */
    .ongoing-wrap { display: grid; place-items: center; }

    .ongoing-card {
      width: 100%;
      background: rgba(247,251,248,0.82);
      backdrop-filter: blur(18px);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: 36px;
      box-shadow: var(--shadow);
      padding: 40px 32px;
      text-align: center;
    }

    .ongoing-card h2 {
      font-family: 'Montserrat', sans-serif;
      font-size: clamp(1.3rem, 1rem + 1.4vw, 2rem);
      font-weight: 800; line-height: 1.2;
      max-width: 680px; margin: 0 auto 12px;
    }

    .ongoing-card p {
      max-width: 620px; margin: 0 auto;
      font-size: 0.96rem; line-height: 1.7;
      color: rgba(18,52,29,0.72);
    }

    .session-meta { margin-top: 12px; font-size: 0.9rem; font-weight: 600; color: rgba(18,52,29,0.68); }

    .donut-panel { margin-top: 30px; display: grid; gap: 20px; place-items: center; }

    .donut-shell {
      position: relative;
      width: min(320px, 72vw);
      aspect-ratio: 1;
      display: grid; place-items: center;
    }

    .donut-chart {
      width: 100%; height: 100%; border-radius: 50%;
      background:
        radial-gradient(closest-side, rgba(247,251,248,1) 71%, transparent 72%),
        conic-gradient(var(--deep) 0deg <?= $donutAngle ?>deg, rgba(109,144,120,0.28) <?= $donutAngle ?>deg 360deg);
      box-shadow: inset 0 0 0 14px rgba(255,255,255,0.28), var(--shadow-soft);
    }

    .donut-center { position: absolute; inset: 0; display: grid; place-items: center; pointer-events: none; }
    .donut-center div { width: 55%; text-align: center; }
    .donut-center strong { font-family: 'Montserrat', sans-serif; font-size: clamp(1.8rem,1.2rem + 1.4vw,2.8rem); display: block; line-height: 1; margin-bottom: 6px; }
    .donut-center span { font-size: 0.8rem; color: rgba(18,52,29,0.68); font-weight: 600; }

    .legend { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 6px; }
    .legend-item { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.7); padding: 11px 16px; border-radius: 999px; border: 1px solid rgba(51,85,62,0.08); box-shadow: var(--shadow-soft); }
    .swatch { width: 12px; height: 12px; border-radius: 50%; flex: 0 0 auto; }
    .swatch.voted     { background: var(--deep); }
    .swatch.remaining { background: rgba(109,144,120,0.42); }
    .legend-label { font-size: 0.88rem; font-weight: 600; }
    .legend-value { font-size: 0.88rem; font-weight: 800; color: var(--forest); }

    /* ── Results / ended view ── */
    .results-card {
      background: rgba(247,251,248,0.82);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-soft);
      padding: 24px;
    }

    .card-head {
      display: flex; align-items: flex-start;
      justify-content: space-between;
      gap: 16px; margin-bottom: 20px; flex-wrap: wrap;
    }

    .card-title { font-family: 'Montserrat', sans-serif; font-size: 1.05rem; font-weight: 800; margin-bottom: 4px; }
    .card-desc { font-size: 0.9rem; color: rgba(18,52,29,0.7); line-height: 1.6; max-width: 700px; }

    .pill { padding: 8px 14px; border-radius: 999px; background: rgba(109,144,120,0.14); color: var(--forest); font-weight: 700; font-size: 0.8rem; white-space: nowrap; flex-shrink: 0; }

    .summary-figures { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-bottom: 18px; }

    .figure-card {
      background: linear-gradient(145deg, rgba(247,251,248,0.94), rgba(228,241,233,0.92));
      border: 1px solid rgba(51,85,62,0.08);
      border-radius: 22px; padding: 18px;
      box-shadow: var(--shadow-soft);
    }

    .figure-card .metric-sub { font-size: 0.8rem; color: rgba(18,52,29,0.68); margin-bottom: 4px; }
    .figure-card strong { font-family: 'Montserrat', sans-serif; font-size: clamp(1.8rem, 1.2rem + 1vw, 2.4rem); display: block; margin: 6px 0 4px; }
    .figure-card .mini-meta { font-size: 0.8rem; color: rgba(18,52,29,0.6); }

    .metric-sub { font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--forest); margin-bottom: 8px; }

    .progress-rail { height: 16px; background: rgba(109,144,120,0.18); border-radius: 999px; overflow: hidden; margin-top: 8px; margin-bottom: 8px; }
    .progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--forest), var(--sage), var(--mist)); }
    .section-hint { font-size: 0.88rem; color: rgba(18,52,29,0.65); }

    .section-tools { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

    .filter-select {
      min-width: 160px; padding: 10px 14px; border-radius: 12px;
      background: rgba(255,255,255,0.85); border: 1px solid rgba(51,85,62,0.1);
      color: var(--deep); font-weight: 600; cursor: pointer; font: inherit;
    }

    .ghost-btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      padding: 10px 16px; border-radius: 12px;
      background: rgba(18,52,29,0.07); color: var(--deep);
      font-weight: 700; cursor: pointer; font: inherit; border: none; outline: none;
      transition: var(--transition);
    }
    .ghost-btn:hover { background: rgba(18,52,29,0.13); }

    .positions-stack { display: grid; gap: 18px; max-height: 860px; overflow: auto; padding-right: 4px; scrollbar-width: thin; scrollbar-color: rgba(51,85,62,0.45) rgba(109,144,120,0.12); }

    .position-card {
      background: linear-gradient(180deg, rgba(255,255,255,0.78), rgba(247,251,248,0.92));
      border: 1px solid rgba(51,85,62,0.08);
      border-radius: 24px; padding: 18px; box-shadow: var(--shadow-soft); overflow: hidden;
    }

    .position-card h3 { font-family: 'Montserrat', sans-serif; font-size: 1rem; margin-bottom: 4px; }
    .position-card > p { font-size: 0.9rem; color: rgba(18,52,29,0.65); margin-bottom: 16px; }

    .candidate-grid {
      display: flex; gap: 16px;
      overflow-x: auto; overflow-y: hidden;
      padding: 8px 0 12px;
      scroll-snap-type: x proximity;
      scrollbar-width: thin;
      scrollbar-color: rgba(51,85,62,0.45) rgba(109,144,120,0.12);
    }

    .candidate-card {
      position: relative; flex: 0 0 300px; min-height: 230px;
      background: rgba(255,255,255,0.86);
      border: 1px solid rgba(51,85,62,0.08); border-radius: 16px;
      padding: 18px 18px 18px 82px;
      box-shadow: var(--shadow-soft); isolation: isolate; scroll-snap-align: start;
    }

    .rank-display {
      position: absolute; left: 10px; top: 10px; bottom: 10px;
      width: 66px; display: flex; align-items: center; justify-content: center;
      pointer-events: none; z-index: 0;
    }

    .rank-number {
      font-family: 'Montserrat', sans-serif;
      font-size: 7rem; line-height: 0.8; font-weight: 800; letter-spacing: -0.08em;
      color: transparent; -webkit-text-stroke: 2.5px rgba(18,52,29,0.3); transform: translateX(-4px);
    }

    .rank-suffix { position: absolute; right: 2px; top: 16px; font-family: 'Montserrat', sans-serif; font-size: 1rem; font-weight: 800; color: rgba(18,52,29,0.45); }
    .candidate-card > *:not(.rank-display) { position: relative; z-index: 1; }

    .candidate-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
    .candidate-profile { display: flex; align-items: center; gap: 12px; min-width: 0; }

    .candidate-avatar {
      width: 64px; height: 64px; border-radius: 16px; object-fit: cover; flex-shrink: 0;
      background: linear-gradient(150deg, var(--mist), var(--sage));
      border: 2px solid rgba(255,255,255,0.9); box-shadow: var(--shadow-soft);
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; color: var(--deep); font-size: 1.1rem; overflow: hidden;
    }
    .candidate-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .candidate-name { font-weight: 800; font-size: 0.94rem; line-height: 1.25; margin-bottom: 3px; }
    .candidate-position { font-size: 0.78rem; color: rgba(18,52,29,0.62); line-height: 1.3; }

    .vote-values { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
    .vote-values .v-label { font-size: 0.78rem; color: rgba(18,52,29,0.6); }
    .vote-values strong { font-family: 'Montserrat', sans-serif; font-size: 1.45rem; line-height: 1; }

    .vote-bar { height: 16px; background: rgba(109,144,120,0.16); border-radius: 6px; overflow: hidden; margin-bottom: 8px; }
    .vote-bar span { display: block; height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--deep), var(--forest), var(--sage)); }

    .candidate-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 0.82rem; font-weight: 600; margin-top: 6px; }

    /* ── Print actions (new for guest) ── */
    .print-actions {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-top: 4px;
    }

    .print-card {
      border-radius: 24px; padding: 22px;
      background: linear-gradient(155deg, rgba(18,52,29,0.96), rgba(51,85,62,0.9));
      color: var(--white); box-shadow: var(--shadow-soft);
    }

    .print-card.alt {
      background: linear-gradient(155deg, rgba(109,144,120,0.22), rgba(51,85,62,0.10));
      color: var(--deep); border: 1px solid rgba(51,85,62,0.1);
    }

    .print-card h3 { font-family: 'Montserrat', sans-serif; font-size: 1rem; font-weight: 800; margin-bottom: 8px; }
    .print-card p { font-size: 0.9rem; line-height: 1.65; margin-bottom: 16px; opacity: 0.9; }

    .print-btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      padding: 11px 18px; border-radius: 14px;
      background: var(--white); color: var(--deep);
      font-weight: 700; font-size: 0.9rem; cursor: pointer;
      border: none; outline: none; font: inherit; transition: var(--transition);
    }
    .print-btn:hover { opacity: 0.88; transform: translateY(-1px); }
    .print-card.alt .print-btn { background: var(--deep); color: var(--white); }

    .print-note { font-size: 0.82rem; margin-top: 10px; opacity: 0.75; }

    /* ── Empty / no election state ── */
    .empty-state {
      background: rgba(247,251,248,0.78);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(51,85,62,0.1);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-soft);
      padding: 64px 32px; text-align: center;
    }

    .empty-icon { font-size: 3.5rem; margin-bottom: 22px; }
    .empty-state h2 { font-family: 'Montserrat', sans-serif; font-size: 1.6rem; font-weight: 800; margin-bottom: 12px; }
    .empty-state p { color: rgba(18,52,29,0.65); font-size: 0.96rem; max-width: 440px; margin: 0 auto; line-height: 1.7; }

    /* ── Auth modal ── */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(18,52,29,0.55); backdrop-filter:blur(8px); z-index:200; align-items:center; justify-content:center; }
    .modal-overlay.show { display:flex; }
    .modal-card { background:#fff; border-radius:24px; padding:36px; max-width:400px; width:100%; text-align:center; box-shadow:0 24px 60px rgba(0,0,0,0.2); }
    .modal-card h2 { font-family:'Montserrat',sans-serif; font-size:1.5rem; font-weight:900; color:var(--deep); margin-bottom:8px; }
    .modal-card p { color:rgba(18,52,29,0.65); margin-bottom:24px; }
    .modal-btns { display:grid; gap:10px; margin-bottom:18px; }
    .m-btn { display:block; padding:14px; border-radius:14px; font-weight:800; font-size:0.95rem; text-decoration:none; font-family:'Montserrat',sans-serif; }
    .m-login { background:var(--deep); color:#fff; }
    .m-register { background:var(--bg); color:var(--deep); }
    .close-link { font-size:0.88rem; color:var(--sage); cursor:pointer; }

    @media (max-width: 900px) { .stats-row { grid-template-columns: repeat(2,1fr); } .print-actions { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
      .hero-banner { flex-direction: column; }
      .hero-right { grid-template-columns: repeat(2,1fr); min-width: unset; width: 100%; }
      .stats-row { grid-template-columns: repeat(2,1fr); }
      .summary-figures { grid-template-columns: 1fr; }
      .ongoing-card { padding: 28px 20px; }
    }

    /* ── Print styles ── */
    @media print {
      body { background: #fff !important; padding-top: 0; }
      .hero-banner, .countdown-card, .stats-row, .ongoing-wrap,
      .section-tools, .ghost-btn, .filter-select,
      .print-actions, .modal-overlay { display: none !important; }

      /* Show only elements with print-target class */
      .print-hidden { display: none !important; }
      .print-target { display: block !important; }

      .page-shell { width: 100%; padding: 16px; gap: 12px; }
      .results-card { box-shadow: none; border: 1px solid #ccc; border-radius: 0; background: #fff; padding: 14px; }
      .summary-figures { grid-template-columns: 1fr 1fr; }
      .figure-card { border: 1px solid #aaa; border-radius: 0; box-shadow: none; background: #fff; }
      .positions-stack { max-height: none; overflow: visible; }
      .position-card { page-break-inside: avoid; break-inside: avoid; border: 1px solid #888; border-radius: 0; box-shadow: none; background: #fff; }
      .candidate-grid { display: block; overflow: visible; }
      .candidate-card { display: grid; grid-template-columns: 64px 1fr; gap: 10px; min-height: auto; width: 100%; border: 1px solid #aaa; border-radius: 0; box-shadow: none; background: #fff; padding: 10px; margin-bottom: 8px; flex: unset; }
      .rank-display { position: static; width: 64px; min-height: 80px; }
      .rank-number { font-size: 4rem; -webkit-text-stroke: 1.5px rgba(0,0,0,0.4); }

      /* Print header */
      .print-header { display: block !important; padding: 12px 0 18px; border-bottom: 2px solid #12341d; margin-bottom: 16px; }
      .print-header h2 { font-family: 'Montserrat', sans-serif; font-size: 1.3rem; color: #12341d; }
      .print-header p { font-size: 0.85rem; color: #555; margin-top: 4px; }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="page-shell">

    <?php if (!$election): ?>
    <!-- ── No election state ── -->
    <div class="empty-state">
      <div class="empty-icon">🗳️</div>
      <h2>No Active Election</h2>
      <p>There is currently no ongoing or scheduled election. The dashboard will update once an election is created. Please check back later.</p>
    </div>

    <?php else: ?>

    <!-- ── Hero banner ── -->
    <div class="hero-banner print-hidden">
      <div class="hero-left">
        <div class="eyebrow">
          <?php if ($isOngoing): ?>🟢 Election Ongoing
          <?php elseif ($isEnded): ?>🏁 Election Ended
          <?php else: ?>📅 Election Upcoming<?php endif; ?>
        </div>
        <h1><?= htmlspecialchars($election['title']) ?></h1>
        <p>
          <?= date('M d, Y', strtotime($election['start_date'])) ?> &ndash;
          <?= date('M d, Y', strtotime($election['end_date'])) ?>
          &nbsp;&nbsp;|&nbsp;&nbsp; You are viewing as a <strong>Guest</strong>.
        </p>
      </div>

      <div class="hero-right">
        <div class="status-chip accent">
          <div class="chip-label">Status</div>
          <div class="chip-value"><?= ucfirst($election['status']) ?></div>
        </div>
        <div class="status-chip">
          <div class="chip-label">Voter Turnout</div>
          <div class="chip-value"><?= $turnout ?>%</div>
        </div>
        <div class="status-chip">
          <div class="chip-label">Votes Cast</div>
          <div class="chip-value"><?= number_format($stats['votes']) ?></div>
        </div>
        <div class="status-chip">
          <div class="chip-label">View</div>
          <div class="chip-value">Guest</div>
        </div>
      </div>
    </div>

    <?php if ($isOngoing): ?>
    <!-- ── Countdown ── -->
    <div class="countdown-card print-hidden">
      <div class="section-label">⏳ Time Remaining Until Election Closes</div>
      <div class="cd-row" id="countdown">
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Days</span></div>
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Hours</span></div>
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Mins</span></div>
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Secs</span></div>
      </div>
    </div>

    <!-- ── Stats row ── -->
    <div class="stats-row print-hidden">
      <div class="stat-tile">
        <div class="tile-label">Registered Voters</div>
        <div class="tile-value"><?= number_format($stats['voters']) ?></div>
        <div class="tile-sub">Eligible to vote</div>
      </div>
      <div class="stat-tile">
        <div class="tile-label">Votes Cast</div>
        <div class="tile-value"><?= number_format($stats['votes']) ?></div>
        <div class="tile-sub"><?= $turnout ?>% turnout</div>
      </div>
      <div class="stat-tile">
        <div class="tile-label">Positions</div>
        <div class="tile-value"><?= $stats['positions'] ?></div>
        <div class="tile-sub">Open seats</div>
      </div>
      <div class="stat-tile">
        <div class="tile-label">Candidates</div>
        <div class="tile-value"><?= $stats['candidates'] ?></div>
        <div class="tile-sub">Running this election</div>
      </div>
    </div>

    <!-- ── Ongoing donut ── -->
    <div class="ongoing-wrap print-hidden">
      <div class="ongoing-card">
        <div class="eyebrow">Election Session in Progress</div>
        <h2>Voting is currently open. Results will be revealed once the session ends.</h2>
        <p>The dashboard shows live participation progress while the election is active. Final candidate rankings and official vote summaries will be published once voting closes.</p>
        <div class="session-meta"><?= htmlspecialchars($election['title']) ?></div>

        <div class="donut-panel">
          <div class="donut-shell">
            <div class="donut-chart"></div>
            <div class="donut-center">
              <div>
                <strong><?= $turnout ?>%</strong>
                <span>Overall turnout</span>
              </div>
            </div>
          </div>
          <div class="legend">
            <div class="legend-item">
              <span class="swatch voted"></span>
              <span class="legend-label">Already Voted</span>
              <span class="legend-value"><?= number_format($stats['votes']) ?></span>
            </div>
            <div class="legend-item">
              <span class="swatch remaining"></span>
              <span class="legend-label">Eligible Voters</span>
              <span class="legend-value"><?= number_format($stats['voters']) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php elseif ($isEnded): ?>
    <!-- ── Ended: full results ── -->

    <!-- Print header (only shows on print) -->
    <div class="print-header" style="display:none;">
      <h2>iVOTE CS — Official Election Results</h2>
      <p><?= htmlspecialchars($election['title']) ?> &nbsp;|&nbsp; Printed on <?= date('F d, Y') ?> &nbsp;|&nbsp; Guest View</p>
    </div>

    <!-- Election summary card -->
    <div class="results-card">
      <div class="card-head">
        <div>
          <div class="card-title">Election Summary</div>
          <div class="card-desc">Official public results for the completed voting session, including participation count and candidate rankings.</div>
        </div>
        <div class="pill"><?= htmlspecialchars($election['title']) ?></div>
      </div>

      <div class="summary-figures">
        <div class="figure-card">
          <div class="metric-sub">Eligible Registered Voters</div>
          <strong><?= number_format($stats['voters']) ?></strong>
          <div class="mini-meta">Students qualified to vote</div>
        </div>
        <div class="figure-card">
          <div class="metric-sub">Votes Submitted</div>
          <strong><?= number_format($stats['votes']) ?></strong>
          <div class="mini-meta">Recorded ballots</div>
        </div>
      </div>

      <div class="metric-sub">Voter Turnout</div>
      <div class="progress-rail">
        <div class="progress-fill" style="width:<?= $turnout ?>%"></div>
      </div>
      <p class="section-hint"><?= $turnout ?>% of eligible voters participated in this election.</p>
    </div>

    <!-- Position results card -->
    <?php if (!empty($positions)): ?>
    <div class="results-card">
      <div class="card-head">
        <div>
          <div class="card-title">Position Results</div>
          <div class="card-desc">Use the dropdown to filter by position or view all. Each row is horizontally scrollable.</div>
        </div>
        <div class="section-tools print-hidden">
          <select class="filter-select" id="positionFilter" aria-label="Filter by position">
            <option value="all">All Positions</option>
            <?php foreach ($positions as $pos): ?>
            <option value="pos-<?= $pos['id'] ?>"><?= htmlspecialchars($pos['title']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="ghost-btn" id="expandAllBtn" type="button">Show All</button>
        </div>
      </div>

      <div class="positions-stack" id="positionsStack">
        <?php foreach ($positions as $pos):
          $cands   = $candidatesByPos[$pos['id']] ?? [];
          $posTotal = array_sum(array_column($cands, 'votes'));
          $suffixes = ['st','nd','rd'];
        ?>
        <div class="position-card" data-pos="pos-<?= $pos['id'] ?>">
          <h3><?= htmlspecialchars($pos['title']) ?></h3>
          <p><?= $pos['vote_count'] ?> total vote<?= $pos['vote_count'] != 1 ? 's' : '' ?> cast for this position</p>
          <div class="candidate-grid">
            <?php foreach ($cands as $rank => $c):
              $cpct   = $posTotal > 0 ? round($c['votes'] / $posTotal * 100, 1) : 0;
              $photo  = photoSrc($c['photo'] ?? '');
              $rankN  = $rank + 1;
              $suffix = $suffixes[min($rankN - 1, 2)] ?? 'th';
            ?>
            <div class="candidate-card">
              <div class="rank-display">
                <span class="rank-number"><?= $rankN ?></span>
                <span class="rank-suffix"><?= $suffix ?></span>
              </div>
              <div class="candidate-top">
                <div class="candidate-profile">
                  <div class="candidate-avatar">
                    <?php if ($photo): ?>
                      <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($c['name']) ?>" onerror="this.style.display='none';this.parentNode.innerHTML+='<?= htmlspecialchars(initials($c['name'])) ?>'">
                    <?php else: ?>
                      <?= htmlspecialchars(initials($c['name'])) ?>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div class="candidate-name"><?= htmlspecialchars($c['name']) ?></div>
                    <div class="candidate-position"><?= htmlspecialchars($c['course']) ?><?= $c['partylist'] ? ' &bull; ' . htmlspecialchars($c['partylist']) : '' ?></div>
                  </div>
                </div>
              </div>
              <div class="vote-values">
                <span class="v-label">Votes</span>
                <strong><?= $c['votes'] ?></strong>
              </div>
              <div class="vote-bar"><span style="width:<?= $cpct ?>%"></span></div>
              <div class="candidate-foot">
                <span><?= $cpct ?>%</span>
                <span><?= $rank === 0 ? '🏆 Leading' : 'Candidate' ?></span>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($cands)): ?>
              <p style="padding:24px;color:rgba(18,52,29,0.5)">No candidates registered.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── Printable documents section ── -->
    <div class="results-card print-hidden">
      <div class="card-head">
        <div>
          <div class="card-title">Printable Result Documents</div>
          <div class="card-desc">Download or print a copy of the official election results. Guest view shows only public officer results without personal vote data.</div>
        </div>
      </div>
      <div class="print-actions">
        <div class="print-card">
          <h3>🏆 Print Elected Officers Summary</h3>
          <p>Generate a clean, ready-to-print results sheet showing the top-ranked candidate (elected officer) for each position in this completed election.</p>
          <button class="print-btn" type="button" onclick="printWinners()">Print Officers Summary</button>
          <div class="print-note">The printout contains elected officer names, their positions, vote counts, and overall turnout statistics.</div>
        </div>
        <div class="print-card alt">
          <h3>📄 Print Full Results</h3>
          <p>Print the complete results page including all candidates ranked by votes for every position, plus the participation summary.</p>
          <button class="print-btn" type="button" onclick="printFull()">Print Full Results</button>
          <div class="print-note">Includes all candidate rankings per position and voter turnout. To view personal vote summaries, please log in as a student.</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- ── Upcoming ── -->
    <div class="countdown-card print-hidden">
      <div class="section-label">📅 Election Opens In</div>
      <div class="cd-row" id="countdown">
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Days</span></div>
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Hours</span></div>
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Mins</span></div>
        <div class="cd-block"><span class="cd-num">--</span><span class="cd-lbl">Secs</span></div>
      </div>
    </div>

    <div class="ongoing-wrap print-hidden">
      <div class="ongoing-card">
        <div class="eyebrow">📅 Upcoming Election</div>
        <h2>The voting session hasn't started yet.</h2>
        <p>This election is scheduled to open on <?= date('F d, Y \a\t g:ia', strtotime($election['start_date'])) ?>. Check back once voting begins to see live participation data.</p>
        <div class="session-meta"><?= htmlspecialchars($election['title']) ?></div>
      </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>

  </main>

  <!-- GUEST AUTH MODAL -->
  <div class="modal-overlay" id="authModal">
    <div class="modal-card">
      <h2>Access iVOTE CS</h2>
      <p>Log in or register to cast your vote.</p>
      <div class="modal-btns">
        <a href="/login.php" class="m-btn m-login">Log In</a>
        <a href="/login.php?view=register" class="m-btn m-register" onclick="sessionStorage.setItem('showRegister','1')">Register New Account</a>
      </div>
      <span class="close-link" onclick="closeAuthModal()">Close</span>
    </div>
  </div>

  <!-- PRINT-ONLY: Winners Summary — formatted to match official results document -->
  <?php if ($isEnded && !empty($positions)): ?>
  <div id="winnersPrintSection" style="display:none;">
    <div class="doc-header">
      <img class="doc-logo" src="/assets/img/icons/logo.png" alt="Logo" onerror="this.style.display='none'">
      <p class="doc-orgname">College of Science Association (COSA)</p>
      <p class="doc-elec-title"><?= htmlspecialchars($election['title']) ?> — Official Results</p>
      <p class="doc-meta">
        Election Period:
        <?= date('F d, Y', strtotime($election['start_date'])) ?>
        &nbsp;–&nbsp;
        <?= date('F d, Y', strtotime($election['end_date'])) ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Document Generated: <?= date('F d, Y') ?>
      </p>
    </div>
    <div class="doc-certified">
      ✅ &nbsp;The following candidates have been duly ELECTED as Officers based on the final vote tally.
    </div>
    <table class="doc-table">
      <thead>
        <tr>
          <th style="width:5%">#</th>
          <th style="width:30%">Position</th>
          <th style="width:36%">Elected Officer</th>
          <th style="width:29%">Course / Student ID</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ($positions as $pos):
          $topCand = $candidatesByPos[$pos['id']][0] ?? null;
          if (!$topCand) continue;
        ?>
        <tr>
          <td class="td-num"><?= $i++ ?></td>
          <td class="td-pos"><?= htmlspecialchars($pos['title']) ?></td>
          <td class="td-name"><?= htmlspecialchars($topCand['name']) ?><span class="td-badge">ELECTED</span></td>
          <td class="td-course">
            <?= htmlspecialchars($topCand['course']) ?>
            <span class="td-sid"><?= htmlspecialchars($topCand['student_id'] ?? '') ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="sig-section">
      <div class="sig-title">Certified and Attested by:</div>
      <div class="sig-row">
        <div class="sig-block"><div class="sig-line"></div><div class="sig-label">COMELEC Chairperson</div></div>
        <div class="sig-block"><div class="sig-line"></div><div class="sig-label">College Dean / Adviser</div></div>
        <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Date Certified</div></div>
      </div>
    </div>
    <div class="doc-footer">
      <span>College of Science Association (COSA)</span>
      <span>System-generated by iVOTE CS &nbsp;|&nbsp; Confidential</span>
      <span>Page 1 of 1</span>
    </div>
  </div>
  <?php endif; ?>

  <script src="/assets/js/shared.js"></script>
  <script>
  <?php if ($election && in_array($election['status'], ['ongoing','upcoming'])): ?>
  (function () {
    const target = new Date("<?= addslashes($election['status'] === 'ongoing' ? $election['end_date'] : $election['start_date']) ?>").getTime();
    const blocks = document.querySelectorAll('#countdown .cd-block');
    if (!blocks.length) return;
    function tick() {
      const diff = target - Date.now();
      if (diff <= 0) { blocks.forEach(b => b.querySelector('.cd-num').textContent = '00'); return; }
      const parts = [
        Math.floor(diff / 86400000),
        Math.floor(diff % 86400000 / 3600000),
        Math.floor(diff % 3600000  / 60000),
        Math.floor(diff % 60000    / 1000)
      ];
      blocks.forEach((b, i) => { b.querySelector('.cd-num').textContent = String(parts[i]).padStart(2,'0'); });
    }
    tick();
    setInterval(tick, 1000);
  })();
  <?php endif; ?>

  <?php if ($isEnded && !empty($positions)): ?>
  // Position filter
  const filterSel = document.getElementById('positionFilter');
  const posCards  = document.querySelectorAll('.position-card[data-pos]');
  const expandBtn = document.getElementById('expandAllBtn');

  function filterPositions() {
    const val = filterSel.value;
    posCards.forEach(c => { c.style.display = (val === 'all' || c.dataset.pos === val) ? '' : 'none'; });
  }

  if (filterSel) filterSel.addEventListener('change', filterPositions);
  if (expandBtn) expandBtn.addEventListener('click', () => { filterSel.value = 'all'; filterPositions(); });

  // ── Print: Winners Only ──
  function printWinners() {
    const section = document.getElementById('winnersPrintSection');
    if (!section) return;
    const win = window.open('', '_blank', 'width=900,height=700');
    win.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Elected Officers — iVOTE CS</title>
      <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;600;700&family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Geist', Arial, sans-serif; background: #fff; color: #0f172a; padding: 18mm; }
        @page { size: A4 portrait; margin: 14mm 18mm 20mm; }
        @media print { body { padding: 0; } }

        .doc-header { text-align: center; padding-bottom: 14px; margin-bottom: 18px; border-bottom: 2.5px solid #12341d; }
        .doc-logo { width: 76px; height: 76px; object-fit: contain; display: block; margin: 0 auto 10px; }
        .doc-orgname { font-family: 'Montserrat', Arial, sans-serif; font-size: 14pt; font-weight: 900; color: #12341d; margin: 0 0 2px; text-transform: uppercase; letter-spacing: 0.6px; }
        .doc-elec-title { font-family: 'Montserrat', Arial, sans-serif; font-size: 10.5pt; font-weight: 800; color: #33553e; margin: 0 0 5px; }
        .doc-meta { font-size: 7.5pt; color: #64748b; margin: 0; }

        .doc-certified { background: #f0fdf4; border: 1.5px solid #6ee7b7; border-radius: 6px; padding: 7px 14px; margin: 14px 0 18px; text-align: center; font-size: 8.5pt; color: #065f46; font-family: 'Montserrat', Arial, sans-serif; font-weight: 700; }

        .doc-table { width: 100%; border-collapse: collapse; }
        .doc-table thead tr { border-bottom: 1.5px solid #12341d; }
        .doc-table th { color: #64748b; font-family: 'Montserrat', Arial, sans-serif; font-size: 7.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; padding: 7px 10px; text-align: left; }
        .doc-table td { padding: 9px 10px; font-size: 9pt; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .doc-table tbody tr:last-child td { border-bottom: none; }
        .td-num { color: #94a3b8; font-size: 8pt; }
        .td-pos { font-family: 'Montserrat', Arial, sans-serif; font-weight: 800; color: #0f172a; font-size: 9pt; }
        .td-name { font-weight: 600; font-size: 9pt; }
        .td-badge { display: inline-block; background: #d1fae5; color: #065f46; border-radius: 20px; padding: 2px 9px; font-size: 6.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px; vertical-align: middle; margin-left: 6px; font-family: 'Montserrat', Arial, sans-serif; }
        .td-course { font-size: 8.5pt; color: #0f172a; }
        .td-sid { font-size: 7.5pt; color: #94a3b8; display: block; margin-top: 1px; }

        .sig-section { margin-top: 40px; }
        .sig-title { font-family: 'Montserrat', Arial, sans-serif; font-weight: 800; font-size: 8pt; color: #12341d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 32px; }
        .sig-row { display: flex; justify-content: space-around; gap: 20px; }
        .sig-block { flex: 1; text-align: center; }
        .sig-line { border-top: 1px solid #0f172a; margin-top: 44px; padding-top: 5px; }
        .sig-label { font-family: 'Montserrat', Arial, sans-serif; font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.3px; }

        .doc-footer { position: fixed; bottom: 0; left: 0; right: 0; display: flex; justify-content: space-between; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding: 5px 18mm 3px; background: #fff; }
      </style>
    </head><body>`);
    win.document.write(section.innerHTML);
    win.document.write(`</body></html>`);
    win.document.close();
    win.onload = () => { win.print(); };
  }

  // ── Print: Full Results ──
  function printFull() {
    window.print();
  }
  <?php endif; ?>
  </script>
</body>
</html>