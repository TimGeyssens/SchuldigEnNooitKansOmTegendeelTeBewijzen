<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Check of admin is ingelogd
 */
function isAdmin(): bool {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Veilig tekst outputten
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Haal YouTube video ID uit een URL
 */
function youtubeId(string $url): string {
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
        '/youtu\.be\/([a-zA-Z0-9_-]+)/',
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $m)) {
            return $m[1];
        }
    }
    return '';
}

/**
 * Haal alle ziekenhuizen op (voor dropdowns)
 */
function alleZiekenhuizen(SQLite3 $db): array {
    $result = $db->query('SELECT id, naam, stad FROM ziekenhuizen ORDER BY naam');
    $items = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

/**
 * Flash berichten
 */
function setFlash(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * HTML header
 */
function siteHeader(string $titel = '', string $extraClass = ''): void {
    $paginaTitel = $titel ? e($titel) . ' | ' . SITE_NAAM : SITE_NAAM;
    ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $paginaTitel ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= e($extraClass) ?>">
    <div class="alles-is-fine-banner">
        <span>🏥 ALLES IS FINE 🏥 NIETS AAN DE HAND 🏥 ALLES IS FINE 🏥 NIETS AAN DE HAND 🏥 ALLES IS FINE 🏥</span>
    </div>

    <nav class="hoofdnav">
        <a href="index.php" class="nav-logo">S.E.N.K.O.T.T.B.</a>
        <div class="nav-links">
            <a href="podcasts.php">📻 Podcasts</a>
            <a href="getuigenissen.php">📝 Getuigenissen</a>
            <a href="ziekenhuizen.php">🏥 Ziekenhuizen</a>
            <a href="personeel.php">👨‍⚕️ Personeel</a>
            <a href="forum.php">💬 Forum</a>
            <a href="patientenrechten.php">⚖️ Rechten</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="nav-admin">🔑 Admin</a>
            <?php endif; ?>
        </div>
        <button class="hamburger" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</button>
    </nav>

    <main class="content">
    <?php
    $flash = getFlash();
    if ($flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif;
}

/**
 * HTML footer
 */
function siteFooter(): void {
    ?>
    </main>

    <footer class="site-footer">
        <div class="footer-stempel">
            <p>GOEDGEKEURD DOOR NIEMAND</p>
            <p class="footer-small">Want niemand luistert</p>
        </div>
        <p class="footer-tekst">
            <?= SITE_NAAM ?> &mdash; <?= SITE_SUBTITEL ?>
        </p>
        <p class="footer-tekst footer-small">
            <?php if (!isAdmin()): ?>
                <a href="admin.php">Admin</a> |
            <?php endif; ?>
            &copy; <?= date('Y') ?> &mdash; Alle rechten voorbehouden (maar wie controleert dat?)
        </p>
    </footer>

    <div class="bureaucratie-watermark">VERTROUWELIJK DOSSIER NR. <?= rand(10000, 99999) ?></div>
</body>
</html>
<?php
}
