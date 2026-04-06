<?php
// =============================================================
//  student/account.php  (replaces MyAccount.html)
// =============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireStudent();   // Pending students are now allowed through to this page

$db   = getDB();
$user = currentUser();

// Load full user record
$stmt = $db->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

$error   = '';
$success = '';

// Detect fresh registration redirect
$isNewUser = isset($_GET['new']) && $_GET['new'] === '1';

// ---- HANDLE POST ACTIONS --------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $fname   = trim($_POST['first_name']     ?? '');
            $lname   = trim($_POST['last_name']      ?? '');
            $mi      = trim($_POST['middle_initial'] ?? '');
            $address = trim($_POST['address']        ?? '');
            $year    = trim($_POST['year_level']     ?? '');

            // Handle Profile Picture Upload
            $profilePicQuery = "";
            $params = [$fname, $lname, $mi, $address, $year];

            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $filename = 'student_' . $user['id'] . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $filename)) {
                    $profilePicQuery = ", profile_pic=?";
                    $params[] = '/uploads/profiles/' . $filename;
                }
            }

            if (!$fname || !$lname) {
                $error = 'First and last name are required.';
            } else {
                $params[] = $user['id'];
                $upd = $db->prepare(
                    "UPDATE users SET first_name=?, last_name=?, middle_initial=?, address=?, year_level=? $profilePicQuery WHERE id=?"
                );
                $upd->execute($params);
                
                $_SESSION['first_name'] = $fname;
                $_SESSION['full_name']  = "$fname $lname";
                $success = 'Profile updated successfully.';
                
                // Refresh
                $stmt->execute([$user['id']]);
                $profile = $stmt->fetch();
            }
        }

        if ($action === 'upload_cor') {
            $file = $_FILES['id_document'] ?? null;
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg','image/png','image/gif','application/pdf'];
                $maxSize = 5 * 1024 * 1024; // 5 MB

                if (!in_array($file['type'], $allowed)) {
                    $error = 'Only JPG, PNG, or PDF files are allowed.';
                } elseif ($file['size'] > $maxSize) {
                    $error = 'File size must be under 5 MB.';
                } else {
                    $uploadDir = __DIR__ . '/../uploads/documents/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'cor_' . $user['id'] . '_' . time() . '.' . $ext;
                    $dest     = $uploadDir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $db->prepare("UPDATE users SET id_document=?, status='pending' WHERE id=?")
                           ->execute(['documents/' . $filename, $user['id']]);
                        $_SESSION['status'] = 'pending';
                        $success = '✅ COR uploaded successfully! An admin will review and approve your account shortly.';
                        $stmt->execute([$user['id']]);
                        $profile = $stmt->fetch();
                    } else {
                        $error = 'Upload failed. Please try again.';
                    }
                }
            } else {
                $error = 'Please select a file to upload.';
            }
        }
    }
}

