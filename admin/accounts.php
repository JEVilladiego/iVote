<?php
// =============================================================
//  admin/accounts.php  (replaces AccountMan.php)
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// ---- DELETE (AJAX POST) ------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $raw    = file_get_contents('php://input');
    $data   = json_decode($raw, true);
    $userId = intval($data['userId'] ?? 0);
    if ($userId && $userId !== (int)$_SESSION['user_id']) {
        $db->prepare("DELETE FROM users WHERE id=? AND role='student'")->execute([$userId]);
        echo json_encode(['status'=>'success']); exit;
    }
    echo json_encode(['status'=>'error','message'=>'Invalid request.']); exit;
}

// ---- LOAD ACCOUNTS -----------------------------------------
$search     = trim($_GET['q']   ?? '');
$filterYear = trim($_GET['year'] ?? '');
$page       = max(1, intval($_GET['page'] ?? 1));
$perPage    = 10;
$offset     = ($page - 1) * $perPage;

$where  = ["role='student'"];
$params = [];
if ($search) {
    $where[]  = "(student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filterYear) {
    $where[]  = "year_level = ?";
    $params[] = $filterYear;
}
$whereSQL = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $whereSQL");
$countStmt->execute($params);
$total     = $countStmt->fetchColumn();
$pages     = ceil($total / $perPage);

