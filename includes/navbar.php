<?php
// =============================================================
//  SHARED NAVBAR PARTIAL
//  Usage:  $navActive = 'home';  require INCLUDES . 'navbar.php';
//  $navActive values: 'home' | 'dashboard' | 'about'
// =============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

$user      = currentUser();
$role      = $user['role'];
$status    = $user['status'];
$navActive = $navActive ?? '';

// Home is the same page for everyone
$homeUrl = '/index.php';

if ($role === 'admin') {
    $dashboardUrl = '/admin/dashboard.php';
    $homeUrl = '/admin/home.php';
} elseif ($role === 'student' && $status === 'approved') {
    $dashboardUrl = '/student/dashboard.php';
    $homeUrl = '/student/home.php';
} elseif ($role === 'student') {
    // Pending or rejected students have no dashboard yet — send to account page
    $dashboardUrl = '/student/account.php';
} else {
    $dashboardUrl = '/guest/dashboard.php';
}

$aboutUrl = '/about.php';

function navLink(string $href, string $label, string $active, string $key): string {
    $cls = ($active === $key) ? 'nav-link active' : 'nav-link';
    return "<a href=\"$href\" class=\"$cls\">$label</a>";
}
?>
<nav class="navbar">
    <a href="<?= $homeUrl ?>" class="nav-logo">
        <div class="logo-icon">
            <img src="/assets/img/icons/logo.png" alt="logo" style="width:100%;height:100%;object-fit:contain;"
                 onerror="this.style.display='none'">
        </div>
        <div class="logo-text">COS ONLINE VOTING SYSTEM</div>
    </a>

    <div class="nav-links">
        <?= navLink($homeUrl,      'Home',      $navActive, 'home') ?>
        <?php if ($role === 'student' && $status !== 'approved'): ?>
            <?php /* Pending/rejected students see "My Account" instead of "Dashboard" */ ?>
            <?= navLink('/student/account.php', 'My Account', $navActive, 'dashboard') ?>
        <?php else: ?>
            <?= navLink($dashboardUrl, 'Dashboard', $navActive, 'dashboard') ?>
        <?php endif; ?>
        <?= navLink($aboutUrl, 'About', $navActive, 'about') ?>
    </div>

    <?php if ($role === 'guest' || !isLoggedIn()): ?>
    <!-- GUEST: plain Login button -->
    <div class="nav-auth">
        <button class="btn-login-oval" onclick="document.getElementById('authModal').style.display='flex'">
            Log In
        </button>
    </div>

    <?php elseif ($role === 'student'): ?>
    <!-- STUDENT dropdown -->
    <div class="user-menu-container">
        <div class="user-greeting" onclick="toggleUserMenu(event)">
            Hello, <span><?= htmlspecialchars($user['first_name']) ?></span>
            <?php if ($status === 'pending'): ?>
                <span style="display:inline-block;background:#fef3c7;color:#92400e;font-size:0.65rem;font-weight:800;padding:2px 7px;border-radius:20px;margin-left:6px;vertical-align:middle;">PENDING</span>
            <?php elseif ($status === 'rejected'): ?>
                <span style="display:inline-block;background:#fee2e2;color:#991b1b;font-size:0.65rem;font-weight:800;padding:2px 7px;border-radius:20px;margin-left:6px;vertical-align:middle;">REJECTED</span>
            <?php endif; ?>
            <span class="arrow-icon">▼</span>
        </div>
        <div class="user-dropdown" id="userDropdownMenu">
            <div class="dropdown-header">
                <h3><?= htmlspecialchars($user['name']) ?></h3>
                <p>Student ID: <?= htmlspecialchars($user['student_id']) ?></p>
            </div>
            <div class="divider"></div>
            <a href="/student/account.php" class="update-link">My Account</a>
            <form method="POST" action="/logout.php">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button type="submit" class="btn-logout-oval">Log Out</button>
            </form>
        </div>
    </div>

    <?php else: ?>
    <!-- ADMIN dropdown -->
    <div class="user-menu-container">
        <div class="user-greeting" onclick="toggleUserMenu(event)">
            Hello, Admin <span class="arrow-icon">▼</span>
        </div>
        <div class="user-dropdown" id="userDropdownMenu">
            <form method="POST" action="/logout.php">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button type="submit" class="btn-logout-oval">Log Out</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</nav>