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
| `/authors`, `/author/{slug}` | `AuthorController` | Byline index and author profiles |
| `/about`, `/team`, `/contact`, `/editorial-policy`, `/advertise`, `/privacy-policy`, `/cookie-policy`, `/terms` | `PageController` | See the Pages table below |
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
| Authors | Name, slug, bio and avatar — drives the public author profiles |
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
php artisan test      # 148 feature tests
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

## Pages

| Page | Route | Notes |
| --- | --- | --- |
| About us | `/about` | Who we are, what we cover, how we are funded |
| Our team | `/team` | Newsroom structure; the people list is pulled from the database |
| Authors | `/authors` | Byline index — only authors with published work |
| Author profile | `/author/{slug}` | Bio, every story they wrote, `ProfilePage` structured data |
| Editorial policy | `/editorial-policy` | Sourcing, corrections, independence, AI use |
| Advertise with us | `/advertise` | Formats matching the real ad slots, and what is not for sale |
| Contact us | `/contact` | Tips, corrections, advertising, press |
| Privacy policy | `/privacy-policy` | Written against what the code actually does |
| Cookie policy | `/cookie-policy` | Cookie table generated from `config/session.php` |
| Terms of use | `/terms` | Copyright, acceptable use, liability |

Every one of these is linked from the footer on every page, and listed in `sitemap.xml`.

### Contact addresses must exist

Every page points readers at addresses on the sending domain:

`tips@` `corrections@` `ads@` `hello@` `privacy@` `security@` `editor@` `pitches@` — all `@ny-vora.com`.

Create these mailboxes (or forwards) before launch. AdSense review checks that contact details work,
and the editorial policy promises a reply to every correction request.

### Governing law is deliberately not stated

The terms carry no choice-of-law clause, because naming a jurisdiction requires knowing where the
company is established. Without one, ordinary conflict-of-laws rules apply — which for consumers is
usually their own country anyway. Section 12 says local consumer protections still apply. If you
want to nominate a governing law and forum, there is a Blade comment marking the spot in
`pages/terms.blade.php`. The same applies to a registered postal address in the privacy policy.

### Retention promises are enforced in code

The privacy policy states two retention periods, and both are implemented rather than aspirational:

- **Server logs, 30 days** — `LOG_STACK=daily` with `LOG_DAILY_DAYS=30`.
- **Unconfirmed signups, 30 days** — `Subscriber::prunable()`, run by the scheduled `model:prune`.
  Unsubscribed rows are deliberately kept, which the policy discloses.

The scheduler must be running for the second one:

```
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

## Contact forms (no email addresses)

The site publishes **no email address of its own**. Every place that used to carry a `mailto:` link
now opens a form in a popup, with the right topic already selected:

| Topic | Replaced |
| --- | --- |
| Story tip | `tips@` |
| Correction | `corrections@` |
| Advertising | `ads@` |
| Freelance pitch | `pitches@` |
| Editorial complaint | `editor@` |
| Security report | `security@` |
| Privacy request | `privacy@` |
| Something else | `hello@` |

Messages are **stored in `contact_messages` and read at `/admin/messages`** — nothing is emailed
anywhere, because the publication has no mailbox. The sidebar shows an unread count, and opening a
message marks it read.

### How the popup works

`<x-contact-modal>` is rendered once per page by the layout, and every `<x-contact-button>` opens it.
It is a native `<dialog>`, so the browser handles the backdrop, focus trap and Esc-to-close.

It degrades cleanly: the triggers are ordinary links to `/contact#contact-form`, where the same form
(`<x-contact-form>`) is rendered inline. With JavaScript the form posts via `fetch` and swaps in the
thank-you without a reload; without it, the form posts normally and the page comes back with a flash
message. Either way the reader gets a thank-you.

### Abuse protection

- **Honeypot** — a hidden `website` field that must stay empty. No captcha, no third party, no cookies.
- **Rate limit** — 5 submissions per minute per IP.
- **Validation** — topic must be one of the known keys; name, email and a message of at least 10 characters.

No IP address is stored with a message; the rate limiter only uses it transiently in the cache. The
privacy policy describes exactly this.

## Reader comments

Readers leave a **name and a comment** — no email address is asked for, and no IP address is stored.
Every comment is **held for moderation**: it is invisible to everyone until an administrator
approves it at `/admin/comments`, where the waiting queue is the default view and the sidebar shows
a pending badge.

