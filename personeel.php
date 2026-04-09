<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

$filterZh = (int)($_GET['ziekenhuis'] ?? 0);
$filterFunctie = $_GET['functie'] ?? '';
$ziekenhuizen = alleZiekenhuizen($db);

$sql = 'SELECT p.*, z.naam as zh_naam FROM personeel p JOIN ziekenhuizen z ON p.ziekenhuis_id = z.id WHERE 1=1';
$params = [];

if ($filterZh) {
    $sql .= ' AND p.ziekenhuis_id = :zh';
    $params[':zh'] = $filterZh;
}
if ($filterFunctie) {
    $sql .= ' AND p.functie = :f';
    $params[':f'] = $filterFunctie;
}
$sql .= ' ORDER BY z.naam, p.functie, p.naam';

$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, is_int($v) ? SQLITE3_INTEGER : SQLITE3_TEXT);
}
$result = $stmt->execute();
$personeel = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $personeel[] = $row;
}

siteHeader('Personeel');
?>

<h1 class="pagina-titel">👨‍⚕️ Personeel</h1>
<p class="pagina-subtitel">De dokters en verpleging — de gezichten achter het systeem.</p>

<!-- Filters -->
<div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
    <a href="personeel.php" class="btn btn-small <?= (!$filterZh && !$filterFunctie) ? '' : 'btn-secondary' ?>">Alle</a>
    <a href="personeel.php?functie=dokter" class="btn btn-small <?= $filterFunctie === 'dokter' ? '' : 'btn-secondary' ?>">🩺 Dokters</a>
    <a href="personeel.php?functie=verpleging" class="btn btn-small <?= $filterFunctie === 'verpleging' ? '' : 'btn-secondary' ?>">💉 Verpleging</a>
</div>

<?php if (count($ziekenhuizen) > 1): ?>
<div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
    <?php foreach ($ziekenhuizen as $zh): ?>
        <a href="personeel.php?ziekenhuis=<?= $zh['id'] ?><?= $filterFunctie ? '&functie=' . e($filterFunctie) : '' ?>"
           class="btn btn-small <?= $filterZh === (int)$zh['id'] ? '' : 'btn-secondary' ?>">
            <?= e($zh['naam']) ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($personeel)): ?>
    <div class="leeg">
        <p>Geen personeelsleden gevonden<?= ($filterZh || $filterFunctie) ? ' met deze filters' : '' ?>. Ze zijn waarschijnlijk "in vergadering".</p>
    </div>
<?php else: ?>
    <div class="grid-3">
        <?php foreach ($personeel as $p): ?>
            <div class="kaart">
                <h3><?= e($p['naam']) ?></h3>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                    <span class="zh-badge"><?= $p['functie'] === 'dokter' ? '🩺' : '💉' ?> <?= e(ucfirst($p['functie'])) ?></span>
                    <a href="ziekenhuis.php?id=<?= $p['ziekenhuis_id'] ?>" class="zh-badge" style="text-decoration: none;">
                        🏥 <?= e($p['zh_naam']) ?>
                    </a>
                </div>
                <?php if ($p['notities']): ?>
                    <p class="tekst"><?= nl2br(e($p['notities'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php siteFooter(); ?>
