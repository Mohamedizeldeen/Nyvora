<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Search engine indexing
    |--------------------------------------------------------------------------
    |
    | Environment-level switch for whether crawlers may index this deployment.
    |
    | It defaults to TRUE on purpose. Deriving this from APP_ENV means a deploy
    | that forgets APP_ENV=production silently disappears from Google, which is
    | a far more expensive mistake than a staging copy getting crawled.
    |
    | Set SITE_INDEXABLE=false on staging, review apps and any other public
    | copy of the site. This switch can only ever *restrict*: the admin toggle
    | in Settings has to agree before anything is indexed.
    |
    */

    'indexable' => env('SITE_INDEXABLE', true),

];