```
reader posts → pending (invisible) → admin approves → visible on the article
```

Comments can be closed two ways, and **both must agree** for the form to appear:

| Switch | Where |
| --- | --- |
| Site-wide | Admin → Settings → "Let readers comment on articles" |
| Per story | The story's edit screen → "Allow comments on this story" |

Closing comments hides the form but keeps already-approved comments readable, and the POST route
returns 403 so a stale page cannot slip one through.

Comment text is escaped on output and rendered with `whitespace-pre-line`, so paragraphing survives
while markup cannot be injected. Deleting a story deletes its comments with it (`cascadeOnDelete`).

Abuse protection matches the contact forms: a hidden honeypot field, 5 posts per minute per IP, and
length validation. The privacy policy states plainly that a name and comment are **published for
anyone to read** once approved.

## Launch checklist

```bash
php artisan nyvora:preflight
```

Checks the things that actually stop a launch or an AdSense approval, and exits non-zero if any
blocker remains. Run it before going live and again before applying to AdSense.

It covers: `APP_ENV`, `APP_DEBUG`, `APP_URL`, the storage link, the app key, the seeded demo
administrator, whether a real admin exists, how many articles are published, whether any still
contain seeder filler, and whether indexing is on.

## Google AdSense

Everything is configured in **Admin → Settings → Google AdSense**. No file is edited to turn ads on.

1. **Publisher ID** (`ca-pub-…`) — from AdSense → Account → Settings. Saving it adds the loader
   script to every public page and starts serving `/ads.txt`.
2. **Ad units** — create a display unit in AdSense for each size, then paste the code it gives you
   into the matching box. The whole `<ins>` snippet is accepted; only the slot ID is stored.
3. **Auto ads** — optional checkbox, if you want Google placing extra units of its own.

| Placement | Size | Where |
| --- | --- | --- |
| Sidebar | 300×250 | Beside articles and archives |
| Leaderboard | 728×90 | Above the homepage feed |
| In-feed | 320×100 | Between stories |

Until a publisher ID is saved, **no Google code loads anywhere** and each placement shows a
correctly sized placeholder. `/ads.txt` returns 404 rather than an empty file, because an empty
ads.txt declares that nobody is authorised to sell your inventory.

Every placement reserves its height before anything loads, so an ad can never push the article out
from under a reader — the layout-shift behaviour Core Web Vitals measures.

### What AdSense review will look for

Already in place: privacy policy, cookie policy, terms, about, contact, editorial policy, an
identifiable newsroom with bylines, `ads.txt`, and no deceptive layout.

**Still on you: the content.** The 20 seeded articles are invented placeholders. Google requires
original, valuable content, and publishing fabricated stories as news would fail review — replace
them with real reporting before applying. `nyvora:preflight` flags this as a blocker.

## Google Analytics

Set the **Measurement ID** (`G-…`) in Admin → Settings. Nothing loads until it is set.

It runs behind **Google Consent Mode v2**: the tag is on the page but stores nothing — no cookies,
no identifiers — until the reader accepts in the banner. Verified in a real browser:

| Reader action | Cookies set |
| --- | --- |
| First visit, no choice yet | `nyvora-session`, `XSRF-TOKEN` only |
| Rejects | `nyvora-session`, `XSRF-TOKEN` only |
| Accepts | the above plus `_ga`, `_ga_<id>` |

The choice is kept in `localStorage`, not a cookie, so declining does not itself store one. The
banner only appears when analytics or advertising is actually configured.

**Note for EEA/UK traffic with AdSense:** Google requires publishers serving EEA/UK users to use a
Google-certified Consent Management Platform. The banner here is a correct Consent Mode
implementation, but it is not certified — use Google's own Privacy & Messaging (free, in the AdSense
dashboard) if you serve those regions.

## Reports

`/admin/reports` — views per day, most-read stories, and breakdowns by section and author, over 7,
30 or 90 days with a like-for-like comparison against the preceding period.

The figures come from **the site's own database**, not the Analytics API: no service account or
credentials to set up, nothing to break when Google is slow, and it counts every read rather than
only the visitors who accepted cookies. Google Analytics answers the other half — where readers came
from — and the page links straight to it.

`article_view_daily` holds one aggregate row per article per day, written by the same
`Article::recordView()` that increments the running total. No reader, session or IP is recorded.
