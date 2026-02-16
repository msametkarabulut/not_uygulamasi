<?php
require_once __DIR__ . '/../config/config.php';
require_login();
if (!is_admin()) {
    header('Location: ../notes.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/icons.php';

$folders = folders_with_counts();
$notes = load_notes();
$current_page = 'admin';
$page_title = 'Admin Dashboard';
$total_notes = count($notes);
$total_folders = count($folders);
$content = ob_start();
?>
<header class="page-header">
    <h2 class="text-lg font-semibold">Dashboard</h2>
    <div class="header-actions">
        <?php include __DIR__ . '/../includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="section-card flex items-center gap-4">
            <div style="width:3rem;height:3rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;background:hsl(var(--primary)/.1);color:hsl(var(--primary))"><?= icon_user('w-6 h-6') ?></div>
            <div>
                <p class="text-sm text-muted-foreground">Kullanıcı (oturum)</p>
                <p class="text-2xl font-bold">1</p>
            </div>
        </div>
        <div class="section-card flex items-center gap-4">
            <div style="width:3rem;height:3rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;background:hsl(var(--secondary)/.15);color:hsl(var(--secondary))"><?= icon_note('w-6 h-6') ?></div>
            <div>
                <p class="text-sm text-muted-foreground">Toplam Not</p>
                <p class="text-2xl font-bold"><?= $total_notes ?></p>
            </div>
        </div>
        <div class="section-card flex items-center gap-4">
            <div style="width:3rem;height:3rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;background:#dcfce7;color:#15803d"><?= icon_folder('w-6 h-6') ?></div>
            <div>
                <p class="text-sm text-muted-foreground">Klasör Sayısı</p>
                <p class="text-2xl font-bold"><?= $total_folders ?></p>
            </div>
        </div>
    </div>
    <div class="section-card mt-6">
        <h3>NoteFlow Yönetim</h3>
        <p class="section-desc">Firebase bağlandığında tüm kullanıcılar ve istatistikler burada listelenecek.</p>
    </div>
</div>
<?php
$content = ob_get_clean();
$admin_layout = true;
include __DIR__ . '/../includes/layout.php';
