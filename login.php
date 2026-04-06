<?php
// =============================================================
//  login.php — Login & Register (replaces login.html)
// =============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

redirectIfLoggedIn();   // already logged-in users bounce to their dashboard

$error   = '';
$success = '';
$view    = $_GET['view'] ?? 'login';     // 'login' | 'register'

// ---- HANDLE POST -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ---- LOGIN ----
    if ($action === 'login') {
        $sid  = trim($_POST['student_id'] ?? '');
        $pass = $_POST['password'] ?? '';

        if ($sid === '' || $pass === '') {
            $error = 'Please fill in all fields.';
            $view  = 'login';
        } else {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE student_id = ? LIMIT 1");
            $stmt->execute([$sid]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password_hash'])) {
                loginUser($user);
                if ($user['role'] === 'admin') {
                    $dest = '/admin/dashboard.php';
                } elseif ($user['status'] === 'approved') {
                    $dest = '/student/dashboard.php';
                } else {
                    // Pending / rejected: go to account page to upload COR
                    $dest = '/student/account.php';
                }
                header("Location: $dest");
                exit;
            } else {
                $error = 'Invalid Student ID or password.';
                $view  = 'login';
            }
        }
    }

    // ---- REGISTER ----
    if ($action === 'register') {
        $view   = 'register';
        $fname  = trim($_POST['fname']   ?? '');
        $lname  = trim($_POST['lname']   ?? '');
        $idno   = trim($_POST['id_no']   ?? '');
        $course = trim($_POST['course']  ?? '');
        $pass   = $_POST['pass']         ?? '';
        $conf   = $_POST['confirm']      ?? '';

        if (!$fname || !$lname || !$idno || !$course || !$pass) {
            $error = 'All fields are required.';
        } elseif (strlen($pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($pass !== $conf) {
            $error = 'Passwords do not match.';
        } else {
            $db   = getDB();
            $chk  = $db->prepare("SELECT id FROM users WHERE student_id = ? LIMIT 1");
            $chk->execute([$idno]);
            if ($chk->fetch()) {
                $error = 'That Student ID is already registered.';
            } else {
                // Build a placeholder email from student_id
                $email = strtolower(str_replace('-', '', $idno)) . '@student.edu.ph';
                $hash  = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins   = $db->prepare(
                    "INSERT INTO users (student_id, first_name, last_name, email, password_hash, course, year_level, role, status)
                     VALUES (?, ?, ?, ?, ?, ?, '1st Year', 'student', 'pending')"
                );
                $ins->execute([$idno, $fname, $lname, $email, $hash, $course]);

                // Auto-login the new user and send them to their account page
                $newUserStmt = $db->prepare("SELECT * FROM users WHERE student_id = ? LIMIT 1");
                $newUserStmt->execute([$idno]);
                $newUser = $newUserStmt->fetch();

                if ($newUser) {
                    loginUser($newUser);
                    header("Location: /student/account.php?new=1");
                    exit;
                }

                // Fallback (shouldn't happen)
                $success = 'Account created! Please log in to complete your profile.';
                $view    = 'login';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COS | Login & Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Geist:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --deep-green: #12341d; --mid-green: #33553e;
            --soft-green: #6d9078; --pale-green: #a4c1ad; --tint-green: #d5e8db;
        }
        body {
            margin:0; padding:0; font-family:'Geist',sans-serif;
            background: linear-gradient(135deg, #33553e 0%, #b8d1c0 100%);
            display:flex; justify-content:center; align-items:center;
            min-height:100vh; overflow-x:hidden;
        }
        .background-blur {
            position:fixed; inset:0; background:inherit;
            filter:blur(100px); z-index:-1;
        }
        #container-card {
            width:100%; max-width:450px;
            background:rgba(255,255,255,0.3);
            backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,0.2);
            border-radius:28px;
            box-shadow:0 15px 35px -5px rgba(18,52,29,0.2),0 0 15px rgba(255,255,255,0.1);
            overflow:hidden; transition:all 0.5s cubic-bezier(0.4,0,0.2,1);
            display:flex; flex-direction:column;
        }
        #container-card:focus-within {
            box-shadow:0 30px 60px -12px rgba(18,52,29,0.3),0 0 30px rgba(109,144,120,0.4);
            transform:translateY(-5px);
        }
        .top-bar { height:6px; background:linear-gradient(to right,var(--pale-green),var(--tint-green),var(--pale-green)); opacity:0.8; }
        .card-logo-container { padding-top:30px; display:flex; justify-content:center; }
        .logo-image {
            width:80px; height:80px; border-radius:50%; object-fit:cover;
            border:3px solid var(--tint-green); background:rgba(255,255,255,0.7);
            transition:all 0.4s ease; filter:drop-shadow(0 4px 8px rgba(18,52,29,0.1));
        }
        .header-section { padding:15px 40px; text-align:center; }
        .badge {
            display:inline-block; padding:4px 12px;
            background:rgba(213,232,219,0.6); border-radius:100px;
            margin-bottom:8px; border:1px solid rgba(255,255,255,0.3);
        }
        .badge span {
            font-family:'Montserrat',sans-serif; color:#0c0c0c;
            font-weight:800; font-size:9px; letter-spacing:2px; text-transform:uppercase;
        }
        h1 {
            font-family:'Montserrat',sans-serif; color:#fff; margin:0;
            font-size:24px; font-weight:900; text-shadow:0 2px 4px rgba(18,52,29,0.4);
        }
        .form-container { padding:0 40px 30px; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
        .field-group { margin-bottom:15px; }
        label {
            font-family:'Montserrat',sans-serif; font-size:10px; font-weight:800;
            color:#515154; margin-bottom:6px; display:block;
            text-transform:uppercase; text-shadow:0 1px 1px rgba(18,52,29,0.2);
        }
        input, select {
            font-family:'Geist',sans-serif; width:100%; padding:12px 15px;
            background:rgba(255,255,255,0.1); border:2px solid rgba(255,255,255,0.15);
            border-radius:12px; font-size:14px; outline:none; box-sizing:border-box;
            transition:all 0.3s ease; color:#fff;
        }
        input::placeholder { color:rgba(255,255,255,0.6); }
        input:focus { border-color:rgba(109,144,120,0.6); background:rgba(255,255,255,0.2); }
        select option { background:#12341d; color:#fff; }
        .btn-primary {
            font-family:'Montserrat',sans-serif; width:100%; padding:15px;
            background:var(--tint-green); color:var(--deep-green); border:none;
            border-radius:12px; font-weight:800; font-size:13px; cursor:pointer;
            text-transform:uppercase; letter-spacing:1.5px; margin-top:10px;
            transition:0.3s; box-shadow:0 8px 15px rgba(18,52,29,0.2);
        }
        .btn-primary:hover { background:#fff; transform:translateY(-2px); }
        .toggle-btn {
            background:none; border:none; color:var(--tint-green); font-weight:800;
            cursor:pointer; text-decoration:underline; font-family:'Montserrat';
            font-size:11px; transition:color 0.2s;
        }
        .toggle-btn:hover { color:#fff; }
        .secure-footer {
            background:rgba(213,232,219,0.1); padding:12px; text-align:center;
            border-top:1px solid rgba(255,255,255,0.1);
        }
        .secure-text { font-size:9px; color:#515154; font-weight:700; letter-spacing:1px; text-transform:uppercase; }
        .alert { padding:10px 14px; border-radius:10px; margin-bottom:14px; font-size:13px; font-weight:600; }
        .alert-error   { background:rgba(239,68,68,0.25); color:#fff; border:1px solid rgba(239,68,68,0.4); }
        .alert-success { background:rgba(16,185,129,0.25); color:#fff; border:1px solid rgba(16,185,129,0.4); }
    </style>
</head>
<body>
<div class="background-blur"></div>
<div id="container-card">
    <div class="top-bar"></div>
    <div class="card-logo-container">
        <img src="/assets/img/icons/logo.png" alt="COS Logo" class="logo-image"
             onerror="this.style.background='var(--tint-green)'">
    </div>
    <div class="header-section">
        <div class="badge"><span>COLLEGE OF SCIENCE</span></div>
        <h1 id="form-title"><?= $view === 'login' ? 'Student Login' : 'Create Account' ?></h1>
    </div>

    <div class="form-container">
        <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <!-- LOGIN VIEW -->
        <div id="signin-view" <?= $view !== 'login' ? 'style="display:none"' : '' ?>>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="field-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="M2023-00000" required>
                </div>
                <div class="field-group" style="margin-bottom:25px">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary">Verify &amp; Enter</button>
            </form>
            <div style="text-align:center;margin-top:25px">
                <span style="font-size:12px;color:#515154">New here?</span>
                <button onclick="toggleForms()" class="toggle-btn">Create Account</button>
            </div>
        </div>

        <!-- REGISTER VIEW -->
        <div id="signup-view" <?= $view !== 'register' ? 'style="display:none"' : '' ?>>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="grid-2">
                    <div><label>First Name</label><input type="text" name="fname" placeholder="John" required></div>
                    <div><label>Last Name</label><input type="text" name="lname" placeholder="Doe" required></div>
                </div>
                <div class="field-group">
                    <label>Official Student No.</label>
                    <input type="text" name="id_no" placeholder="M2023-00000" required>
                </div>
                <div class="field-group">
                    <label>Major Course</label>
                    <select name="course" required>
                        <option value="" disabled selected>Select Major</option>
                        <option>BS Computer Science</option>
                        <option>BS Mathematics</option>
                        <option>BS Psychology</option>
                        <option>BS Biology</option>
                        <option>BS Human Services</option>
                    </select>
                </div>
                <div class="grid-2">
                    <div><label>Password</label><input type="password" name="pass" placeholder="••••••••" required></div>
                    <div><label>Confirm</label><input type="password" name="confirm" placeholder="••••••••" required></div>
                </div>
                <button type="submit" class="btn-primary">Register Account</button>
            </form>
            <div style="text-align:center;margin-top:20px">
                <button onclick="toggleForms()" class="toggle-btn">Back to Sign In</button>
            </div>
        </div>
    </div>

    <div class="secure-footer">
        <p class="secure-text">🔒 Secure 256-bit Encrypted Portal</p>
    </div>
</div>
<script>
function toggleForms() {
    const signin  = document.getElementById('signin-view');
    const signup  = document.getElementById('signup-view');
    const title   = document.getElementById('form-title');
    const card    = document.getElementById('container-card');
    const showing = signin.style.display !== 'none';
    signin.style.display = showing ? 'none' : 'block';
    signup.style.display = showing ? 'block' : 'none';
    title.innerText       = showing ? 'Create Account' : 'Student Login';
    card.style.maxWidth   = showing ? '550px' : '450px';
}
</script>
</body>
</html>