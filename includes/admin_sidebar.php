<?php
// =============================================================
//  Admin sidebar partial
//  Usage: $sidebarActive = 'dashboard'; require INCLUDES . 'admin_sidebar.php';
// =============================================================
$sidebarActive = $sidebarActive ?? '';
$user          = currentUser();
?>
<aside class="sidebar">
    <div class="admin-profile">
        <img src="/assets/img/candidates/AuditorMark.PNG" alt="Admin" class="profile-pic"
             onerror="this.style.background='#d5e8db'">
        <h3 class="admin-name"><?= htmlspecialchars($user['name']) ?></h3>
        <p class="admin-id">Admin ID: <?= htmlspecialchars($user['student_id']) ?></p>
    </div>
    <nav class="sidebar-menu">
        <a href="/admin/dashboard.php"   class="<?= $sidebarActive==='dashboard'   ? 'active':'' ?>">📊 Dashboard</a>
        <a href="/admin/verification.php" class="<?= $sidebarActive==='verification' ? 'active':'' ?>">✅ Validate Accounts</a>
        <a href="/admin/accounts.php"    class="<?= $sidebarActive==='accounts'    ? 'active':'' ?>">👥 Manage Accounts</a>
        <a href="/admin/elections.php"   class="<?= $sidebarActive==='elections'   ? 'active':'' ?>">🗳️ Manage Elections</a>
        <a href="/admin/candidates.php"  class="<?= $sidebarActive==='candidates'  ? 'active':'' ?>">🏅 Manage Candidates</a>
    </nav>
</aside>
