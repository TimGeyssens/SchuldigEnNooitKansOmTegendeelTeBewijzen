<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

// Getuigenis insturen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auteur = trim($_POST['auteur'] ?? '') ?: 'Anoniem';
    $ziekenhuis_id = (int)($_POST['ziekenhuis_id'] ?? 0);
    $tekst = trim($_POST['tekst'] ?? '');

    if ($ziekenhuis_id && $tekst) {
        $stmt = $db->prepare('INSERT INTO getuigenissen (auteur, ziekenhuis_id, tekst) VALUES (:a, :z, :t)');
        $stmt->bindValue(':a', $auteur, SQLITE3_TEXT);
        $stmt->bindValue(':z', $ziekenhuis_id, SQLITE3_INTEGER);
        $stmt->bindValue(':t', $tekst, SQLITE3_TEXT);
        $stmt->execute();
        setFlash('Bedankt voor je getuigenis! 📋 Die wordt bekeken voor publicatie. (Ja, er is wél iemand die luistert hier.)');
    } else {
        setFlash('Vul minstens een ziekenhuis en je verhaal in.', 'error');
    }
    redirect('getuigenissen.php');
}

// Filter op ziekenhuis
$filterZh = (int)($_GET['ziekenhuis'] ?? 0);
$ziekenhuizen = alleZiekenhuizen($db);

if ($filterZh) {
    $stmt = $db->prepare('SELECT g.*, z.naam as zh_naam FROM getuigenissen g JOIN ziekenhuizen z ON g.ziekenhuis_id = z.id WHERE g.goedgekeurd = 1 AND g.ziekenhuis_id = :zh ORDER BY g.datum DESC');
    $stmt->bindValue(':zh', $filterZh, SQLITE3_INTEGER);
} else {
    $stmt = $db->prepare('SELECT g.*, z.naam as zh_naam FROM getuigenissen g JOIN ziekenhuizen z ON g.ziekenhuis_id = z.id WHERE g.goedgekeurd = 1 ORDER BY g.datum DESC');
}
$result = $stmt->execute();
$getuigenissen = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $getuigenissen[] = $row;
}

siteHeader('Getuigenissen');
?>

<h1 class="pagina-titel">📝 Getuigenissen</h1>
<p class="pagina-subtitel">Echte verhalen. Echte patiënten. Echt niet normaal.</p>

<!-- Filter -->
<?php if (count($ziekenhuizen) > 0): ?>
<div style="margin-bottom: 1.5rem;">
    <a href="getuigenissen.php" class="btn btn-small <?= !$filterZh ? '' : 'btn-secondary' ?>">Alle</a>
    <?php foreach ($ziekenhuizen as $zh): ?>
        <a href="getuigenissen.php?ziekenhuis=<?= $zh['id'] ?>"
           class="btn btn-small <?= $filterZh === (int)$zh['id'] ? '' : 'btn-secondary' ?>">
            <?= e($zh['naam']) ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Getuigenissen tonen -->
<?php if (empty($getuigenissen)): ?>
    <div class="leeg">
        <p>Nog geen goedgekeurde getuigenissen<?= $filterZh ? ' voor dit ziekenhuis' : '' ?>. Wees de eerste die het stilzwijgen doorbreekt!</p>
    </div>
<?php else: ?>
    <?php foreach ($getuigenissen as $g): ?>
        <div class="dossier" data-id="<?= $g['id'] ?>">
            <div style="margin-bottom:0.5rem;">
                <span class="zh-badge">🏥 <?= e($g['zh_naam']) ?></span>
            </div>
            <div class="inhoud">
                <?= nl2br(e($g['tekst'])) ?>
            </div>
            <div class="auteur">
                — <?= e($g['auteur']) ?> | <?= date('d/m/Y', strtotime($g['datum'])) ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Instuurformulier -->
<div class="formulier">
    <h3>📋 Jouw Getuigenis Insturen</h3>
    <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.9rem;">
        Je mag anoniem blijven. Je verhaal wordt eerst bekeken voor het gepubliceerd wordt.
        Ziekenhuis is verplicht — we moeten weten waar het fout loopt.
    </p>
    <form method="post">
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
            <p style="color: var(--accent2); font-size: 0.85rem;">⚠️ Er zijn nog geen ziekenhuizen toegevoegd. Contacteer de admin.</p>
        <?php endif; ?>

        <label for="tekst">Jouw Verhaal *</label>
        <textarea name="tekst" id="tekst" placeholder="Vertel wat je hebt meegemaakt..." required></textarea>

        <button type="submit" class="btn">Verstuur Getuigenis 📮</button>
    </form>
</div>

<?php siteFooter(); ?>
