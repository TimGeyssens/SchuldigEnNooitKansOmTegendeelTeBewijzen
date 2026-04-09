<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

// ---- ACTIES (POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actie = $_POST['actie'] ?? '';

    // Login
    if ($actie === 'login') {
        if (($_POST['wachtwoord'] ?? '') === ADMIN_PASSWORD) {
            $_SESSION['admin'] = true;
            setFlash('Welkom, geheim agent! 🕵️');
        } else {
            setFlash('Fout wachtwoord. Probeer het opnieuw, of niet.', 'error');
        }
        redirect('admin.php');
    }

    // Logout
    if ($actie === 'logout') {
        unset($_SESSION['admin']);
        setFlash('Uitgelogd. Niemand zal weten dat je hier was.');
        redirect('index.php');
    }

    // Alles hieronder vereist admin
    if (!isAdmin()) {
        redirect('admin.php');
    }

    // Ziekenhuis toevoegen
    if ($actie === 'add_ziekenhuis') {
        $naam = trim($_POST['naam'] ?? '');
        $stad = trim($_POST['stad'] ?? '');
        $provincie = trim($_POST['provincie'] ?? '');
        $type = trim($_POST['type'] ?? 'psychiatrisch');
        $notities = trim($_POST['notities'] ?? '');

        if ($naam && $stad) {
            $stmt = $db->prepare('INSERT INTO ziekenhuizen (naam, stad, provincie, type, notities) VALUES (:n, :s, :p, :t, :no)');
            $stmt->bindValue(':n', $naam, SQLITE3_TEXT);
            $stmt->bindValue(':s', $stad, SQLITE3_TEXT);
            $stmt->bindValue(':p', $provincie, SQLITE3_TEXT);
            $stmt->bindValue(':t', $type, SQLITE3_TEXT);
            $stmt->bindValue(':no', $notities, SQLITE3_TEXT);
            $stmt->execute();
            setFlash('Ziekenhuis toegevoegd! 🏥');
        } else {
            setFlash('Naam en stad zijn verplicht.', 'error');
        }
        redirect('admin.php');
    }

    // Ziekenhuis verwijderen
    if ($actie === 'verwijder_ziekenhuis') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("DELETE FROM getuigenissen WHERE ziekenhuis_id = $id");
            $db->exec("DELETE FROM personeel WHERE ziekenhuis_id = $id");
            $db->exec("DELETE FROM forum_replies WHERE topic_id IN (SELECT id FROM forum_topics WHERE ziekenhuis_id = $id)");
            $db->exec("DELETE FROM forum_topics WHERE ziekenhuis_id = $id");
            $db->exec("DELETE FROM ziekenhuizen WHERE id = $id");
            setFlash('Ziekenhuis en alle gerelateerde data verwijderd.');
        }
        redirect('admin.php');
    }

    // Personeel toevoegen
    if ($actie === 'add_personeel') {
        $naam = trim($_POST['naam'] ?? '');
        $functie = trim($_POST['functie'] ?? 'dokter');
        $zh_id = (int)($_POST['ziekenhuis_id'] ?? 0);
        $notities = trim($_POST['notities'] ?? '');

        if ($naam && $zh_id) {
            $stmt = $db->prepare('INSERT INTO personeel (naam, functie, ziekenhuis_id, notities) VALUES (:n, :f, :z, :no)');
            $stmt->bindValue(':n', $naam, SQLITE3_TEXT);
            $stmt->bindValue(':f', $functie, SQLITE3_TEXT);
            $stmt->bindValue(':z', $zh_id, SQLITE3_INTEGER);
            $stmt->bindValue(':no', $notities, SQLITE3_TEXT);
            $stmt->execute();
            setFlash('Personeelslid toegevoegd! 👨‍⚕️');
        } else {
            setFlash('Naam en ziekenhuis zijn verplicht.', 'error');
        }
        redirect('admin.php');
    }

    // Personeel verwijderen
    if ($actie === 'verwijder_personeel') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("DELETE FROM personeel WHERE id = $id");
            setFlash('Personeelslid verwijderd.');
        }
        redirect('admin.php');
    }

    // Getuigenis goedkeuren
    if ($actie === 'goedkeuren') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("UPDATE getuigenissen SET goedgekeurd = 1 WHERE id = $id");
            setFlash('Getuigenis goedgekeurd! ✅');
        }
        redirect('admin.php');
    }

    // Getuigenis afwijzen/verwijderen
    if ($actie === 'afwijzen') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("DELETE FROM getuigenissen WHERE id = $id");
            setFlash('Getuigenis verwijderd.');
        }
        redirect('admin.php');
    }

    // Podcast verwijderen
    if ($actie === 'verwijder_podcast') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("DELETE FROM podcasts WHERE id = $id");
            setFlash('Podcast verwijderd.');
        }
        redirect('admin.php');
    }

    // Forum topic verwijderen
    if ($actie === 'verwijder_topic') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("DELETE FROM forum_replies WHERE topic_id = $id");
            $db->exec("DELETE FROM forum_topics WHERE id = $id");
            setFlash('Forum topic verwijderd.');
        }
        redirect('admin.php');
    }

    // Forum reply verwijderen
    if ($actie === 'verwijder_reply') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("DELETE FROM forum_replies WHERE id = $id");
            setFlash('Antwoord verwijderd.');
        }
        redirect('admin.php');
    }
}