$stmt = $db->prepare("SELECT * FROM users WHERE $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$accounts = $stmt->fetchAll();

// View-only detail via modal (AJAX GET)
if (isset($_GET['detail'])) {
    header('Content-Type: application/json');
    $uid = intval($_GET['detail']);
    $s   = $db->prepare("SELECT * FROM users WHERE id=? AND role='student'");
    $s->execute([$uid]);
    echo json_encode($s->fetch() ?: ['error'=>'Not found']);
    exit;
}

$navActive     = 'dashboard';
$sidebarActive = 'accounts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts | iVOTE CS</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        body { background:#f8fafc; padding-top:80px; }
        .page-wrap { display:flex; }
        .main { flex:1; margin-left:280px; padding:40px; }
        h2 { font-family:'Montserrat',sans-serif; color:#12341d; font-size:1.6rem; font-weight:800; margin-bottom:20px; }
        .filters { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:24px; }
        .filter-input {
            padding:10px 14px; border:1px solid #e2e8f0; border-radius:10px;
            font-size:14px; outline:none; transition:border 0.2s; font-family:'Geist',sans-serif;
        }
        .filter-input:focus { border-color:#33553e; }
        .filter-btn {
            padding:10px 20px; background:#12341d; color:#fff; border:none;
            border-radius:10px; font-weight:700; cursor:pointer; font-family:'Montserrat',sans-serif; font-size:13px;
        }

        /* Account cards */
        .accounts-list { display:flex; flex-direction:column; gap:14px; }
        .account-box {
            background:#fff; border-radius:14px; padding:20px 24px;
            border:1px solid #e2e8f0; display:flex; align-items:center;
            justify-content:space-between; gap:16px; transition:all 0.2s;
            box-shadow:0 2px 8px rgba(0,0,0,0.04);
        }
        .account-box:hover { transform:translateX(4px); box-shadow:0 6px 20px rgba(18,52,29,0.1); }
        .acc-info .sid   { font-weight:700; color:#12341d; font-size:0.92rem; }
        .acc-info .name  { font-size:1rem; font-weight:600; color:#0f172a; margin-top:2px; }
        .acc-info .meta  { font-size:0.8rem; color:#64748b; margin-top:2px; }
        .acc-status {
            display:inline-block; padding:4px 12px; border-radius:100px;
            font-size:0.72rem; font-weight:700; text-transform:uppercase;
        }
        .status-approved { background:#d1fae5; color:#065f46; }
        .status-pending  { background:#fef3c7; color:#92400e; }
        .status-rejected { background:#fee2e2; color:#991b1b; }
        .acc-actions { display:flex; gap:8px; flex-shrink:0; }
        .btn-view, .btn-del {
            width:36px; height:36px; border:none; border-radius:8px;
            cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:14px;
        }
        .btn-view { background:#d5e8db; color:#12341d; }
        .btn-del  { background:#fee2e2; color:#dc2626; }
        .btn-view:hover { background:#a4c1ad; }
        .btn-del:hover  { background:#dc2626; color:#fff; }

        /* Pagination */
        .pagination { display:flex; gap:8px; justify-content:center; margin-top:28px; flex-wrap:wrap; }
        .pg-btn {
            padding:8px 16px; border-radius:10px; border:1px solid #e2e8f0;
            background:#fff; cursor:pointer; font-weight:600; font-size:14px; transition:all 0.2s;
        }
        .pg-btn:hover, .pg-btn.active { background:#12341d; color:#fff; border-color:#12341d; }

        /* Modal */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); display:none; align-items:center; justify-content:center; z-index:9999; }
        .modal-overlay.open { display:flex; }
        .modal-box {
            background:#fff; border-radius:20px; padding:32px; width:400px; max-width:92vw;
            position:relative; animation:popIn 0.25s ease;
        }
        @keyframes popIn { from{transform:scale(0.9);opacity:0} to{transform:scale(1);opacity:1} }
        .modal-close { position:absolute; right:18px; top:14px; font-size:22px; cursor:pointer; color:#64748b; background:none; border:none; }
        .modal-box h3 { font-family:'Montserrat',sans-serif; color:#12341d; font-size:1.2rem; margin-bottom:18px; }
        .detail-row { padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:0.9rem; }
        .detail-row:last-child { border-bottom:none; }
        .detail-label { font-weight:700; color:#64748b; font-size:0.78rem; text-transform:uppercase; }
        .detail-val   { color:#0f172a; margin-top:2px; }
    </style>
</head>
<body>
<?php $navActive='dashboard'; require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-wrap">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="main">
        <h2>Voter Accounts</h2>
        <form method="GET" action="" class="filters">
            <input class="filter-input" name="q"    placeholder="Search name or ID..." value="<?= htmlspecialchars($search) ?>">
            <select class="filter-input" name="year">
                <option value="">All Year Levels</option>
                <?php foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $yr): ?>
                    <option <?= $filterYear===$yr?'selected':'' ?>><?= $yr ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="filter-btn">Search</button>
            <a href="/admin/accounts.php" style="padding:10px 18px;color:#33553e;font-weight:600;text-decoration:none;font-size:14px;display:flex;align-items:center">Clear</a>
        </form>

        <div class="accounts-list">
            <?php if (empty($accounts)): ?>
                <p style="color:#64748b;padding:30px 0;text-align:center">No accounts found.</p>
            <?php endif; ?>
            <?php foreach ($accounts as $a): ?>
            <div class="account-box" id="acc-<?= $a['id'] ?>">
                <div class="acc-info">
                    <div class="sid"><?= htmlspecialchars($a['student_id']) ?></div>
                    <div class="name"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div>
                    <div class="meta"><?= htmlspecialchars($a['course']) ?> · <?= htmlspecialchars($a['year_level']) ?></div>
                </div>
                <span class="acc-status status-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span>
                <div class="acc-actions">
                    <button class="btn-view" title="View details" onclick="viewAccount(<?= $a['id'] ?>)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-del" title="Delete account" onclick="deleteAccount(<?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['first_name'])) ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <a href="?q=<?= urlencode($search) ?>&year=<?= urlencode($filterYear) ?>&page=<?= $p ?>">
                    <button class="pg-btn <?= $p===$page?'active':'' ?>"><?= $p ?></button>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <p style="color:#64748b;font-size:0.83rem;text-align:center;margin-top:14px">
            Showing <?= count($accounts) ?> of <?= $total ?> accounts
        </p>
    </main>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h3>Voter Details</h3>
        <div id="modalContent"></div>
    </div>
</div>

<script src="/assets/js/shared.js"></script>
<script>
async function viewAccount(id) {
    const res  = await fetch(`/admin/accounts.php?detail=${id}`);
    const data = await res.json();
    if (data.error) return alert('Could not load details.');
    document.getElementById('modalContent').innerHTML = `
        <div class="detail-row"><div class="detail-label">Full Name</div><div class="detail-val">${data.first_name} ${data.last_name}</div></div>
        <div class="detail-row"><div class="detail-label">Student ID</div><div class="detail-val">${data.student_id}</div></div>
        <div class="detail-row"><div class="detail-label">Email</div><div class="detail-val">${data.email}</div></div>
        <div class="detail-row"><div class="detail-label">Course</div><div class="detail-val">${data.course}</div></div>
        <div class="detail-row"><div class="detail-label">Year Level</div><div class="detail-val">${data.year_level}</div></div>
        <div class="detail-row"><div class="detail-label">Status</div><div class="detail-val">${data.status}</div></div>
        <div class="detail-row"><div class="detail-label">Has Voted</div><div class="detail-val">${data.has_voted ? 'Yes ✅' : 'Not yet'}</div></div>
        <div class="detail-row"><div class="detail-label">Registered</div><div class="detail-val">${data.created_at}</div></div>
    `;
    document.getElementById('detailModal').classList.add('open');
}

function closeModal() { document.getElementById('detailModal').classList.remove('open'); }
document.getElementById('detailModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeModal(); });

async function deleteAccount(id, name) {
    if (!confirm(`Delete account for "${name}"? This cannot be undone.`)) return;
    const res    = await fetch('/admin/accounts.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({userId:id})
    });
    const result = await res.json();
    if (result.status === 'success') {
        const el = document.getElementById('acc-'+id);
        el.style.transition='opacity 0.3s'; el.style.opacity='0';
        setTimeout(() => el.remove(), 300);
    } else { alert('Error: ' + result.message); }
}
</script>
</body>
</html>
