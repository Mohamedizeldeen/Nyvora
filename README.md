# Nyvora

A technology news magazine built with Laravel 13, Blade, MySQL and Tailwind CSS v4 (via Vite).

Everything on the site is driven by the database: the navbar sections, the hero, the feed,
the category tints and the "Most Popular" widget.

## Requirements

- PHP 8.3+
- MySQL 8+
- Node 20+
- Composer

### A note on the PHP version

Composer resolves dependencies against a **pinned platform version**, declared in `composer.json`:

```json
"config": { "platform": { "php": "8.3.33" } }
```

This is deliberate. Left unpinned, Composer resolves against whatever PHP the *developer* happens to
run — and on PHP 8.5 it picks Symfony 8, Pest 5 and PHPUnit 13, all of which require **PHP 8.4.1+**.
`composer install` then fails on an 8.3 server with `your php version (8.3.x) does not satisfy that
requirement`.

With the pin, Composer resolves the Symfony **7.4 LTS** line and Pest 4 instead — versions that run
on 8.3 *and* on 8.4/8.5, so one lockfile works everywhere. Laravel 13 itself supports PHP 8.3 and
accepts `symfony/* ^7.4 || ^8.0`, so nothing is lost.

**If you upgrade every environment to PHP 8.4+**, raise the pin and re-resolve to move up to the
newer packages:

```bash
composer config platform.php 8.4.0
composer update
```

Always run `composer install` (not `update`) on servers, so they get exactly what is in the lockfile.

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
npm run build                # or: npm run dev (see note on public/hot below)
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

## Newsletter (Mailgun)

The list is **double opt-in**: signing up creates a pending row and Mailgun sends a confirmation
link. Nothing is mailed and nothing reaches your mailing list until the reader clicks it.

```
sidebar form → pending subscriber → confirmation email (Mailgun)
             → reader clicks link → confirmed → (optional) added to Mailgun list
```

### Configuration

Configured and verified against the live account:

```dotenv
MAIL_MAILER=mailgun
MAIL_FROM_ADDRESS="newsletter@ny-vora.com"   # must be on the verified domain

MAILGUN_API_KEY=...                # sending key (set, verified)
MAILGUN_DOMAIN=ny-vora.com         # active custom domain, US region
MAILGUN_ENDPOINT=api.mailgun.net   # EU accounts would use api.eu.mailgun.net
MAILGUN_LIST_ADDRESS=              # optional, e.g. news@mg.ny-vora.com
```

`MAILGUN_LIST_ADDRESS` is optional. Leave it blank and subscribers are stored locally only (export
them as CSV from the admin). Set it and confirmed addresses are pushed to that Mailgun mailing list
automatically, and unsubscribes are removed from it — so newsletters can be sent from the Mailgun
dashboard. Sync failures are logged, never shown to the reader.

> **Note on key scope.** The configured key is a *sending* key. It authenticates for sending, and
> for reading domains, events and lists — but it returns `401` on the suppressions API. If you
> enable `MAILGUN_LIST_ADDRESS` and the sync logs 401s, swap in a private API key with Lists write
> permission. Nothing breaks either way: the site keeps its own subscriber table regardless.

Check the setup without waiting for a real signup:

```bash
php artisan nyvora:mail-test you@yourdomain.com
```

### A queue worker is required

Confirmation emails are **queued** so a slow Mailgun never delays a visitor's request. In production
something must run:

```bash
php artisan queue:work
```

Without a worker the rows are created but no email is ever sent.

### Unsubscribing

Every confirmation email carries `List-Unsubscribe` and `List-Unsubscribe-Post` headers, so Gmail
and Outlook show a native unsubscribe button — a requirement for bulk senders. The link works by
`GET` (a click) and by `POST` (RFC 8058 one-click), and resolves on an unguessable token, so nobody
can unsubscribe someone else by guessing an id.

## SEO

| Feature | Where |
| --- | --- |
| `sitemap.xml` | Generated from the database; published stories, sections and static pages only |
| `robots.txt` | Generated; allows crawling by default, disallows `/admin`, `/login`, `/search`, `/newsletter/`, points at the sitemap |
| RSS feed | `/feed` — 30 latest stories, linked from `<head>` and the footer |
| Canonical URLs | Self-referencing, including `?page=N` |
| `rel="prev"` / `rel="next"` | On every paginated archive |
| Structured data | `NewsMediaOrganization` + `WebSite` (with SearchAction) site-wide, `NewsArticle` and `BreadcrumbList` on stories |
| Open Graph / Twitter | Title, description, image, locale, author, section, publish time |
| Semantic HTML | One `<h1>` per page; `article`, `nav`, `aside`, `main`, `time`, `figure` |
| Custom 404 | Branded, `noindex, follow`, with search and section links |
| `noindex` | Search results, newsletter confirm/unsubscribe pages, the whole admin |

**Set `APP_URL` to your real domain before going live** — the sitemap, feed, canonical tags and
email links are all built from it.

Lighthouse SEO scores **100/100** on the homepage, article, category, paginated and static pages.

### Controlling indexing

Indexing is **on by default**, and switched off only by an explicit decision — never inferred from
`APP_ENV`. A deploy that forgets `APP_ENV=production` silently vanishing from Google is a far more
expensive failure than a staging copy being crawled.

Two switches, and **both must agree** before anything is indexed:

| Switch | Where | Use it for |
| --- | --- | --- |
| `SITE_INDEXABLE` | `.env` (default `true`) | Per-deployment. Set `false` on staging and review apps. |
| "Allow search engines to index this site" | Admin → Settings | Editorial control on the live site. |

Either one can block; neither can force indexing on alone. When blocked, `robots.txt` returns
`Disallow: /` **and** every page carries `<meta name="robots" content="noindex, nofollow">` — a
`Disallow` alone stops crawling but does not stop a URL discovered elsewhere from being listed.
The Settings screen shows a warning whenever the site is hidden, and explains which switch did it.

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
php artisan test      # 62 feature tests
./vendor/bin/pint     # code style
```

## Troubleshooting

**The site loads with no styling at all.** `npm run dev` leaves a `public/hot` file behind when it
stops. While that file exists, Laravel serves assets from the Vite dev server on port 5173 instead
of the built files, so everything 404s once the dev server is gone. Delete it:

```bash
rm -f public/hot public/fonts-manifest.dev.json
npm run build
```

**`composer install` fails with "your php version does not satisfy that requirement".** See the note
on the PHP version above — the platform pin exists to prevent exactly this.

**Signups work but no email arrives.** Check `php artisan nyvora:mail-test you@yourdomain.com`, then
confirm a queue worker is running (`php artisan queue:work`) — confirmation emails are queued.
