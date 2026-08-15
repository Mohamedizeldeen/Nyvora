<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fills the site with demo content so every layout can be checked immediately:
 * 5 categories, 6 bylines and 20 published stories (plus a draft and a
 * scheduled post that should stay invisible on the public site).
 *
 * Every company, product and person below is invented for this demo.
 */
class NewsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The five sections in the navbar. `color` tints that section's labels.
     *
     * @var list<array{name: string, slug: string, color: string}>
     */
    private const CATEGORIES = [
        ['name' => 'AI', 'slug' => 'ai', 'color' => '#6D28D9'],
        ['name' => 'Startups', 'slug' => 'startups', 'color' => '#0F766E'],
        ['name' => 'Security', 'slug' => 'security', 'color' => '#DC2626'],
        ['name' => 'Gadgets', 'slug' => 'gadgets', 'color' => '#C2410C'],
        ['name' => 'Space', 'slug' => 'space', 'color' => '#1D4ED8'],
    ];

    /**
     * @var list<array{name: string, bio: string}>
     */
    private const AUTHORS = [
        ['name' => 'Maya Okonkwo', 'bio' => 'Maya covers machine learning research and the companies trying to turn it into products.'],
        ['name' => 'Daniel Reyes', 'bio' => 'Daniel reports on early-stage startups, venture funding and the people betting on both.'],
        ['name' => 'Priya Raman', 'bio' => 'Priya writes about security, privacy and the long tail of software supply chains.'],
        ['name' => 'Tomas Lindqvist', 'bio' => 'Tomas reviews consumer hardware and has opinions about hinges, ports and battery life.'],
        ['name' => 'Amara Bello', 'bio' => 'Amara follows spaceflight, astronomy and the science that rarely makes the front page.'],
        ['name' => 'Jonas Weber', 'bio' => 'Jonas writes long-form features about how technology reshapes ordinary work.'],
    ];

    /**
     * The demo newsroom.
     *
     * Fields: title, category slug, author index, excerpt, featured flag,
     * view count and how many hours ago it was published (which is what makes
     * Carbon's diffForHumans() show a realistic spread of timestamps).
     *
     * @var list<array{0: string, 1: string, 2: int, 3: string, 4: bool, 5: int, 6: int}>
     */
    private const ARTICLES = [
        ['Helion Labs says its newest model runs on a single laptop GPU', 'ai', 0, 'The startup claims a 40x reduction in memory footprint with no measurable quality loss, and says the weights will be public by the end of the quarter.', true, 48200, 3],
        ['Northwind Robotics raises $48M to retrofit warehouses it does not own', 'startups', 1, 'Rather than selling robots outright, the company installs them for a cut of the throughput it adds — a model its investors think travels well.', true, 31800, 6],
        ['Passkeys go mainstream as three more banks retire SMS codes', 'security', 2, 'The shift removes the single most abused fallback in consumer authentication, though support on older devices remains uneven.', true, 27400, 9],
        ['Hands on with the Aurora Fold, a laptop that keeps forgetting it is not a tablet', 'gadgets', 3, 'The hardware is genuinely impressive. The software has not decided what it wants the device to be, and you feel that in daily use.', true, 22950, 14],
        ['A private lander reached the lunar south pole. Here is what it sent back.', 'space', 4, 'Six days of surface operations produced more usable data than the mission planners had budgeted for, including a surprise in the regolith samples.', false, 19600, 20],
        ['The quiet rise of small models inside big enterprises', 'ai', 0, 'While attention stays on frontier systems, procurement teams are standardising on models small enough to run beside the data they read.', false, 17300, 26],
        ['A build tool used by thousands of teams shipped a backdoor for six weeks', 'security', 2, 'The malicious release was signed with a legitimate key. What failed was not cryptography but the review process around it.', false, 16100, 33],
        ['Solo founders are skipping the seed round, and investors are adjusting', 'startups', 1, 'Cheaper infrastructure and smaller teams mean some companies reach revenue before they ever pitch, which changes who has leverage.', false, 14750, 41],
        ['E-ink is having a moment, and it is not about e-readers', 'gadgets', 3, 'Colour refresh rates finally crossed the threshold where the technology makes sense for signage, laptops and, oddly, kitchen appliances.', false, 13400, 52],
        ['Astronomers say the satellite constellation problem is measurably worse', 'space', 4, 'A three-year survey quantifies what observatories have reported anecdotally: a rising share of long-exposure frames now arrive streaked.', false, 12200, 63],
        ['Researchers publish an open benchmark for agent reliability', 'ai', 5, 'The suite measures how often autonomous systems fail quietly, which the authors argue matters more than peak capability scores.', false, 11050, 78],
        ['Security teams are drowning in alerts. Some are simply turning them off.', 'security', 2, 'Interviews with two dozen practitioners describe a tuning problem that has become a staffing problem, and increasingly a governance one.', false, 9900, 95],
        ['Inside the accelerator making a contrarian bet on climate hardware', 'startups', 1, 'Software returns faster. The partners think the durable companies of the next decade will be the ones that had to build factories.', false, 8600, 112],
        ['The travel chargers worth carrying, after three months of testing', 'gadgets', 3, 'We ran eighteen chargers through airports, hotel rooms and one very cold train. Four were worth the space in a bag.', false, 7450, 140],
        ['Fusion startups quietly moved their timelines up', 'space', 5, 'The revisions are modest and heavily caveated, but several teams now describe grid-scale demonstrations in years rather than decades.', false, 6300, 168],
        ['Why inference costs fell faster than almost anyone forecast', 'ai', 0, 'A combination of better scheduling, cheaper memory and aggressive quantisation compounded into a curve nobody put in a slide deck.', false, 5200, 205],
        ['The ransomware economy is consolidating around fewer, larger crews', 'security', 2, 'Takedowns removed the middle of the market. What remains is a smaller number of groups with better tooling and more patience.', false, 4400, 250],
        ['Down rounds are back, and founders are finally willing to talk about them', 'startups', 5, 'The stigma is fading faster than the valuations, and a generation of operators is learning to run a company without a rising line.', false, 3600, 310],
        ['Repairability scores are quietly changing what phones get built', 'gadgets', 3, 'Regulation aimed at consumers is landing hardest on industrial design, and the adhesive is the first thing to go.', false, 2800, 420],
        ['Ground-based telescopes are getting a software upgrade that acts like new glass', 'space', 4, 'Reprocessing archival data with modern deconvolution is recovering detail that observatories assumed was lost to the atmosphere.', false, 1950, 560],
    ];

    /**
     * Seed the categories, authors and articles.
     */
    public function run(): void
    {
        $categories = $this->seedCategories();
        $authors = $this->seedAuthors();

        foreach (self::ARTICLES as $index => [$title, $categorySlug, $authorIndex, $excerpt, $featured, $views, $hoursAgo]) {
            $slug = Str::slug($title);

            Article::query()->create([
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'body' => $this->body($excerpt, $index),
                'thumbnail_url' => "https://picsum.photos/seed/{$slug}/1200/800",
                'category_id' => $categories[$categorySlug]->id,
                'author_id' => $authors[$authorIndex]->id,
                'views_count' => $views,
                'is_featured' => $featured,
                'published_at' => now()->subHours($hoursAgo),
            ]);
        }

        // Two stories that must never appear on the public site. They exist so
        // the Article::published() scope is visibly doing something.
        Article::factory()->draft()->create([
            'title' => 'Draft: this story is unfinished and should never render',
            'slug' => 'draft-should-never-render',
            'category_id' => $categories['ai']->id,
            'author_id' => $authors[0]->id,
        ]);

        Article::factory()->scheduled()->create([
            'title' => 'Scheduled: this story is embargoed until next week',
            'slug' => 'scheduled-should-never-render',
            'category_id' => $categories['startups']->id,
            'author_id' => $authors[1]->id,
        ]);
    }

    /**
     * @return array<string, Category> keyed by slug
     */
    private function seedCategories(): array
    {
        $categories = [];

        foreach (self::CATEGORIES as $attributes) {
            // updateOrCreate keeps re-seeding idempotent without duplicating rows.
            $categories[$attributes['slug']] = Category::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }

        return $categories;
    }

    /**
     * @return array<int, Author> keyed by their index in self::AUTHORS
     */
    private function seedAuthors(): array
    {
        $authors = [];

        foreach (self::AUTHORS as $index => $attributes) {
            $authors[$index] = Author::query()->updateOrCreate(
                ['name' => $attributes['name']],
                [
                    'bio' => $attributes['bio'],
                    'avatar_url' => 'https://i.pravatar.cc/160?u='.urlencode($attributes['name']),
                ],
            );
        }

        return $authors;
    }

    /**
     * Build placeholder article copy.
     *
     * The body is stored as plain text: blank lines separate blocks, a leading
     * "## " marks a subheading and a leading "> " marks a pull quote. The
     * <x-article-body> component renders those blocks with escaping, so nothing
     * in this column can inject markup.
     */
    private function body(string $excerpt, int $index): string
    {
        // Rotating pools keep the 20 demo bodies from reading identically.
        $observations = [
            'The details were confirmed by two people familiar with the plans, both of whom asked not to be named because the work is not public.',
            'Executives have been careful to frame the change as an experiment, which is usually a sign that the internal debate is not settled.',
            'Competitors have spent the past year telling customers the opposite, and some of those contracts come up for renewal soon.',
            'Analysts covering the sector had modelled a slower path, and several have already revised their notes.',
            'The reaction from practitioners has been noticeably warmer than the reaction from the vendors who sell to them.',
            'None of this is settled, and the people closest to the work are the least willing to predict how it lands.',
        ];

        $context = [
            'What makes the shift hard to read is that the underlying numbers improved while the story around them got more complicated.',
            'Similar promises have been made before, generally by teams with more funding and less to show for it.',
            'The constraint was never really technical. It was procurement, and procurement moves on its own schedule.',
            'Anyone who has run this kind of migration knows the second year is where the costs actually show up.',
            'The gap between demo and deployment remains the most expensive distance in the industry.',
            'For smaller teams the calculus is different, and that difference is starting to show up in hiring.',
        ];

        $quotes = [
            'We stopped optimising for the benchmark the moment we watched a customer use it.',
            'Everyone wants the roadmap. Almost nobody wants the maintenance contract that comes with it.',
            'The interesting failures are the quiet ones, because those are the ones you ship.',
            'You can buy the hardware in a quarter. Changing how people work takes about three years.',
        ];

        $pick = fn (array $pool, int $offset): string => $pool[($index + $offset) % count($pool)];

        return implode("\n\n", [
            $excerpt.' '.$pick($observations, 0),
            '## What happened',
            $pick($context, 0).' '.$pick($observations, 1).' '.$pick($context, 3),
            $pick($observations, 2).' '.$pick($context, 1),
            '> '.$pick($quotes, 0),
            '## Why it matters',
            $pick($context, 2).' '.$pick($observations, 3).' '.$pick($context, 4),
            $pick($observations, 4).' '.$pick($context, 5),
            'This is placeholder copy generated by the database seeder so the layout can be reviewed with realistic text lengths. Replace it with real reporting before launch.',
        ]);
    }
}
