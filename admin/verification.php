<?php
// =============================================================
//  admin/verification.php
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// ---- HANDLE APPROVE / REJECT (AJAX POST) -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $raw    = file_get_contents('php://input');
    $data   = json_decode($raw, true);
    $userId = intval($data['userId']  ?? 0);
    $action = $data['action'] ?? '';

    if (!$userId || !in_array($action, ['approve','reject'], true)) {
        echo json_encode(['status'=>'error','message'=>'Invalid request.']); exit;
    }

    $newStatus = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $db->prepare(
        "UPDATE users SET status=?, verified_at=NOW(), verified_by=? WHERE id=? AND role='student'"
    );
    $stmt->execute([$newStatus, $_SESSION['user_id'], $userId]);

    // Fetch student details for email
    $stu = $db->prepare("SELECT first_name, last_name, email, student_id FROM users WHERE id=?");
    $stu->execute([$userId]);
    $student = $stu->fetch();

    if ($student) {
        $name    = $student['first_name'] . ' ' . $student['last_name'];
        $sid     = $student['student_id'];
        $to      = $student['email'];
        $subject = "iVOTE CS — Registration " . ucfirst($newStatus);
        $headers = "From: admin@ivote.edu.ph\r\nContent-Type: text/html; charset=UTF-8\r\n";

        if ($action === 'approve') {
            $body = "<h3>Registration Approved ✅</h3>
                     <p>Dear {$name}, your registration for the COS Online Voting System has been <strong>approved</strong>.
                     You may now log in with your Student ID (<b>{$sid}</b>) and cast your vote during the election period.</p>";
        } else {
            $body = "<h3>Registration Rejected ❌</h3>
                     <p>Dear {$name}, your registration was <strong>rejected</strong>. Please log in to your account and re-upload a valid COR, or visit the admin office.</p>";
        }
        @mail($to, $subject, $body, $headers);
    }

    echo json_encode(['status'=>'success','message'=>"Student $newStatus."]);
    exit;
}

// ---- LOAD PENDING STUDENTS ---------------------------------
$pending = $db->query(
    "SELECT id, student_id, first_name, last_name, course, year_level, email, id_document, created_at
     FROM users WHERE role='student' AND status='pending'
     ORDER BY created_at ASC"
)->fetchAll();

