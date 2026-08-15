# Nyvora

A technology news magazine built with Laravel 13, Blade, MySQL and Tailwind CSS v4 (via Vite).

Everything on the site is driven by the database: the navbar sections, the hero, the feed,
the category tints and the "Most Popular" widget.

## Requirements

- PHP 8.3+
- MySQL 8+
- Node 20+
- Composer

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Create the database, then point .env at it (DB_DATABASE=nyvora by default)
mysql -u root -p -e "CREATE DATABASE nyvora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed   # 5 categories, 6 authors, 20 published articles
php artisan storage:link     # serves uploaded images from public/storage
npm run build                # or: npm run dev
php artisan serve            # http://127.0.0.1:8000
```

The seeder is idempotent — re-run `php artisan migrate:fresh --seed` any time to reset the demo content.

### Signing in

The seeder creates a demo administrator:

| Email | Password |
| --- | --- |
| `admin@nyvora.test` | `password` |

**Change it before the site is reachable from anywhere but your laptop.** Real accounts are created
from the command line — there is no public registration:

```bash
php artisan nyvora:make-admin              # prompts for name, email, password
php artisan nyvora:make-admin you@site.com # promotes an existing user instead
```

Sign in at `/login`; the dashboard is at `/admin`.

## Routes

| Route | Controller | Notes |
| --- | --- | --- |
| `/` | `HomeController@index` | Hero, paginated feed, sidebar |
| `/category/{slug}` | `CategoryController@show` | Section archive, tinted with the category colour |
| `/article/{slug}` | `ArticleController@show` | Increments `views_count`, shows related stories |
| `/search?q=` | `SearchController@index` | Keyword search over headline and excerpt |
| `/subscribe` | `SubscriberController@store` | Newsletter form (POST) |
| `/about`, `/contact`, `/privacy-policy` | `PageController` | Static pages required for AdSense review |
| `/login`, `/logout` | `Auth\LoginController` | Newsroom sign-in (rate limited to 5 tries/minute) |
| `/admin/…` | `Admin\*` | Dashboard — requires `auth` + `admin` middleware |

## Admin

Everything under `/admin` needs a signed-in user with `is_admin = true`; a signed-in reader without
the flag gets a 403 rather than a redirect.

| Screen | What it controls |
| --- | --- |
| Dashboard | Published/draft/scheduled counts, total views, subscriber growth, most-read stories |
| Stories | Full CRUD, search and filters, image upload, one-click publish and feature toggles |
| Sections | Name, slug and the colour used for that section's labels site-wide |
| Authors | Name, bio and avatar |
| Subscribers | Search, remove, and CSV export of the newsletter list |
| Settings | Tagline, footer blurb, stories per page, announcement strip, AdSense publisher ID |

Sections and authors cannot be deleted while stories still reference them — the admin gets a clear
message instead of a database error.

### Publish times

The publish date field is read in the **application** timezone (`APP_TIMEZONE` in `.env`, `UTC` by
default), not the editor's browser timezone. The field shows the current site time underneath so
there is no ambiguity; set `APP_TIMEZONE` to your newsroom's zone if UTC is not what you want.

### Article body format

Bodies are stored as plain text and escaped on output, so nothing in the database can inject markup:

- a blank line separates paragraphs
- a line starting with `## ` becomes a subheading
- a line starting with `> ` becomes a pull quote

## Social sharing

Every article carries a share row (under the byline and again after the body) with X, Facebook,
LinkedIn, WhatsApp, Telegram, email, copy-link and the native mobile share sheet. These are plain
links — no third-party scripts or trackers. Shared links pick up full Open Graph and
`NewsArticle` structured data, including the byline, section and publish time.

## Data model

- **Category** — `name`, `slug`, `color` (hex, drives every category label on the site)
- **Author** — `name`, `bio`, `avatar_url`
- **Article** — belongs to a Category and an Author; `views_count`, `is_featured`, `published_at`
- **Subscriber** — newsletter signups

An article is public only when `published_at` is set and in the past. The `Article::published()`
scope enforces that everywhere, and the seeder creates one draft and one scheduled post so you can
see it working.

## Blade structure

```
resources/views/
├── layouts/app.blade.php        page shell: meta, fonts, header/footer
├── home | category | article | search .blade.php
├── pages/                       about, contact, privacy-policy
└── components/
    ├── header, footer, hero, promo-banner, sidebar, page-header
    ├── article-feed → article-row      (the "Latest News" list)
    ├── article-card                    (related-stories grid)
    ├── article-body                    (renders the stored body safely)
    ├── category-label, thumbnail, byline, section-heading
    └── ad-slot                         (AdSense placeholders)
```

## Re-branding

All brand colours and the font live in `resources/css/app.css` under `@theme`. Change the hex
values there and the whole site follows:

```css
--color-brand: #5b2bef;   /* hero block, buttons, links */
--color-ink:   #0b0b12;   /* navbar and footer */
--color-accent:#ffc940;   /* promo strip */
```

The site name comes from `APP_NAME` in `.env`. The headline font (Inter) is configured in
`vite.config.js` and self-hosted at build time.

## Adding AdSense

Three placeholders are already sized and positioned:

| Slot | Size | Where |
| --- | --- | --- |
| `ad-slot-1` | 300×250 | Sidebar |
| `ad-slot-2` | 728×90 | Above the homepage feed |
| `ad-slot-3` | 320×100 | Inside the feed |

1. Save your publisher ID in **Admin → Settings**. The loader script is then added to every public
   page automatically; while the field is empty, no script is loaded at all.
2. Paste each unit's `<ins class="adsbygoogle">` tag into `resources/views/components/ad-slot.blade.php`
   where the `ADSENSE SLOT` comment is, and remove the placeholder `<span>`.

Before applying, replace the placeholder contact addresses in `pages/contact.blade.php` and fill in
the bracketed sections of `pages/privacy-policy.blade.php`.

## Tests

```bash
php artisan test      # 51 feature tests
./vendor/bin/pint     # code style
```
