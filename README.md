# Numis — Laravel coin catalog

This is a rebuild of the "Numis" WordPress plugin (Bulgarian coins catalog,
`ivandewolf.com/bulgarian-coins-catalog`) as a standalone Laravel + Blade +
MySQL application. No WordPress required.

## What was ported

| WordPress plugin                         | Laravel equivalent                                    |
|-------------------------------------------|--------------------------------------------------------|
| `coin` custom post type + post meta       | `coins` table (`App\Models\Coin`) — full numismatic field set (see below) |
| `coin_series` taxonomy                    | `series` table — a flat category with a name and its coins (`App\Models\Series`) |
| `[coin_catalog]` shortcode + AJAX filter  | `/` route — `CatalogController`, fetch-based filtering |
| Numis admin dashboard + meta boxes        | `/admin` — auth-protected CRUD for coins & series      |
| WP media uploader for front/back images   | plain file inputs → Laravel `Storage` (public disk)     |

A **series** is simply a category: a name and the coins that belong to it —
each coin belongs to at most one series (no hierarchy, no multi-select).

Each coin can have **one or more artists** credited to it (`App\Models\Artist`,
many-to-many via `artist_coin`) — managed from `/admin/artists` and filterable
on the public catalog.

A **coin** has the following fields:

| Field (BG)              | Field (Laravel column) | Translated? |
|--------------------------|------------------------|-------------|
| Заглавие                 | `title`                | ✅ bg/en/de |
| Серия                    | `series_id` (belongs to one `Series`) | — |
| Художник(ци)             | `artists` (many-to-many `Artist`) | — (proper names) |
| Година                   | `year`                 | — |
| Дата на въвеждане        | `issue_date`           | — |
| Номинална стойност       | `denomination`         | — |
| Метал, проба             | `metal`                | — |
| Качество                 | `quality`               | — |
| Тегло                    | `weight`               | — |
| Диаметър                 | `diameter`             | — |
| Гурт                     | `edge`                 | ✅ bg/en/de |
| Тираж                    | `mintage`              | — |
| Отсечена в               | `mint`                 | ✅ bg/en/de |
| —                        | `front_image`          | — |
| —                        | `front_description`    | ✅ bg/en/de |
| —                        | `back_image`           | — |
| —                        | `back_description`     | ✅ bg/en/de |
| —                        | `description`          | ✅ bg/en/de |

The public catalog can be filtered by series, year (or a year range/period),
metal, denomination, and artist.

Everything is plain Blade + Bootstrap 5 (via CDN) — **no npm/Vite build step
is required**, which keeps deploys simple.

## 1. Create the base Laravel project

This folder is an *overlay*: it contains only the application-specific files
(models, controllers, views, migrations, routes, public assets). You need a
fresh Laravel install underneath it.

```bash
composer create-project laravel/laravel numis "^11.0"
cd numis
```

## 2. Copy the overlay files in

Copy everything from this folder into the new `numis/` project, merging
folders (don't overwrite `numis/vendor`, `numis/.env`, or the default
`routes/web.php` if you already customized it — here we just replace it):

```bash
cp -r app/* /path/to/numis/app/
cp -r database/migrations/* /path/to/numis/database/migrations/
cp -r database/seeders/* /path/to/numis/database/seeders/
cp -r database/data /path/to/numis/database/
cp -r resources/views/* /path/to/numis/resources/views/
cp -r lang /path/to/numis/
cp routes/web.php /path/to/numis/routes/web.php
cp -r public/css /path/to/numis/public/
cp -r public/js /path/to/numis/public/
```

### Register the locale middleware

Laravel 11's default skeleton configures middleware in `bootstrap/app.php`
instead of a `Kernel.php`. Open it and add the alias:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'setlocale' => \App\Http\Middleware\SetLocale::class,
    ]);
})
```

(The route file references `\App\Http\Middleware\SetLocale::class` directly
rather than the alias, so this registration step is only needed if you ever
want to refer to it as `'setlocale'` elsewhere — otherwise routing works
without it since `routes/web.php` already uses the class name.)

## 3. Configure the database

Create a MySQL database and user, then edit `.env` in the Laravel project
using `.env.additions.example` from this folder as a guide (DB_*, APP_URL,
ADMIN_EMAIL, ADMIN_PASSWORD).

## 4. Install, migrate, seed

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder
```

For automatic coin translations when creating a coin, add a DeepL Free API
key to the application's `.env` file:

```env
DEEPL_API_KEY=your-deepl-api-key
```

The create form accepts Bulgarian text only and translates the translatable
coin fields into English and German through DeepL. The edit form shows all
three language fields for manual corrections. Keep the API key out of source
control.

### Admin CSV import/export

The admin list pages provide CSV export and import for coins, series, and
artists. Export a resource first to get the exact header format for imports.
Imports update existing records by `id` for coins and by `slug` for series and
artists. Coin relationships use `series_slug` and pipe-separated
`artist_slugs` (for example `artist-one|artist-two`). Coin image columns store
the existing storage paths; CSV import does not upload image binaries.

Imports run inside a database transaction. Invalid rows are skipped and
reported after the import; keep a backup before replacing a large dataset.