$navActive     = 'dashboard';
$sidebarActive = 'verification';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate Accounts | iVOTE CS</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        body { background:linear-gradient(135deg,#33553e 0%,#b8d1c0 100%); background-attachment:fixed; padding-top:80px; }
        .page-wrap { display:flex; }
        .main { flex:1; margin-left:280px; padding:40px; min-height:calc(100vh - 80px); }
        h2 { font-family:'Montserrat',sans-serif; color:#fff; font-size:1.6rem; font-weight:800; margin-bottom:6px; }
        .badge-count {
            display:inline-block; padding:5px 14px; border-radius:100px;
            background:#d1fae5; color:#065f46; font-weight:700; font-size:0.85rem; margin-left:12px;
        }

        /* Cards grid instead of table */
        .cards-grid {
            display:grid; grid-template-columns:repeat(auto-fill, minmax(420px, 1fr));
            gap:20px; margin-top:24px;
        }

        .student-card {
            background:#fff; border-radius:20px; overflow:hidden;
            box-shadow:0 4px 20px rgba(0,0,0,0.08);
            transition:transform 0.2s, box-shadow 0.2s;
        }
        .student-card:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,0,0,0.12); }

        /* COR preview strip */
        .cor-preview {
            width:100%; height:180px; background:#f1f5f9;
            display:flex; align-items:center; justify-content:center;
            position:relative; overflow:hidden; cursor:pointer;
        }
        .cor-preview img {
            width:100%; height:100%; object-fit:cover; transition:transform 0.3s;
        }
        .cor-preview:hover img { transform:scale(1.03); }
        .cor-preview .no-doc {
            text-align:center; color:#94a3b8;
        }
        .cor-preview .no-doc .no-doc-icon { font-size:2.5rem; display:block; margin-bottom:8px; }
        .cor-preview .no-doc span { font-size:0.8rem; font-weight:600; }

        .cor-preview .view-badge {
            position:absolute; bottom:10px; right:10px;
            background:rgba(0,0,0,0.55); color:#fff; font-size:0.72rem;
            font-weight:700; padding:4px 10px; border-radius:20px;
            backdrop-filter:blur(4px);
        }
        .cor-preview .pdf-icon {
            font-size:3rem; color:#ef4444;
        }

        /* Card body */
        .card-body { padding:20px 22px; }
        .student-name { font-family:'Montserrat',sans-serif; font-size:1rem; font-weight:800; color:#0f172a; margin-bottom:2px; }
        .student-meta { font-size:0.82rem; color:#64748b; margin-bottom:12px; }
        .student-meta span { margin-right:12px; }

        .detail-row {
            display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;
        }
        .detail-chip {
            background:#f1f5f9; color:#334155; border-radius:8px;
            padding:4px 10px; font-size:0.78rem; font-weight:600;
        }
        .detail-chip.highlight { background:#d1fae5; color:#065f46; }

        .action-buttons { display:flex; gap:10px; }
        .btn-approve, .btn-reject {
            flex:1; padding:10px; border:none; border-radius:10px;
            font-family:'Montserrat',sans-serif; font-size:0.85rem; font-weight:700;
            cursor:pointer; transition:all 0.2s;
        }
        .btn-approve { background:#10b981; color:#fff; }
        .btn-approve:hover { background:#059669; transform:translateY(-1px); }
        .btn-reject  { background:#fff; color:#ef4444; border:1.5px solid #ef4444; }
        .btn-reject:hover  { background:#ef4444; color:#fff; transform:translateY(-1px); }

        .no-doc-warn {
            background:#fef3c7; color:#92400e; border-radius:8px;
            padding:6px 10px; font-size:0.78rem; font-weight:700; margin-bottom:12px;
        }

        .empty-state { padding:80px 20px; text-align:center; color:rgba(255,255,255,0.8); }
        .empty-state .icon { font-size:3.5rem; margin-bottom:12px; display:block; }
        .empty-state p { font-family:'Montserrat',sans-serif; font-size:1.1rem; font-weight:700; }

        .card-removing { opacity:0; transform:scale(0.9); transition:all 0.4s ease-out; pointer-events:none; }

        /* Modal for image preview */
        .modal-overlay {
            display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75);
            z-index:9999; align-items:center; justify-content:center;
            backdrop-filter:blur(4px);
        }
        .modal-overlay.open { display:flex; }
        .modal-content {
            background:#fff; border-radius:20px; overflow:hidden;
            max-width:90vw; max-height:90vh; position:relative;
            box-shadow:0 25px 60px rgba(0,0,0,0.4);
        }
        .modal-content img { display:block; max-width:80vw; max-height:80vh; object-fit:contain; }
        .modal-close {
            position:absolute; top:12px; right:14px; background:#0f172a; color:#fff;
            border:none; border-radius:50%; width:32px; height:32px; font-size:1.1rem;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
        }
        .modal-name { padding:12px 20px; font-family:'Montserrat',sans-serif; font-weight:700; font-size:0.9rem; color:#0f172a; background:#f8fafc; }

        /* Toast */
        .toast-container { position:fixed; bottom:24px; right:24px; display:flex; flex-direction:column; gap:10px; z-index:9999; }
        .toast {
            background:#0f172a; color:#fff; padding:14px 22px; border-radius:10px;
            font-size:0.875rem; font-weight:500; opacity:0; transform:translateY(20px);
            transition:all 0.3s ease; box-shadow:0 4px 14px rgba(0,0,0,0.2); min-width:260px;
        }
        .toast.show { opacity:1; transform:translateY(0); }
    </style>
</head>
<body>
<?php $navActive='dashboard'; require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-wrap">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="main">
        <h2>
            Pending Voter Registrations
            <span class="badge-count" id="pendingCount"><?= count($pending) ?> Pending</span>
        </h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:0;font-size:0.9rem">
            Review the COR and account details, then approve or reject each registration.
        </p>

        <?php if (empty($pending)): ?>
        <div class="empty-state">
            <span class="icon">🎉</span>
            <p>No pending registrations — all caught up!</p>
        </div>
        <?php else: ?>
        <div class="cards-grid" id="cardsGrid">
        <?php foreach ($pending as $s):
            $docPath  = $s['id_document'] ? '/uploads/' . $s['id_document'] : null;
            $ext      = $docPath ? strtolower(pathinfo($docPath, PATHINFO_EXTENSION)) : '';
            $isPdf    = ($ext === 'pdf');
            $isImage  = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            $fullName = htmlspecialchars($s['last_name'] . ', ' . $s['first_name']);
        ?>
        <div class="student-card" id="card-<?= $s['id'] ?>" data-id="<?= $s['id'] ?>">

            <!-- COR Preview -->
            <div class="cor-preview" <?= $isImage ? 'onclick="openModal(\'' . addslashes($docPath) . '\', \'' . addslashes($fullName) . '\')" title="Click to enlarge"' : '' ?>>
                <?php if ($isImage): ?>
                    <img src="<?= htmlspecialchars($docPath) ?>" alt="COR">
                    <span class="view-badge">🔍 Click to enlarge</span>
                <?php elseif ($isPdf): ?>
                    <div class="no-doc">
                        <span class="pdf-icon">📄</span>
                        <a href="<?= htmlspecialchars($docPath) ?>" target="_blank"
                           style="display:block;margin-top:8px;color:#3b82f6;font-weight:700;font-size:0.85rem;text-decoration:none;">
                            Open PDF Document ↗
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-doc">
                        <span class="no-doc-icon">📭</span>
                        <span>No document uploaded yet</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <div class="student-name"><?= $fullName ?></div>
                <div class="student-meta">
                    <span>🎓 <?= htmlspecialchars($s['student_id']) ?></span>
                    <span>📧 <?= htmlspecialchars($s['email']) ?></span>
                </div>

                <?php if (!$docPath): ?>
                <div class="no-doc-warn">⚠️ Student has not uploaded a COR yet.</div>
                <?php endif; ?>

                <div class="detail-row">
                    <span class="detail-chip highlight"><?= htmlspecialchars($s['course']) ?></span>
                    <span class="detail-chip"><?= htmlspecialchars($s['year_level']) ?></span>
                    <span class="detail-chip">📅 <?= date('M d, Y', strtotime($s['created_at'])) ?></span>
                </div>

                <div class="action-buttons">
                    <button class="btn-approve" onclick="processAction(<?= $s['id'] ?>, 'approve', this)">✅ Approve</button>
                    <button class="btn-reject"  onclick="processAction(<?= $s['id'] ?>, 'reject',  this)">❌ Reject</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- Image preview modal -->
<div class="modal-overlay" id="imgModal" onclick="closeModal(event)">
    <div class="modal-content">
        <button class="modal-close" onclick="document.getElementById('imgModal').classList.remove('open')">✕</button>
        <div class="modal-name" id="modalName"></div>
        <img id="modalImg" src="" alt="COR Preview">
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="/assets/js/shared.js"></script>
<script>
let pendingCount = <?= count($pending) ?>;

function openModal(src, name) {
    document.getElementById('modalImg').src  = src;
    document.getElementById('modalName').textContent = name + ' — Certificate of Registration';
    document.getElementById('imgModal').classList.add('open');
}
function closeModal(e) {
    if (e.target.id === 'imgModal') document.getElementById('imgModal').classList.remove('open');
}

function showToast(message, type) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.borderLeft = `4px solid ${type === 'approve' ? '#10b981' : '#ef4444'}`;
    toast.innerText = message;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3500);
}

async function processAction(userId, action, btn) {
    const card = document.getElementById('card-' + userId);
    card.querySelectorAll('button').forEach(b => { b.disabled=true; b.style.opacity='0.5'; b.textContent='Wait...'; });

    try {
        const res = await fetch('/admin/verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userId, action })
        });
        const result = await res.json();

        if (result.status === 'success') {
            const name = card.querySelector('.student-name').innerText.trim();
            card.classList.add('card-removing');
            setTimeout(() => {
                card.remove();
                pendingCount--;
                document.getElementById('pendingCount').textContent = pendingCount + ' Pending';
                if (pendingCount === 0) {
                    document.getElementById('cardsGrid').outerHTML =
                        '<div class="empty-state"><span class="icon">🎉</span><p>No pending registrations — all caught up!</p></div>';
                }
                showToast(`${name} ${action === 'approve' ? 'approved ✅' : 'rejected ❌'} and notified.`, action);
            }, 400);
        } else {
            showToast('Error: ' + result.message, 'reject');
            card.querySelectorAll('button').forEach(b => { b.disabled=false; b.style.opacity='1'; });
            card.querySelector('.btn-approve').textContent = '✅ Approve';
            card.querySelector('.btn-reject').textContent  = '❌ Reject';
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'reject');
        card.querySelectorAll('button').forEach(b => { b.disabled=false; b.style.opacity='1'; });
        card.querySelector('.btn-approve').textContent = '✅ Approve';
        card.querySelector('.btn-reject').textContent  = '❌ Reject';
    }
}
</script>
</body>
</html>