<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

$result = $db->query('
    SELECT z.*,
        (SELECT COUNT(*) FROM getuigenissen g WHERE g.ziekenhuis_id = z.id AND g.goedgekeurd = 1) as aantal_getuigenissen,
        (SELECT COUNT(*) FROM personeel p WHERE p.ziekenhuis_id = z.id) as aantal_personeel,
        (SELECT COUNT(*) FROM forum_topics ft WHERE ft.ziekenhuis_id = z.id) as aantal_topics
    FROM ziekenhuizen z
    ORDER BY z.naam
');
$ziekenhuizen = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $ziekenhuizen[] = $row;
}

siteHeader('Ziekenhuizen');
?>

<h1 class="pagina-titel">🏥 Ziekenhuizen</h1>
<p class="pagina-subtitel">De "zorginstellingen" waar het allemaal gebeurt. Klik door voor details.</p>

<?php if (empty($ziekenhuizen)): ?>
    <div class="leeg">
        <p>Nog geen ziekenhuizen toegevoegd. De admin is er waarschijnlijk mee bezig. Of niet. Wie zal het zeggen.</p>
    </div>
<?php else: ?>
    <div class="grid-3">
        <?php foreach ($ziekenhuizen as $zh): ?>
            <a href="ziekenhuis.php?id=<?= $zh['id'] ?>" class="sectie-link shake-hover">
                <h3>🏥 <?= e($zh['naam']) ?></h3>
                <p>
                    📍 <?= e($zh['stad']) ?>
                    <?= $zh['provincie'] ? '(' . e($zh['provincie']) . ')' : '' ?>
                </p>
                <p style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                    📝 <?= $zh['aantal_getuigenissen'] ?> getuigenis<?= $zh['aantal_getuigenissen'] !== 1 ? 'sen' : '' ?> |
                    👨‍⚕️ <?= $zh['aantal_personeel'] ?> personeelsleden |
                    💬 <?= $zh['aantal_topics'] ?> topics
                </p>
                <?php if ($zh['type']): ?>
                    <span class="stempel" style="margin-top:0.5rem;"><?= e(strtoupper($zh['type'])) ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php siteFooter(); ?>
