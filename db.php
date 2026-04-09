<?php
require_once __DIR__ . '/config.php';

function getDB(): SQLite3 {
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $db = new SQLite3(DB_PATH);
    $db->busyTimeout(5000);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    migrate($db);
    return $db;
}

function migrate(SQLite3 $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS ziekenhuizen (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        naam TEXT NOT NULL,
        stad TEXT NOT NULL,
        provincie TEXT DEFAULT "",
        type TEXT DEFAULT "psychiatrisch",
        notities TEXT DEFAULT "",
        aangemaakt DATETIME DEFAULT CURRENT_TIMESTAMP
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS podcasts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titel TEXT NOT NULL,
        youtube_url TEXT NOT NULL,
        beschrijving TEXT DEFAULT "",
        datum DATETIME DEFAULT CURRENT_TIMESTAMP
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS getuigenissen (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        auteur TEXT DEFAULT "Anoniem",
        ziekenhuis_id INTEGER NOT NULL,
        tekst TEXT NOT NULL,
        goedgekeurd INTEGER DEFAULT 0,
        datum DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ziekenhuis_id) REFERENCES ziekenhuizen(id)
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS personeel (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        naam TEXT NOT NULL,
        functie TEXT NOT NULL DEFAULT "dokter",
        ziekenhuis_id INTEGER NOT NULL,
        notities TEXT DEFAULT "",
        aangemaakt DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ziekenhuis_id) REFERENCES ziekenhuizen(id)
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS forum_topics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titel TEXT NOT NULL,
        auteur TEXT DEFAULT "Anoniem",
        ziekenhuis_id INTEGER NOT NULL,
        tekst TEXT NOT NULL,
        datum DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ziekenhuis_id) REFERENCES ziekenhuizen(id)
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS forum_replies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        topic_id INTEGER NOT NULL,
        auteur TEXT DEFAULT "Anoniem",
        tekst TEXT NOT NULL,
        datum DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (topic_id) REFERENCES forum_topics(id)
    )');
}
