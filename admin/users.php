<?php
require_once __DIR__ . '/../config/config.php';
require_login();
if (!is_admin()) {
    header('Location: ../notes.php');
    exit;
}
require_once __DIR__ . '/../includes/icons.php';
$user = current_user();
$current_page = 'admin_users';
$page_title = 'Kullanıcılar';
$content = ob_start();
?>
<header class="page-header">
    <h2 class="text-lg font-semibold">Kullanıcılar</h2>
    <div class="header-actions">
        <?php include __DIR__ . '/../includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <div class="section-card">
        <h3>Kullanıcı Listesi</h3>
        <p class="section-desc">Firebase Auth bağlandığında tüm kullanıcılar burada listelenecek.</p>
        <table class="w-full mt-4" style="border-collapse: collapse;">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Kullanıcı</th>
                    <th class="text-left py-2">E-posta</th>
                    <th class="text-left py-2">Rol</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b">
                    <td class="py-2"><?= htmlspecialchars($user['name']) ?></td>
                    <td class="py-2"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="py-2"><?= $user['role'] ?? 'user' ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
$admin_layout = true;
include __DIR__ . '/../includes/layout.php';
