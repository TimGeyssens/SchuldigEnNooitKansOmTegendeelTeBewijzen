<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

// Admin: podcast toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    $titel = trim($_POST['titel'] ?? '');
    $url = trim($_POST['youtube_url'] ?? '');
    $beschrijving = trim($_POST['beschrijving'] ?? '');

    if ($titel && $url && youtubeId($url)) {
        $stmt = $db->prepare('INSERT INTO podcasts (titel, youtube_url, beschrijving) VALUES (:t, :u, :b)');
        $stmt->bindValue(':t', $titel, SQLITE3_TEXT);
        $stmt->bindValue(':u', $url, SQLITE3_TEXT);
        $stmt->bindValue(':b', $beschrijving, SQLITE3_TEXT);
        $stmt->execute();
        setFlash('Podcast toegevoegd! 🎙️');
    } else {
        setFlash('Vul alle velden in en gebruik een geldige YouTube URL.', 'error');
    }
    redirect('podcasts.php');
}

$podcasts = [];
$result = $db->query('SELECT * FROM podcasts ORDER BY datum DESC');
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $podcasts[] = $row;
}

siteHeader('Podcasts');
?>

<h1 class="pagina-titel">📻 Podcasts</h1>
<p class="pagina-subtitel">Twee patiënten, één microfoon, en een systeem dat liever niet luistert.</p>

<?php if (isAdmin()): ?>
<div class="formulier">
    <h3>🎙️ Nieuwe Podcast Toevoegen</h3>
    <form method="post">
        <label for="titel">Titel</label>
        <input type="text" name="titel" id="titel" placeholder="Aflevering 1: Waarom mogen we geen veters?" required>

        <label for="youtube_url">YouTube URL</label>
        <input type="url" name="youtube_url" id="youtube_url" placeholder="https://youtube.com/watch?v=..." required>

        <label for="beschrijving">Beschrijving (optioneel)</label>
        <textarea name="beschrijving" id="beschrijving" placeholder="Waar gaat deze aflevering over?"></textarea>

        <button type="submit" class="btn">Toevoegen 🎬</button>
    </form>
</div>
<?php endif; ?>

<?php if (empty($podcasts)): ?>
    <div class="leeg">
        <p>Nog geen podcasts... maar we zijn aan het opnemen! Waarschijnlijk met een kapotte microfoon.</p>
    </div>
<?php else: ?>
    <div class="grid-2">
        <?php foreach ($podcasts as $pod): ?>
            <div class="kaart">
                <?php
                $vid = youtubeId($pod['youtube_url']);
                if ($vid): ?>
                    <div class="podcast-embed">
                        <iframe src="https://www.youtube.com/embed/<?= e($vid) ?>"
                                allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    </div>
                <?php endif; ?>
                <h3><?= e($pod['titel']) ?></h3>
                <p class="meta">📅 <?= date('d/m/Y', strtotime($pod['datum'])) ?></p>
                <?php if ($pod['beschrijving']): ?>
                    <p class="tekst"><?= nl2br(e($pod['beschrijving'])) ?></p>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                    <form method="post" action="admin.php" style="margin-top:0.5rem">
                        <input type="hidden" name="actie" value="verwijder_podcast">
                        <input type="hidden" name="id" value="<?= $pod['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Weet je het zeker?')">Verwijder</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php siteFooter(); ?>
