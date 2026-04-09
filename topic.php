<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('forum.php');
}

// Topic ophalen
$stmt = $db->prepare('SELECT ft.*, z.naam as zh_naam FROM forum_topics ft JOIN ziekenhuizen z ON ft.ziekenhuis_id = z.id WHERE ft.id = :id');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$topic = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$topic) {
    redirect('forum.php');
}

// Antwoord plaatsen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auteur = trim($_POST['auteur'] ?? '') ?: 'Anoniem';
    $tekst = trim($_POST['tekst'] ?? '');

    if ($tekst) {
        $stmt = $db->prepare('INSERT INTO forum_replies (topic_id, auteur, tekst) VALUES (:t, :a, :te)');
        $stmt->bindValue(':t', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':a', $auteur, SQLITE3_TEXT);
        $stmt->bindValue(':te', $tekst, SQLITE3_TEXT);
        $stmt->execute();
        setFlash('Antwoord geplaatst! 💬');
    } else {
        setFlash('Schrijf een antwoord.', 'error');
    }
    redirect('topic.php?id=' . $id);
}

// Antwoorden ophalen
$stmt = $db->prepare('SELECT * FROM forum_replies WHERE topic_id = :id ORDER BY datum ASC');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$replies = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $replies[] = $row;
}

siteHeader($topic['titel']);
?>

<div style="margin-bottom: 1rem;">
    <a href="forum.php" class="btn btn-secondary btn-small">← Terug naar forum</a>
</div>

<div class="kaart">
    <h2 style="font-family: 'Permanent Marker', cursive; color: var(--accent); margin-bottom: 0.5rem;">
        <?= e($topic['titel']) ?>
    </h2>
    <div class="meta">
        Door <strong><?= e($topic['auteur']) ?></strong> |
        <span class="zh-badge">🏥 <?= e($topic['zh_naam']) ?></span> |
        <?= date('d/m/Y H:i', strtotime($topic['datum'])) ?>
    </div>
    <div class="tekst" style="margin-top: 1rem; line-height: 1.8;">
        <?= nl2br(e($topic['tekst'])) ?>
    </div>
</div>

<!-- Antwoorden -->
<h3 style="color: var(--accent2); font-family: 'Permanent Marker', cursive; margin: 1.5rem 0 1rem;">
    💬 Antwoorden (<?= count($replies) ?>)
</h3>

<?php if (empty($replies)): ?>
    <p style="color: var(--text-muted); font-style: italic;">Nog geen antwoorden. Wees de eerste die reageert!</p>
<?php else: ?>
    <?php foreach ($replies as $r): ?>
        <div class="forum-reply">
            <div class="meta">
                <strong><?= e($r['auteur']) ?></strong> | <?= date('d/m/Y H:i', strtotime($r['datum'])) ?>
            </div>
            <div style="line-height: 1.7;">
                <?= nl2br(e($r['tekst'])) ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Antwoord formulier -->
<div class="formulier">
    <h3>✏️ Reageer</h3>
    <form method="post">
        <label for="auteur">Naam (optioneel)</label>
        <input type="text" name="auteur" id="auteur" placeholder="Anoniem">

        <label for="tekst">Jouw antwoord *</label>
        <textarea name="tekst" id="tekst" placeholder="Deel je kennis of ervaring..." required></textarea>

        <button type="submit" class="btn">Antwoord Plaatsen 📮</button>
    </form>
</div>

<?php siteFooter(); ?>
