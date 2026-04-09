# Schuldig En Nooit Kans Om Tegendeel Te Bewijzen

Een website over de wantoestanden in de Belgische mentale gezondheidszorg.
Gemaakt door patiënten, voor patiënten. Met een absurde twist.

## Functies

- **Podcasts** — YouTube podcast embeds
- **Getuigenissen** — Anoniem verhalen insturen (moderatie door admin)
- **Ziekenhuizen** — Database van psychiatrische instellingen in België
- **Personeel** — Overzicht van dokters en verpleging per ziekenhuis
- **Forum** — Eenvoudig forum voor vragen over patiëntenrechten (anoniem, ziekenhuis verplicht)
- **Patiëntenrechten** — Volledige Belgische Wet Patiëntenrechten (2002)
- **Admin panel** — Beheer alle content via een beveiligd admin panel

## Vereisten

- PHP 8.0+ met SQLite3 extensie (standaard inbegrepen)
- Apache of Nginx webserver
- Geen MySQL of andere database nodig

## Installatie

1. Upload alle bestanden naar je webserver
2. Zorg dat de `data/` map schrijfbaar is:
   ```
   chmod 755 data/
   ```
3. Pas het admin wachtwoord aan in `config.php`:
   ```php
   define('ADMIN_PASSWORD', 'jouw_geheim_wachtwoord');
   ```
4. Ga naar `admin.php` en log in
5. Voeg ziekenhuizen toe (dit is nodig voor getuigenissen en forum)
6. Klaar!

## Goedkope hosting

Deze site draait op elke hosting die PHP ondersteunt. Enkele opties:

- **one.com** — Vanaf ~€2/maand
- **Combell** — Belgische hosting, vanaf ~€3/maand
- **Antagonist** — Vanaf ~€2/maand
- **000webhost** — Gratis (met beperkingen)

Geen MySQL nodig = goedkoopste plannen werken.

## Bestandsstructuur

```
├── index.php              # Homepage
├── config.php             # Configuratie (ADMIN WACHTWOORD HIER)
├── db.php                 # Database connectie + auto-migratie
├── helpers.php            # Layout, sanitize, auth functies
├── podcasts.php           # Podcast pagina (YouTube embeds)
├── getuigenissen.php      # Getuigenissen lezen + insturen
├── ziekenhuizen.php       # Ziekenhuizen overzicht
├── ziekenhuis.php         # Ziekenhuis detail (+ getuigenissen, personeel, topics)
├── personeel.php          # Dokters & verpleging overzicht
├── patientenrechten.php   # Wet Patiëntenrechten
├── forum.php              # Forum overzicht + nieuw topic
├── topic.php              # Forum topic detail + antwoorden
├── admin.php              # Admin panel (login, beheer)
├── style.css              # Styling (absurd/satirisch)
├── .htaccess              # Apache security
└── data/
    ├── .htaccess          # Blokkeer directe toegang
    └── site.db            # SQLite database (wordt automatisch aangemaakt)
```

## Veiligheid

- Alle user input wordt geëscaped met `htmlspecialchars()`
- SQL queries gebruiken prepared statements
- Database is beschermd met `.htaccess`
- Getuigenissen worden gemodereerd voor publicatie
- Admin sessie via PHP sessions

## Licentie

Vrij te gebruiken. Want patiëntenrechten zijn van iedereen.