`php artisan storage:link` is required — uploaded coin images are stored in
`storage/app/public/coins` and served through the `public/storage` symlink.

## 5. Import your existing coin data

Your WordPress export (`ivandew1_website.sql`) has already been parsed and
converted into `database/data/numis_import_data.json` — it contains **85
coins** and **5 series** (Bulgarian artists, Bulgarian renaissance, Medieval
Bulgarian rulers, Bulgarian iconography, 1300 years of Bulgaria; all flat,
no sub-series in the current data).

Run the import after `php artisan migrate` and `php artisan storage:link`:

```bash
php artisan numis:import
```

This command (`app/Console/Commands/ImportNumisData.php`):

- creates the 5 `Series` rows
- creates all 85 `Coin` rows (title, year, denomination, metal, diameter,
  description), each assigned to its one series
- **downloads** each coin's front/back image directly from
  `https://ivandewolf.com/wp-content/uploads/...` and stores it in
  `storage/app/public/coins`, so the new app no longer depends on the old
  WordPress host — this requires the machine running the import to have
  internet access to `ivandewolf.com`

The old plugin never had `issue_date`, `quality`, `weight`, `mintage`,
`edge`, `mint`, `front_description`, or `back_description` — those start
blank on every imported coin and you fill them in per coin from
`/admin/coins` as you get to them. Artists are a new concept too — none of
the 85 imported coins have an artist credited yet; add them from
`/admin/artists` and assign them per coin.

One coin in the export ("St. Protomartyr Stephen") has no metadata or
images at all — it was an empty/incomplete draft in WordPress. It will
still be imported (with blank fields) so you don't lose it silently; delete
it from `/admin/coins` if you don't want to keep it.

The command is safe to re-run: series are matched by slug
(`updateOrCreate`), though re-running will create duplicate coins since
those are always inserted fresh — only run `numis:import` once per fresh
database, or truncate the `coins` table first if you need a clean re-import.

## 6. Run it

```bash
php artisan serve   # local check
```

For production, point your web server (Nginx/Apache) at `public/` as usual
for Laravel, run behind PHP-FPM, and set `APP_ENV=production`,
`APP_DEBUG=false` in `.env`.

## Logging into the admin

Go to `/login` and use the `ADMIN_EMAIL` / `ADMIN_PASSWORD` you set in
`.env` before seeding. From there:

- **Dashboard** (`/admin`) — coin/series/artist counts, quick-add links
- **Coins** (`/admin/coins`) — list, create, edit, delete; upload front/back
  images; fill in all numismatic fields (year, denomination, metal, quality,
  weight, diameter, mintage, edge, mint); assign a series and one or more
  artists
- **Series** (`/admin/series`) — list, create, edit, delete; a series is
  just a name — no hierarchy
- **Artists** (`/admin/artists`) — list, create, edit, delete; just a name.
  Deleting an artist doesn't delete their coins, it just removes that credit.

The public catalog lives at `/` with filters for year (or a year range),
metal, denomination, diameter, series, and artist, plus the same flip-card
hover effect on coin images.

## Multilingual support (Bulgarian / English / German)

The public catalog is fully trilingual; the admin panel stays in one
language (it's only for you).

**URLs:** `/bg`, `/en`, `/de` — `/` redirects to `/bg` (set by `APP_LOCALE`
in `.env`). A language switcher in the top nav swaps the locale segment on
the current page, preserving the URL path and query string (so filters
stay applied when switching language).

**UI text** (labels, buttons, "No coins found", etc.) lives in
`lang/bg/catalog.php`, `lang/en/catalog.php`, `lang/de/catalog.php` — plain
PHP arrays, edit directly and redeploy to change wording.

**Content** (coin titles, coin descriptions, series names) is stored per
coin/series as JSON, e.g. `{"bg":"...","en":"...","de":"..."}`. In
`/admin/coins` and `/admin/series` each of those fields now shows three
inputs (Bulgarian / English / German) instead of one. You don't have to
fill in all three — if a language is missing, the public site falls back
to `APP_FALLBACK_LOCALE` (English), then to whichever language *is* filled,
so nothing ever shows blank.

**Not translated on purpose:** denomination, metal, diameter, and artist
names stay as plain single values — numismatic notation ("Cu 999/1000",
"34.2 mm") is normally written the same way regardless of language, and a
person's name doesn't change between languages either (beyond
transliteration, which you can just type once into the name field the way
you want it shown everywhere). Say the word if you'd rather have any of
these translatable too.

**After `numis:import`:** all 85 imported coins and 5 series will have only
their English text filled in (that's what the WordPress data contained) —
Bulgarian and German are blank until you fill them in from `/admin/coins`
and `/admin/series`. Visitors on `/bg` and `/de` will see the English text
in the meantime rather than a blank page.

## Notes / things you may want to adjust

- Diameter, weight, and mintage are stored as free text rather than
  numbers, since the filter dropdown works off distinct stored values and
  the old data used free-text notation.
- A coin belongs to at most one series. Deleting a series doesn't delete
  its coins — they just become unassigned.
- There's no image cropping/resizing — uploads are stored as-is. Let me know
  if you want thumbnails generated on upload.