// ---- WEERGAVE ----
siteHeader('Admin');

if (!isAdmin()):
?>

<div class="login-box">
    <h1 class="pagina-titel">🔑 Admin Login</h1>
    <p class="pagina-subtitel">Alleen voor geautoriseerd personeel. (Ha! Alsof dat ooit werkt.)</p>

    <div class="formulier">
        <form method="post">
            <input type="hidden" name="actie" value="login">
            <label for="wachtwoord">Wachtwoord</label>
            <input type="password" name="wachtwoord" id="wachtwoord" placeholder="Het geheime woord..." required>
            <button type="submit" class="btn">Inloggen 🕵️</button>
        </form>
    </div>
</div>

<?php else: ?>

<h1 class="pagina-titel">🔑 Admin Panel</h1>
<p class="pagina-subtitel">Het geheime hoofdkwartier. Beheer alles van hieruit.</p>

<form method="post" style="margin-bottom: 1.5rem;">
    <input type="hidden" name="actie" value="logout">
    <button type="submit" class="btn btn-secondary btn-small">Uitloggen 🚪</button>
</form>

<div class="admin-grid">

    <!-- ZIEKENHUIZEN BEHEER -->
    <div class="admin-sectie">
        <h3>🏥 Ziekenhuizen</h3>
        <form method="post" style="margin-bottom: 1rem;">
            <input type="hidden" name="actie" value="add_ziekenhuis">
            <input type="text" name="naam" placeholder="Naam ziekenhuis *" required style="width:100%;padding:0.5rem;margin-bottom:0.3rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;">
            <input type="text" name="stad" placeholder="Stad *" required style="width:100%;padding:0.5rem;margin-bottom:0.3rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;">
            <input type="text" name="provincie" placeholder="Provincie" style="width:100%;padding:0.5rem;margin-bottom:0.3rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;">
            <select name="type" style="width:100%;padding:0.5rem;margin-bottom:0.3rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;">
                <option value="psychiatrisch">Psychiatrisch</option>
                <option value="algemeen">Algemeen</option>
                <option value="revalidatie">Revalidatie</option>
                <option value="forensisch">Forensisch</option>
            </select>
            <textarea name="notities" placeholder="Notities (optioneel)" style="width:100%;padding:0.5rem;margin-bottom:0.5rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;min-height:60px;"></textarea>
            <button type="submit" class="btn btn-small">Toevoegen</button>
        </form>

        <?php
        $result = $db->query('SELECT * FROM ziekenhuizen ORDER BY naam');
        while ($zh = $result->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="admin-item">
                <span><?= e($zh['naam']) ?> <small style="color:var(--text-muted);">(<?= e($zh['stad']) ?>)</small></span>
                <form method="post" class="admin-actions">
                    <input type="hidden" name="actie" value="verwijder_ziekenhuis">
                    <input type="hidden" name="id" value="<?= $zh['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Dit verwijdert ook alle getuigenissen, personeel en topics!')">×</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- PERSONEEL BEHEER -->
    <div class="admin-sectie">
        <h3>👨‍⚕️ Personeel</h3>
        <?php $ziekenhuizen = alleZiekenhuizen($db); ?>
        <form method="post" style="margin-bottom: 1rem;">
            <input type="hidden" name="actie" value="add_personeel">
            <input type="text" name="naam" placeholder="Naam *" required style="width:100%;padding:0.5rem;margin-bottom:0.3rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;">
            <select name="functie" style="width:100%;padding:0.5rem;margin-bottom:0.3rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;">
                <option value="dokter">Dokter</option>
                <option value="verpleging">Verpleging</option>
                <option value="psycholoog">Psycholoog</option>
                <option value="therapeut">Therapeut</option>
                <option value="sociaal_werker">Sociaal werker</option>
                <option value="andere">Andere</option>
            </select>
            <select name="ziekenhuis_id" required style="width:100%;padding:0.5rem;margin-bottom:0.3rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;">
                <option value="">-- Ziekenhuis --</option>
                <?php foreach ($ziekenhuizen as $zh): ?>
                    <option value="<?= $zh['id'] ?>"><?= e($zh['naam']) ?></option>
                <?php endforeach; ?>
            </select>
            <textarea name="notities" placeholder="Notities (optioneel)" style="width:100%;padding:0.5rem;margin-bottom:0.5rem;background:var(--bg);color:var(--text);border:1px solid #444;border-radius:4px;min-height:60px;"></textarea>
            <button type="submit" class="btn btn-small">Toevoegen</button>
        </form>

        <?php
        $result = $db->query('SELECT p.*, z.naam as zh_naam FROM personeel p JOIN ziekenhuizen z ON p.ziekenhuis_id = z.id ORDER BY p.naam');
        while ($p = $result->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="admin-item">
                <span><?= e($p['naam']) ?> <small style="color:var(--text-muted);">(<?= e($p['functie']) ?> - <?= e($p['zh_naam']) ?>)</small></span>
                <form method="post" class="admin-actions">
                    <input type="hidden" name="actie" value="verwijder_personeel">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Weet je het zeker?')">×</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- GETUIGENISSEN MODERATIE -->
    <div class="admin-sectie">
        <h3>📝 Getuigenissen (wachtend)</h3>
        <?php
        $result = $db->query('SELECT g.*, z.naam as zh_naam FROM getuigenissen g JOIN ziekenhuizen z ON g.ziekenhuis_id = z.id WHERE g.goedgekeurd = 0 ORDER BY g.datum DESC');
        $heeftWachtend = false;
        while ($g = $result->fetchArray(SQLITE3_ASSOC)):
            $heeftWachtend = true;
        ?>
            <div style="background: var(--bg); padding: 0.8rem; border-radius: 8px; margin-bottom: 0.8rem; border-left: 3px solid var(--accent2);">
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.3rem;">
                    <?= e($g['auteur']) ?> | 🏥 <?= e($g['zh_naam']) ?> | <?= date('d/m/Y', strtotime($g['datum'])) ?>
                </div>
                <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                    <?= e(mb_substr($g['tekst'], 0, 200)) ?><?= mb_strlen($g['tekst']) > 200 ? '...' : '' ?>
                </div>
                <div class="admin-actions">
                    <form method="post">
                        <input type="hidden" name="actie" value="goedkeuren">
                        <input type="hidden" name="id" value="<?= $g['id'] ?>">
                        <button type="submit" class="btn btn-small" style="background:var(--success);border-color:var(--success);">✅</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="actie" value="afwijzen">
                        <input type="hidden" name="id" value="<?= $g['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Verwijderen?')">❌</button>
                    </form>
                </div>
            </div>
        <?php endwhile;
        if (!$heeftWachtend): ?>
            <p style="color: var(--text-muted); font-style: italic; font-size: 0.9rem;">Geen wachtende getuigenissen. 🎉</p>
        <?php endif; ?>
    </div>

    <!-- PODCASTS BEHEER -->
    <div class="admin-sectie">
        <h3>📻 Podcasts</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
            Nieuwe podcasts toevoegen? Ga naar de <a href="podcasts.php" style="color: var(--accent);">podcast pagina</a>.
        </p>
        <?php
        $result = $db->query('SELECT * FROM podcasts ORDER BY datum DESC');
        while ($pod = $result->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="admin-item">
                <span><?= e($pod['titel']) ?></span>
                <form method="post" class="admin-actions">
                    <input type="hidden" name="actie" value="verwijder_podcast">
                    <input type="hidden" name="id" value="<?= $pod['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Weet je het zeker?')">×</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- FORUM BEHEER -->
    <div class="admin-sectie">
        <h3>💬 Forum Topics</h3>
        <?php
        $result = $db->query('SELECT ft.*, z.naam as zh_naam FROM forum_topics ft JOIN ziekenhuizen z ON ft.ziekenhuis_id = z.id ORDER BY ft.datum DESC LIMIT 20');
        while ($t = $result->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="admin-item">
                <span>
                    <a href="topic.php?id=<?= $t['id'] ?>" style="color: var(--text); text-decoration: none;">
                        <?= e(mb_substr($t['titel'], 0, 40)) ?>
                    </a>
                    <small style="color:var(--text-muted);">(<?= e($t['zh_naam']) ?>)</small>
                </span>
                <form method="post" class="admin-actions">
                    <input type="hidden" name="actie" value="verwijder_topic">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Topic en alle antwoorden verwijderen?')">×</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<?php endif; ?>

<?php siteFooter(); ?>
