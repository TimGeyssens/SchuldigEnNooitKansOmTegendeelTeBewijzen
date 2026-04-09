<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('ziekenhuizen.php');
}

$stmt = $db->prepare('SELECT * FROM ziekenhuizen WHERE id = :id');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$zh = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$zh) {
    redirect('ziekenhuizen.php');
}

// Getuigenissen
$stmt = $db->prepare('SELECT * FROM getuigenissen WHERE ziekenhuis_id = :id AND goedgekeurd = 1 ORDER BY datum DESC');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$getuigenissen = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $getuigenissen[] = $row;
}

// Personeel
$stmt = $db->prepare('SELECT * FROM personeel WHERE ziekenhuis_id = :id ORDER BY functie, naam');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$personeel = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $personeel[] = $row;
}

// Forum topics
$stmt = $db->prepare('SELECT * FROM forum_topics WHERE ziekenhuis_id = :id ORDER BY datum DESC LIMIT 10');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$topics = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $topics[] = $row;
}

siteHeader($zh['naam']);
?>

<h1 class="pagina-titel">🏥 <?= e($zh['naam']) ?></h1>
<p class="pagina-subtitel">
    📍 <?= e($zh['stad']) ?>
    <?= $zh['provincie'] ? '(' . e($zh['provincie']) . ')' : '' ?>
    <?php if ($zh['type']): ?>
        — <span class="stempel"><?= e(strtoupper($zh['type'])) ?></span>
    <?php endif; ?>
</p>

<?php if ($zh['notities']): ?>
    <div class="kaart">
        <h3>📋 Notities</h3>
        <p class="tekst"><?= nl2br(e($zh['notities'])) ?></p>
    </div>
<?php endif; ?>

<!-- Getuigenissen -->
<h2 style="color: var(--accent2); margin: 2rem 0 1rem; font-family: 'Permanent Marker', cursive;">
    📝 Getuigenissen (<?= count($getuigenissen) ?>)
</h2>

<?php if (empty($getuigenissen)): ?>
    <p style="color: var(--text-muted); font-style: italic;">Nog geen getuigenissen voor dit ziekenhuis.
        <a href="getuigenissen.php" style="color: var(--accent);">Wees de eerste →</a>
    </p>
<?php else: ?>
    <?php foreach ($getuigenissen as $g): ?>
        <div class="dossier" data-id="<?= $g['id'] ?>">
            <div class="inhoud"><?= nl2br(e($g['tekst'])) ?></div>
            <div class="auteur">— <?= e($g['auteur']) ?> | <?= date('d/m/Y', strtotime($g['datum'])) ?></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Personeel -->
<h2 style="color: var(--accent2); margin: 2rem 0 1rem; font-family: 'Permanent Marker', cursive;">
    👨‍⚕️ Personeel (<?= count($personeel) ?>)
</h2>

<?php if (empty($personeel)): ?>
    <p style="color: var(--text-muted); font-style: italic;">Nog geen personeelsleden geregistreerd.</p>
<?php else: ?>
    <div class="grid-3">
        <?php foreach ($personeel as $p): ?>
            <div class="kaart">
                <h3><?= e($p['naam']) ?></h3>
                <span class="zh-badge"><?= e(ucfirst($p['functie'])) ?></span>
                <?php if ($p['notities']): ?>
                    <p class="tekst" style="margin-top:0.5rem;"><?= nl2br(e($p['notities'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Forum topics -->
<h2 style="color: var(--accent2); margin: 2rem 0 1rem; font-family: 'Permanent Marker', cursive;">
    💬 Forum Topics (<?= count($topics) ?>)
</h2>

<?php if (empty($topics)): ?>
    <p style="color: var(--text-muted); font-style: italic;">Nog geen forum topics over dit ziekenhuis.
        <a href="forum.php" style="color: var(--accent);">Start een gesprek →</a>
    </p>
<?php else: ?>
    <?php foreach ($topics as $t): ?>
        <a href="topic.php?id=<?= $t['id'] ?>" class="forum-topic">
            <h3><?= e($t['titel']) ?></h3>
            <div class="meta">
                Door <?= e($t['auteur']) ?> | <?= date('d/m/Y H:i', strtotime($t['datum'])) ?>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>

<div style="margin-top: 2rem;">
    <a href="ziekenhuizen.php" class="btn btn-secondary">← Terug naar alle ziekenhuizen</a>
</div>

<?php siteFooter(); ?>
