<?php
require_once __DIR__ . '/config/config.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/icons.php';

$user = current_user();
$folders = folders_with_counts();
$notes = load_notes();
$favorite_count = count(array_filter($notes, function ($n) { return !empty($n['isFavorite']); }));
$current_page = 'profile';
$page_title = 'Profil';
$content = ob_start();
$initials = implode('', array_map(function ($s) { return mb_substr($s, 0, 1); }, preg_split('/\s+/', $user['name'] ?? 'Kullanıcı', 2)));
$initials = mb_strtoupper($initials ?: 'K');
?>
<header class="page-header">
    <div class="flex items-center gap-3">
        <?= icon_user('w-5 h-5') ?>
        <h2 class="text-lg font-semibold">Profil</h2>
    </div>
    <div class="header-actions">
        <?php include __DIR__ . '/includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <div class="max-w-2xl">
        <div class="section-card">
            <div class="flex gap-6 items-start">
                <div class="user-avatar" style="width:5rem;height:5rem;border-radius:9999px;background:hsl(var(--primary));color:hsl(var(--primary-foreground));display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;border:4px solid hsl(var(--primary)/0.2)"><?= $initials ?></div>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($user['name']) ?></h3>
                        <span class="badge" style="background:hsl(var(--primary));color:white;padding:0.25rem 0.5rem;border-radius:0.25rem;font-size:0.75rem"><?= icon_shield('w-3 h-3') ?> <?= ($user['role'] ?? '') === 'admin' ? 'Admin' : 'Kullanıcı' ?></span>
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground"><?= htmlspecialchars($user['email']) ?></p>
                    <p class="mt-1 text-sm text-muted-foreground"><?= count($notes) ?> not · <?= $favorite_count ?> favori · <?= count($folders) ?> klasör</p>
                </div>
            </div>
        </div>
        <div class="section-card">
            <h3>Profili Düzenle</h3>
            <form method="post" action="settings_save.php">
                <div class="form-group">
                    <label for="name">Ad Soyad</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Güncelle</button>
            </form>
        </div>
        <div class="flex gap-4 mt-6">
            <div class="section-card flex-1 text-center">
                <span class="text-3xl font-bold text-primary"><?= count($notes) ?></span>
                <p class="mt-1 text-sm text-muted-foreground">Toplam Not</p>
            </div>
            <div class="section-card flex-1 text-center">
                <span class="text-3xl font-bold text-secondary"><?= $favorite_count ?></span>
                <p class="mt-1 text-sm text-muted-foreground">Favori</p>
            </div>
            <div class="section-card flex-1 text-center">
                <span class="text-3xl font-bold"><?= count($folders) ?></span>
                <p class="mt-1 text-sm text-muted-foreground">Klasör</p>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
