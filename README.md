# Numis — Laravel coin catalog

This is a rebuild of the "Numis" WordPress plugin (Bulgarian coins catalog,
`ivandewolf.com/bulgarian-coins-catalog`) as a standalone Laravel + Blade +
MySQL application. No WordPress required.

## What was ported

| WordPress plugin                         | Laravel equivalent                                    |
|-------------------------------------------|--------------------------------------------------------|
| `coin` custom post type + post meta       | `coins` table (`App\Models\Coin`)                      |
| `coin_series` hierarchical taxonomy       | `series` table, self-referencing parent (`App\Models\Series`) |
| `[coin_catalog]` shortcode + AJAX filter  | `/` route — `CatalogController`, fetch-based filtering |
| Numis admin dashboard + meta boxes        | `/admin` — auth-protected CRUD for coins & series      |
| WP media uploader for front/back images   | plain file inputs → Laravel `Storage` (public disk)     |

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
cp -r resources/views/* /path/to/numis/resources/views/
cp routes/web.php /path/to/numis/routes/web.php
cp -r public/css /path/to/numis/public/
cp -r public/js /path/to/numis/public/
```

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
  description)
- **downloads** each coin's front/back image directly from
  `https://ivandewolf.com/wp-content/uploads/...` and stores it in
  `storage/app/public/coins`, so the new app no longer depends on the old
  WordPress host — this requires the machine running the import to have
  internet access to `ivandewolf.com`
- attaches each coin to its series

One coin in the export ("St. Protomartyr Stephen") has no metadata or
images at all — it was an empty/incomplete draft in WordPress. It will
still be imported (with blank fields) so you don't lose it silently; delete
it from `/admin/coins` if you don't want to keep it.

The command is safe to re-run: series are matched by slug
(`updateOrCreate`), though re-running will create duplicate coins since
those are always inserted fresh — only run `numis:import` once per fresh
database, or truncate the `coins`/`coin_series` tables first if you need a
clean re-import.

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

- **Dashboard** (`/admin`) — coin/series counts, quick-add links
- **Coins** (`/admin/coins`) — list, create, edit, delete; upload front/back
  images; assign one or more series
- **Series** (`/admin/series`) — list, create, edit, delete; supports a
  parent series for hierarchy (mirrors the old taxonomy)

The public catalog lives at `/` with the same filters as before (year range,
metal, diameter, series) and the same flip-card hover effect on coin images.

## Notes / things you may want to adjust

- Diameter is stored as free text (like the old plugin) rather than a
  decimal, since the filter dropdown works off distinct stored values.
- A coin can belong to more than one series (many-to-many), which is a
  slightly more flexible version of the old single-select taxonomy term.
- There's no image cropping/resizing — uploads are stored as-is. Let me know
  if you want thumbnails generated on upload.
