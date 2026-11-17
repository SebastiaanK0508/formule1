# F1SITE.NL

Onafhankelijke Formule 1-site met live standen, uitslagen sinds 1950, coureur- en teamprofielen, de racekalender en het laatste nieuws. Gebouwd in PHP 8.2+ met MySQL/MariaDB, zonder framework en zonder build-stap.

## Functies

| Pagina | Wat het doet |
| --- | --- |
| `index.php` | Volgende race met countdown (in de tijdzone van de bezoeker), weersverwachting, laatste uitslag, kampioenschapsstand, nieuws en gevolgde coureurs/teams |
| `kalender.php` | Seizoenskalender met status/winnaar per race, plus een `.ics`-export (`calendar.ics.php`) voor Google/Apple/Outlook |
| `circuit-details.php` | Raceweekend: sessietijden, circuitfeiten, kaart, eerdere winnaars en weersverwachting |
| `results.php` | Seizoensoverzicht (1950–nu): winnaar, pole en snelste ronde per race |
| `race_result.php` | Volledige uitslag met tabs: race, kwalificatie (met gaps), sprint, pitstops, posities gewonnen/verloren |
| `standings.php` | Coureurs- en constructeursstand voor elk seizoen, met puntenverloop-grafiek |
| `drivers.php` / `driver-details.php` | Grid met teamfilter; profiel met seizoenresultaten, grid-vs-finish-grafiek, teamgenoot-duel en carrièrecijfers |
| `teams.php` / `team-details.php` | Teams met coureurs en stand, archief van 185 historische constructeurs; teamprofiel met resultaten per race |
| `compare.php` | Twee coureurs naast elkaar: seizoen, onderlinge duels en carrière |
| `stats.php` | Seizoen in cijfers (1996–nu): records, alle teamgenotenduels, puntenverdeling en betrouwbaarheid per team |
| `quiz.php` | Dagelijkse quiz: 5 vragen over historische races (voor iedereen dezelfde per dag), met score, streak en deelknop |
| Titelstrijd | Op home en standen: wie kan nog kampioen worden en wat de leider nodig heeft om het in de volgende race te beslissen |
| Rondegrafiek | Tab op de uitslagpagina met de positie van elke coureur per ronde (vanaf 1996) |
| Snel zoeken | `⌘K` / `Ctrl+K` of `/` opent overal een zoekvenster; `#search` in een URL opent het direct |
| LIVE-balk | Verschijnt bovenaan elke pagina zodra een sessie bezig is (of binnen 15 minuten begint) |
| `nieuws.php`, `zoek.php`, `contact.php` | Nieuws (via NewsAPI), zoeken in coureurs/teams/races/nieuws, contactformulier met CSRF- en spambescherming |
| `achterkant/` | Beheerpaneel: kalender, coureurs, teams, nieuws, berichten-inbox en onderhoudstaken |

Licht en donker thema, volledig responsive, `sitemap.xml`, `robots.txt`, JSON-LD en Open Graph-metadata.

## Structuur

```
├── *.php                 Publieke pagina's (URL's zijn ongewijzigd t.o.v. de oude site)
├── api/                  JSON-endpoints voor de frontend (weer, gevolgde coureurs)
├── assets/               CSS (design system), JavaScript, afbeeldingen
├── includes/
│   ├── bootstrap.php     Laadt config, helpers, database en datalaag
│   ├── helpers.php       Escaping, URL's, datums, CSRF, iconen
│   ├── db.php            PDO-verbinding + query-helpers
│   ├── http.php          Gecachte HTTP-client (stale-on-error, rate-limit back-off)
│   ├── f1data.php        Jolpica/Ergast-API + koppeling met eigen database
│   ├── tasks.php         Onderhoudstaken (nieuws, sessietijden, cache)
│   ├── layout/           Header en footer
│   └── partials/         Herbruikbare blokken
├── achterkant/           Beheerpaneel (kan als eigen subdomein draaien)
├── scripts/              Cronjobs (alleen via de command line)
├── data/                 Statische data (historisch constructeursarchief)
├── database/             Databaseschema en migraties
└── storage/cache/        API-cache (niet in git)
```

`includes/`, `scripts/`, `data/`, `database/`, `storage/` en de configbestanden zijn via `.htaccess` afgeschermd voor bezoekers.

## Installatie

1. Kopieer `config.example.php` naar `config.local.php` en vul de databasegegevens, `base_url`, `admin_url`, GA-id en NewsAPI-key in. Alles kan ook via omgevingsvariabelen (`F1_DB_HOST`, `F1_DB_PASS`, …). `config.local.php` staat in `.gitignore` en mag nooit gecommit worden.
2. Nieuwe database: importeer `database/schema.sql`. Bestaande database: voer eventueel `database/migrations/001_contact_created_at.sql` uit (voegt een ontvangstdatum toe aan contactberichten).
3. Maak een beheerder aan: `php scripts/create-admin.php <gebruikersnaam>`.
4. Zorg dat `storage/cache/` schrijfbaar is voor de webserver.

## Cronjobs

```
0 */2 * * *  php /pad/naar/site/scripts/fetch-news.php       # nieuws ophalen
30 4 * * *   php /pad/naar/site/scripts/prune-news.php 5     # nieuws ouder dan 5 dagen opruimen
0 5 * * *    php /pad/naar/site/scripts/sync-sessions.php    # sessietijden van OpenF1
15 * * * *   php /pad/naar/site/scripts/warm-cache.php       # API-cache voorverwarmen (optioneel)
```

Alle taken zijn ook handmatig uit te voeren via **Beheer → Onderhoud**.

## Data en tijden

- **Uitslagen, standen en historie** komen van de [Jolpica/Ergast API](https://github.com/jolpica/jolpica-f1) en worden in `storage/cache/` bewaard: oude seizoenen 30 dagen, het lopende seizoen 6 uur (15 minuten tijdens een raceweekend). Bij een storing of rate limit wordt de laatst bekende data getoond.
- **Kalender, sessietijden, teams en coureurs** staan in de eigen database en worden beheerd in `achterkant/`. API-teams en -coureurs worden automatisch gekoppeld op naam. Een coureur met punten zonder profiel verschijnt als aandachtspunt op het dashboard.
- **Alle sessietijden staan in UTC in de database.** De site toont ze in de tijdzone van de bezoeker; in het beheerpaneel voer je ze in Nederlandse tijd in, die bij het opslaan automatisch naar UTC wordt omgezet.
