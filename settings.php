<?php
require_once __DIR__ . '/config/config.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/icons.php';

$folders = folders_with_counts();
$notes = load_notes();
$user = current_user();
$current_page = 'settings';
$page_title = 'Ayarlar';
$content = ob_start();
?>
<header class="page-header">
    <div class="flex items-center gap-3">
        <?= icon_settings('w-5 h-5') ?>
        <h2 class="text-lg font-semibold">Ayarlar</h2>
    </div>
    <div class="header-actions">
        <?php include __DIR__ . '/includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <div class="max-w-2xl">
        <section class="section-card">
            <h3>Profil Bilgileri</h3>
            <p class="section-desc">Hesabınıza ait temel bilgileri güncelleyin.</p>
            <form method="post" action="settings_save.php" class="mt-5">
                <div class="form-group">
                    <label for="name">Ad Soyad</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </form>
        </section>

        <?php
        $fb_active = function_exists('_use_firestore') && _use_firestore();
        ?>
        <section class="section-card">
            <h3>Firebase Bağlantısı</h3>
            <p class="section-desc">Verilerinizi buluta senkronize edin.</p>
            <div class="flex gap-4 items-center mt-5">
                <div class="flex items-center gap-2 rounded-lg bg-muted" style="height:2.25rem;padding:0 .875rem">
                    <span style="width:8px;height:8px;border-radius:50%;background:<?= $fb_active ? '#22c55e' : '#eab308' ?>"></span>
                    <span class="text-sm"><?= $fb_active ? 'Firestore Bağlı' : 'Yerel Depolama' ?></span>
                </div>
                <span class="text-sm text-muted-foreground"><?= $fb_active ? 'Veriler Firebase Firestore ile senkronize.' : 'Service account JSON dosyasını ekleyin.' ?></span>
            </div>
        </section>

        <section class="section-card" style="border-color: hsl(var(--destructive) / 0.3);">
            <h3 style="color: hsl(var(--destructive))">Tehlikeli Bölge</h3>
            <p class="section-desc">Bu işlemler geri alınamaz.</p>
            <div class="flex flex-col gap-3 mt-5">
                <form method="post" action="note_delete_all.php" onsubmit="return confirm('Tüm notlarınızı silmek istediğinize emin misiniz?');">
                    <button type="submit" class="btn btn-ghost" style="border-color: hsl(var(--destructive)/0.3); color: hsl(var(--destructive))">Tüm Notları Sil</button>
                </form>
            </div>
        </section>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
