<?php
/**
 * Site configuratie
 * Pas deze waarden aan voor je eigen hosting.
 */

// Admin wachtwoord - VERANDER DIT!
define('ADMIN_PASSWORD', 'verander_dit_wachtwoord_123');

// Database pad
define('DB_PATH', __DIR__ . '/data/site.db');

// Site naam
define('SITE_NAAM', 'Schuldig En Nooit Kans Om Tegendeel Te Bewijzen');
define('SITE_SUBTITEL', 'Over de wantoestanden in de Belgische mentale gezondheidszorg');

// Sessie starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
