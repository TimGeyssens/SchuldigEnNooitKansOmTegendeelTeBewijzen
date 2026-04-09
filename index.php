<?php
require_once __DIR__ . '/helpers.php';
$db = getDB();

// Tellingen voor de homepage
$aantalPodcasts = $db->querySingle('SELECT COUNT(*) FROM podcasts');
$aantalGetuigenissen = $db->querySingle('SELECT COUNT(*) FROM getuigenissen WHERE goedgekeurd = 1');
$aantalZiekenhuizen = $db->querySingle('SELECT COUNT(*) FROM ziekenhuizen');
$aantalTopics = $db->querySingle('SELECT COUNT(*) FROM forum_topics');

siteHeader();
?>

<div class="hero">
    <h1>Schuldig En Nooit Kans<br>Om Tegendeel Te Bewijzen</h1>
    <p class="subtitel"><?= SITE_SUBTITEL ?></p>

    <div class="hero-uitleg">
        <p>
            Welkom op de site die niemand wilde maar iedereen nodig heeft.
            Wij zijn patiënten die het beu zijn om in stilte te lijden
            terwijl het systeem zichzelf een schouderklopje geeft.
        </p>
        <br>
        <p>
            Hier verzamelen we getuigenissen, bespreken we de absurditeit
            in onze podcasts, en ja — we lachen ermee. Want als je niet lacht,
            huil je. En dat doen we al genoeg in de isoleercel.
        </p>
    </div>

    <div class="sectie-links">
        <a href="hosts.php" class="sectie-link shake-hover">
            <h3>⭐ Meet The Hosts</h3>
            <p>Tim & Maxim — twee patiënten, één missie, nul budget.</p>
        </a>

        <a href="podcasts.php" class="sectie-link shake-hover">
            <h3>📻 Podcasts</h3>
            <p>Luister naar onze absurde gesprekken over het systeem.
               <?php if ($aantalPodcasts > 0): ?><span class="teller"><?= $aantalPodcasts ?></span><?php endif; ?>
            </p>
        </a>

        <a href="getuigenissen.php" class="sectie-link shake-hover">
            <h3>📝 Getuigenissen</h3>
            <p>Echte verhalen van echte patiënten. Anoniem kan ook.
               <?php if ($aantalGetuigenissen > 0): ?><span class="teller"><?= $aantalGetuigenissen ?></span><?php endif; ?>
            </p>
        </a>

        <a href="ziekenhuizen.php" class="sectie-link shake-hover">
            <h3>🏥 Ziekenhuizen</h3>
            <p>Een overzicht van de "zorginstellingen" in België.
               <?php if ($aantalZiekenhuizen > 0): ?><span class="teller"><?= $aantalZiekenhuizen ?></span><?php endif; ?>
            </p>
        </a>

        <a href="personeel.php" class="sectie-link shake-hover">
            <h3>👨‍⚕️ Personeel</h3>
            <p>Dokters en verpleging — de gezichten achter het systeem.</p>
        </a>

        <a href="forum.php" class="sectie-link shake-hover">
            <h3>💬 Forum</h3>
            <p>Stel vragen over je patiëntenrechten. Anoniem welkom.
               <?php if ($aantalTopics > 0): ?><span class="teller"><?= $aantalTopics ?></span><?php endif; ?>
            </p>
        </a>

        <a href="patientenrechten.php" class="sectie-link shake-hover">
            <h3>⚖️ Patiëntenrechten</h3>
            <p>De rechten die je hebt — ook al doet niemand alsof.</p>
        </a>
    </div>
</div>

<?php siteFooter(); ?>
