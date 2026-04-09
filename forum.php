<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

// Nieuw topic aanmaken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = trim($_POST['titel'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '') ?: 'Anoniem';
    $ziekenhuis_id = (int)($_POST['ziekenhuis_id'] ?? 0);
    $tekst = trim($_POST['tekst'] ?? '');

    if ($titel && $ziekenhuis_id && $tekst) {
        $stmt = $db->prepare('INSERT INTO forum_topics (titel, auteur, ziekenhuis_id, tekst) VALUES (:ti, :a, :z, :te)');
        $stmt->bindValue(':ti', $titel, SQLITE3_TEXT);
        $stmt->bindValue(':a', $auteur, SQLITE3_TEXT);
        $stmt->bindValue(':z', $ziekenhuis_id, SQLITE3_INTEGER);
        $stmt->bindValue(':te', $tekst, SQLITE3_TEXT);
        $stmt->execute();
        $newId = $db->lastInsertRowID();
        setFlash('Topic aangemaakt! 💬');
        redirect('topic.php?id=' . $newId);
    } else {
        setFlash('Vul alle verplichte velden in (titel, ziekenhuis, vraag).', 'error');
    }
    redirect('forum.php');
}

$ziekenhuizen = alleZiekenhuizen($db);
$filterZh = (int)($_GET['ziekenhuis'] ?? 0);

if ($filterZh) {
    $stmt = $db->prepare('
        SELECT ft.*, z.naam as zh_naam,
            (SELECT COUNT(*) FROM forum_replies fr WHERE fr.topic_id = ft.id) as aantal_antwoorden
        FROM forum_topics ft
        JOIN ziekenhuizen z ON ft.ziekenhuis_id = z.id
        WHERE ft.ziekenhuis_id = :zh
        ORDER BY ft.datum DESC
    ');
    $stmt->bindValue(':zh', $filterZh, SQLITE3_INTEGER);
} else {
    $stmt = $db->prepare('
        SELECT ft.*, z.naam as zh_naam,
            (SELECT COUNT(*) FROM forum_replies fr WHERE fr.topic_id = ft.id) as aantal_antwoorden
        FROM forum_topics ft
        JOIN ziekenhuizen z ON ft.ziekenhuis_id = z.id
        ORDER BY ft.datum DESC
    ');
}
$result = $stmt->execute();
$topics = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $topics[] = $row;
}

siteHeader('Forum');
?>

<h1 class="pagina-titel">💬 Forum</h1>
<p class="pagina-subtitel">Vragen over patiëntenrechten? Stel ze hier. Anoniem mag. Ziekenhuis is verplicht.</p>

<!-- Filter -->
<?php if (count($ziekenhuizen) > 0): ?>
<div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
    <a href="forum.php" class="btn btn-small <?= !$filterZh ? '' : 'btn-secondary' ?>">Alle</a>
    <?php foreach ($ziekenhuizen as $zh): ?>
        <a href="forum.php?ziekenhuis=<?= $zh['id'] ?>"
           class="btn btn-small <?= $filterZh === (int)$zh['id'] ? '' : 'btn-secondary' ?>">
            <?= e($zh['naam']) ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Topics -->
<?php if (empty($topics)): ?>
    <div class="leeg">
        <p>Nog geen topics<?= $filterZh ? ' voor dit ziekenhuis' : '' ?>. Stel de eerste vraag!</p>
    </div>
<?php else: ?>
    <?php foreach ($topics as $t): ?>
        <a href="topic.php?id=<?= $t['id'] ?>" class="forum-topic">
            <h3><?= e($t['titel']) ?></h3>
            <div class="meta">
                Door <?= e($t['auteur']) ?> |
                🏥 <?= e($t['zh_naam']) ?> |
                <?= date('d/m/Y H:i', strtotime($t['datum'])) ?> |
                💬 <?= $t['aantal_antwoorden'] ?> antwoord<?= $t['aantal_antwoorden'] !== 1 ? 'en' : '' ?>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Nieuw topic -->
<div class="formulier">
    <h3>📝 Nieuwe Vraag Stellen</h3>
    <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.9rem;">
        Anoniem mag. Ziekenhuis is verplicht zodat we de context kennen.
    </p>
    <form method="post">
        <label for="titel">Titel van je vraag *</label>
        <input type="text" name="titel" id="titel" placeholder="Mogen ze mijn GSM afpakken?" required>

        <label for="auteur">Naam (optioneel — laat leeg voor anoniem)</label>
        <input type="text" name="auteur" id="auteur" placeholder="Anoniem">

        <label for="ziekenhuis_id">Ziekenhuis *</label>
        <select name="ziekenhuis_id" id="ziekenhuis_id" required>
            <option value="">-- Kies een ziekenhuis --</option>
            <?php foreach ($ziekenhuizen as $zh): ?>
                <option value="<?= $zh['id'] ?>"><?= e($zh['naam']) ?> (<?= e($zh['stad']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($ziekenhuizen)): ?>
            <p style="color: var(--accent2); font-size: 0.85rem;">⚠️ Er zijn nog geen ziekenhuizen toegevoegd.</p>
        <?php endif; ?>

        <label for="tekst">Je vraag *</label>
        <textarea name="tekst" id="tekst" placeholder="Beschrijf je situatie of vraag..." required></textarea>

        <button type="submit" class="btn">Vraag Stellen 📮</button>
    </form>
</div>

<?php siteFooter(); ?>