$status    = $profile['status'] ?? 'pending';
$hasDoc    = !empty($profile['id_document']);
$navActive = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | iVOTE CS</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/shared.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'Geist',sans-serif;
            background:linear-gradient(135deg,#33553e 0%,#b8d1c0 100%);
            background-attachment:fixed; min-height:100vh;
            display:flex; flex-direction:column; align-items:center;
            padding:100px 20px 60px;
        }
        h2,h3,.edit-btn,.btn-verify,label { font-family:'Montserrat',sans-serif; }

        /* ── Onboarding / status banner ── */
        .status-banner {
            width:100%; max-width:900px; border-radius:20px;
            padding:20px 28px; margin-bottom:24px;
            display:flex; align-items:flex-start; gap:16px;
        }
        .status-banner .banner-icon { font-size:2rem; flex-shrink:0; margin-top:2px; }
        .status-banner .banner-body h3 { font-size:1rem; font-weight:800; margin-bottom:4px; }
        .status-banner .banner-body p  { font-size:0.85rem; line-height:1.5; margin:0; }

        .banner-welcome  { background:rgba(255,255,255,0.55); border:1px solid rgba(255,255,255,0.7); }
        .banner-welcome  .banner-body h3 { color:#12341d; }
        .banner-welcome  .banner-body p  { color:#33553e; }

        .banner-pending  { background:rgba(254,243,199,0.75); border:1px solid #fcd34d; }
        .banner-pending  .banner-body h3 { color:#92400e; }
        .banner-pending  .banner-body p  { color:#78350f; }

        .banner-rejected { background:rgba(254,226,226,0.75); border:1px solid #fca5a5; }
        .banner-rejected .banner-body h3 { color:#991b1b; }
        .banner-rejected .banner-body p  { color:#7f1d1d; }

        /* ── COR upload card (shown when no doc uploaded yet) ── */
        .cor-card {
            width:100%; max-width:900px; border-radius:20px;
            background:rgba(255,255,255,0.55); border:2px dashed #33553e;
            padding:28px 32px; margin-bottom:24px;
        }
        .cor-card h3 { color:#12341d; font-size:1rem; font-weight:800; margin-bottom:6px; }
        .cor-card p  { color:#33553e; font-size:0.85rem; margin-bottom:20px; }

        .cor-upload-zone {
            background:rgba(255,255,255,0.4); border:2px dashed rgba(51,85,62,0.4);
            border-radius:16px; padding:24px 20px; text-align:center; cursor:pointer;
            transition:all 0.3s; display:block; margin-bottom:16px;
        }
        .cor-upload-zone:hover { background:rgba(255,255,255,0.6); border-color:#33553e; }
        .cor-upload-zone .upload-title { font-size:14px; color:#33553e; font-weight:700; text-transform:uppercase; }
        .cor-upload-zone .upload-sub   { font-size:11px; color:#515154; margin-top:6px; font-weight:600; }
        #corFileChosen { font-size:12px; color:#33553e; margin-top:8px; font-weight:600; display:block; }

        .btn-submit-cor {
            background:#33553e; color:#fff; border:none; padding:14px 28px;
            border-radius:14px; font-family:'Montserrat',sans-serif; font-size:14px;
            font-weight:800; cursor:pointer; transition:all 0.2s;
            box-shadow:0 6px 16px rgba(51,85,62,0.3); width:100%;
        }
        .btn-submit-cor:hover { background:#12341d; transform:translateY(-2px); }

        /* ── Main account card ── */
        .glass-card {
            background:rgba(255,255,255,0.45);
            backdrop-filter:blur(25px) saturate(180%);
            border:1px solid rgba(255,255,255,0.5); border-radius:30px;
            width:100%; max-width:900px; padding:40px;
            box-shadow:0 20px 50px rgba(0,0,0,0.1);
        }
        .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .title-area h2 { font-size:24px; color:#1d1d1f; font-weight:700; }
        .title-area p  { font-size:13px; color:#515154; margin-top:4px; }

        .status-pill {
            display:inline-block; padding:5px 14px; border-radius:100px;
            font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-top:6px;
        }
        .status-approved { background:#d1fae5; color:#065f46; }
        .status-pending  { background:#fef3c7; color:#92400e; }
        .status-rejected { background:#fee2e2; color:#991b1b; }

        .edit-btn {
            background:rgba(255,255,255,0.6); border:1px solid rgba(255,255,255,0.8);
            padding:8px 20px; border-radius:12px; font-size:14px; font-weight:600;
            color:#1d1d1f; cursor:pointer; transition:all 0.2s;
        }
        .edit-btn:hover { background:#fff; transform:translateY(-1px); }

        .main-grid { display:grid; grid-template-columns:240px 1fr; gap:40px; }
        @media(max-width:768px){ .main-grid{ grid-template-columns:1fr; } }

        /* Profile side */
        .profile-side { text-align:center; }
        .avatar-circle {
            width:150px; height:150px; background:rgba(255,255,255,0.5);
            border:2px solid white; border-radius:50%; margin:0 auto 20px;
            display:flex; align-items:center; justify-content:center;
            font-size:2.5rem; font-weight:900; color:#33553e;
            overflow:hidden;
        }
        .avatar-circle img { width:100%; height:100%; object-fit:cover; }
        .profile-name { font-family:'Montserrat',sans-serif; font-size:1.1rem; font-weight:700; color:#1d1d1f; }
        .profile-id   { font-size:0.85rem; color:#515154; margin-top:4px; }

        /* Info grid */
        .info-grid {
            display:grid; grid-template-columns:1fr 1fr; gap:16px;
        }
        .input-group { display:flex; flex-direction:column; }
        .full-width { grid-column:1/-1; }

        .glass-input {
            background:rgba(255,255,255,0.3); border:1.5px solid rgba(255,255,255,0.5);
            border-radius:12px; padding:11px 14px; font-size:14px; color:#1d1d1f;
            font-family:'Geist',sans-serif; outline:none; transition:all 0.2s;
            width:100%;
        }
        .glass-input:focus { border-color:#33553e; background:rgba(255,255,255,0.5); }
        .glass-input[readonly], .glass-input:disabled { opacity:0.7; cursor:default; }
        select.glass-input { appearance:auto; }

        /* Doc section */
        .doc-section { margin-top:28px; }
        .upload-zone {
            background:rgba(255,255,255,0.2); border:2px dashed rgba(51,85,62,0.3);
            border-radius:20px; padding:28px 20px; text-align:center; cursor:pointer;
            transition:all 0.3s; display:block;
        }
        .upload-zone:hover { background:rgba(255,255,255,0.3); border-color:#33553e; }
        .upload-zone .upload-title { font-size:14px; color:#33553e; font-weight:700; text-transform:uppercase; }
        .upload-zone .upload-sub   { font-size:11px; color:#515154; margin-top:6px; font-weight:600; }
        .current-doc { font-size:12px; color:#33553e; margin-top:8px; font-weight:600; }
        .hidden { display:none; }

        .btn-verify {
            background:#33553e; color:white; border:none; padding:16px;
            border-radius:16px; width:100%; font-size:15px; font-weight:700;
            margin-top:24px; cursor:pointer; transition:all 0.2s;
            box-shadow:0 8px 20px rgba(51,85,62,0.3);
        }
        .btn-verify:hover { background:#12341d; transform:translateY(-2px); }

        .alert { padding:12px 16px; border-radius:12px; margin-bottom:18px; font-size:13px; font-weight:600; }
        .alert-error   { background:rgba(239,68,68,0.15); color:#991b1b; border:1px solid rgba(239,68,68,0.3); }
        .alert-success { background:rgba(16,185,129,0.15); color:#065f46; border:1px solid rgba(16,185,129,0.3); }

        .step-indicator {
            display:flex; align-items:center; gap:8px; margin-bottom:16px;
        }
        .step-dot {
            width:28px; height:28px; border-radius:50%; display:flex; align-items:center;
            justify-content:center; font-size:0.75rem; font-weight:800;
        }
        .step-done  { background:#d1fae5; color:#065f46; }
        .step-active{ background:#33553e; color:#fff; }
        .step-todo  { background:rgba(0,0,0,0.1); color:#515154; }
        .step-line  { flex:1; height:2px; background:rgba(0,0,0,0.1); border-radius:2px; }
        .step-line.done { background:#33553e; }
        .step-label { font-size:0.72rem; font-weight:700; color:#515154; text-align:center; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<?php
// ── Status / onboarding banner ────────────────────────────────
if ($isNewUser): ?>
<div class="status-banner banner-welcome">
    <div class="banner-icon">🎉</div>
    <div class="banner-body">
        <h3>Welcome to iVOTE CS, <?= htmlspecialchars($profile['first_name']) ?>!</h3>
        <p>Your account has been created. To complete your registration, please upload a clear photo or scan of your <strong>Certificate of Registration (COR)</strong> below. An admin will review and approve your account once your document is verified.</p>
    </div>
</div>
<?php elseif ($status === 'pending' && !$hasDoc): ?>
<div class="status-banner banner-pending">
    <div class="banner-icon">⏳</div>
    <div class="banner-body">
        <h3>Action Required — Upload your COR</h3>
        <p>Your account is pending. Please upload your <strong>Certificate of Registration (COR)</strong> so an admin can verify your identity and approve your account.</p>
    </div>
</div>
<?php elseif ($status === 'pending' && $hasDoc): ?>
<div class="status-banner banner-pending">
    <div class="banner-icon">🕐</div>
    <div class="banner-body">
        <h3>Awaiting Admin Approval</h3>
        <p>Your COR has been submitted. Please wait while an admin reviews your document. You'll be notified by email once approved. You may re-upload if the document is unclear.</p>
    </div>
</div>
<?php elseif ($status === 'rejected'): ?>
<div class="status-banner banner-rejected">
    <div class="banner-icon">❌</div>
    <div class="banner-body">
        <h3>Registration Rejected</h3>
        <p>Your registration was rejected. Please re-upload a valid COR or visit the admin office. Once you submit a new document, your account will be re-queued for review.</p>
    </div>
</div>
<?php endif; ?>

<?php
// ── COR upload card — shown when no doc yet, or rejected ──────
if (!$hasDoc || $status === 'rejected'): ?>
<div class="cor-card">
    <?php
    // Progress steps
    $step1done = true;
    $step2done = $hasDoc;
    $step3done = ($status === 'approved');
    ?>
    <div class="step-indicator">
        <div>
            <div class="step-dot step-done">✓</div>
            <div class="step-label">Register</div>
        </div>
        <div class="step-line done"></div>
        <div>
            <div class="step-dot <?= $step2done ? 'step-done' : 'step-active' ?>"><?= $step2done ? '✓' : '2' ?></div>
            <div class="step-label">Upload COR</div>
        </div>
        <div class="step-line <?= $step3done ? 'done' : '' ?>"></div>
        <div>
            <div class="step-dot <?= $step3done ? 'step-done' : 'step-todo' ?>"><?= $step3done ? '✓' : '3' ?></div>
            <div class="step-label">Get Approved</div>
        </div>
    </div>

    <h3>📄 Upload Certificate of Registration (COR)</h3>
    <p>Take a clear photo or scan of your official COR from the registrar. Accepted formats: JPG, PNG, or PDF. Max file size: 5 MB.</p>

    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_cor">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <label for="cor-upload" class="cor-upload-zone">
            <div class="upload-title">📁 Click to choose your COR file</div>
            <div class="upload-sub">JPG · PNG · PDF &nbsp;|&nbsp; Max 5 MB</div>
            <span id="corFileChosen"></span>
        </label>
        <input type="file" id="cor-upload" name="id_document" class="hidden"
               accept=".jpg,.jpeg,.png,.pdf"
               onchange="document.getElementById('corFileChosen').textContent = this.files[0]?.name || ''">

        <button type="submit" class="btn-submit-cor">Submit COR for Verification →</button>
    </form>
</div>
<?php endif; ?>

<!-- ── Main profile card ──────────────────────────────────── -->
<div class="glass-card">
    <?php if (!(!$hasDoc || $status === 'rejected')): // Only show alerts here when COR card is not shown ?>
        <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="card-header">
        <div class="title-area">
            <h2>My Account</h2>
            <p>Review and manage your registration details</p>
        </div>
        <button class="edit-btn" id="edit-button" onclick="toggleEdit()">Edit Profile</button>
    </div>

    <div class="main-grid">
        <div class="profile-side">
            <div class="avatar-circle">
                <?php if (!empty($profile['profile_pic'])): ?>
                    <img src="<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile">
                <?php else: ?>
                    <?= htmlspecialchars(strtoupper(substr($profile['first_name'],0,1) . substr($profile['last_name'],0,1))) ?>
                <?php endif; ?>
            </div>
            <div class="profile-name"><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></div>
            <div class="profile-id"><?= htmlspecialchars($profile['student_id']) ?></div>
            <div>
                <span class="status-pill status-<?= htmlspecialchars($status) ?>"><?= ucfirst($status) ?></span>
            </div>
            <?php if ($profile['has_voted']): ?>
                <div style="margin-top:12px;font-size:12px;color:#065f46;font-weight:700">✅ Vote Cast</div>
            <?php endif; ?>
        </div>

        <div>
            <form method="POST" id="profileForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <div class="info-grid">
                    <div class="input-group full-width hidden" id="picGroup">
                        <label>Change Profile Photo</label>
                        <input type="file" name="profile_pic" class="glass-input editable" accept="image/*">
                    </div>

                    <div class="input-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="glass-input editable" value="<?= htmlspecialchars($profile['first_name']) ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label>M.I.</label>
                        <input type="text" name="middle_initial" class="glass-input editable" value="<?= htmlspecialchars($profile['middle_initial'] ?? '') ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="glass-input editable" value="<?= htmlspecialchars($profile['last_name']) ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label>Student No.</label>
                        <input type="text" class="glass-input" value="<?= htmlspecialchars($profile['student_id']) ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label>Year Level</label>
                        <select name="year_level" class="glass-input editable" disabled>
                            <?php foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $yr): ?>
                                <option <?= $profile['year_level']===$yr?'selected':'' ?>><?= $yr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Course / Program</label>
                        <input type="text" class="glass-input" value="<?= htmlspecialchars($profile['course']) ?>" readonly>
                    </div>
                    <div class="input-group full-width">
                        <label>Residential Address</label>
                        <input type="text" name="address" class="glass-input editable" value="<?= htmlspecialchars($profile['address'] ?? '') ?>" readonly>
                    </div>
                </div>
                <button type="submit" id="saveProfileBtn" class="btn-verify hidden" style="margin-top:16px">Save Profile Changes</button>
            </form>

            <?php if ($hasDoc && $status !== 'rejected'): ?>
            <!-- Re-upload section (doc already submitted) -->
            <div class="doc-section">
                <form method="POST" enctype="multipart/form-data" id="docForm">
                    <input type="hidden" name="action" value="upload_cor">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <label style="color:#1d1d1f;font-weight:700;font-size:12px;text-transform:uppercase;display:block;margin-bottom:8px">
                        📄 Verification Document (COR / School ID)
                    </label>
                    <label for="doc-upload-2" class="upload-zone">
                        <div class="upload-title">Replace document</div>
                        <div class="upload-sub">PDF, JPG, or PNG · Max 5 MB</div>
                        <div class="current-doc">✅ Current file: <?= htmlspecialchars(basename($profile['id_document'])) ?></div>
                        <div id="fileChosen2" style="font-size:12px;color:#33553e;margin-top:8px;font-weight:600"></div>
                    </label>
                    <input type="file" id="doc-upload-2" name="id_document" class="hidden"
                           accept=".jpg,.jpeg,.png,.pdf"
                           onchange="document.getElementById('fileChosen2').textContent = this.files[0]?.name || ''">
                    <button type="submit" class="btn-verify">Re-submit for Verification</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/assets/js/shared.js"></script>
<script>
function toggleEdit() {
    const btn      = document.getElementById('edit-button');
    const inputs   = document.querySelectorAll('.editable');
    const saveBtn  = document.getElementById('saveProfileBtn');
    const picGroup = document.getElementById('picGroup');
    const isEditing = btn.textContent === 'Cancel';

    if (isEditing) {
        inputs.forEach(input => {
            if (input.tagName === 'SELECT') input.disabled = true;
            else input.setAttribute('readonly', true);
        });
        btn.textContent = 'Edit Profile';
        saveBtn.classList.add('hidden');
        picGroup.classList.add('hidden');
    } else {
        inputs.forEach(input => {
            if (input.tagName === 'SELECT') input.disabled = false;
            else input.removeAttribute('readonly');
        });
        inputs[0].focus();
        btn.textContent = 'Cancel';
        saveBtn.classList.remove('hidden');
        picGroup.classList.remove('hidden');
    }
}
</script>
</body>
</html>