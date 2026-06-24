<?php
/**
 * Site content data.
 *
 * Edit these arrays to add/remove hero slides or games — the markup in
 * index.php loops over them, so no HTML changes are needed.
 *
 * NOTE: slide "title" may contain a <span> for the accent colour, so it is
 * rendered as trusted HTML. Keep it to simple, hand-authored markup.
 */

return [
    'slides' => [
        [
            'tag'   => 'Featured Title',
            'title' => 'The <span>Streets</span> Game',
            'desc'  => 'Step onto the pitch. Master the streets. Dominate every match with quick decisions and raw skill.',
            'btn'   => 'Play Now',
            'img'   => 'assets/img/slide-streets.png',
        ],
        [
            'tag'   => 'Football Predictions',
            'title' => '<span>Predict</span> & Win',
            'desc'  => 'Daily match predictions, power-up bundles, and high-stakes leaderboards. Can you go unbeaten?',
            'btn'   => 'Enter the League',
            'img'   => 'assets/img/slide-predict.png',
        ],
        [
            'tag'   => 'New Drop',
            'title' => 'Red Card <span>Shield</span>',
            'desc'  => 'Protect your streak. The Red Card Shield Bundle keeps you in the game when the pressure is highest.',
            'btn'   => 'Get the Bundle',
            'img'   => 'assets/img/slide-redcard.png',
        ],
        [
            'tag'   => 'Arcade Mode',
            'title' => 'Fast. <span>Furious.</span> Free.',
            'desc'  => 'Pick up and play anytime. Rapid-fire challenges designed to drain your battery and feed your obsession.',
            'btn'   => 'Start Playing',
            'img'   => 'assets/img/slide-arcade.png',
        ],
    ],

    'games' => [
        [
            'genre'  => 'Street Football',
            'title'  => 'The Streets Game',
            'rating' => '4.9',
            'meta'   => ['2–8 Players', 'Free to Play'],
            'img'    => 'assets/img/game-streets.png',
        ],
        [
            'genre'  => 'Predictions',
            'title'  => 'Predict & Win',
            'rating' => '4.8',
            'meta'   => ['Daily'],
            'img'    => 'assets/img/game-predict.png',
        ],
        [
            'genre'  => 'Power-Ups',
            'title'  => 'Red Card Shield',
            'rating' => '4.7',
            'meta'   => ['Bundle Mode'],
            'img'    => 'assets/img/game-redcard.png',
        ],
        [
            'genre'  => 'Arcade',
            'title'  => 'Rapid Fire',
            'rating' => '4.6',
            'meta'   => ['Solo / Co-op'],
            'img'    => 'assets/img/game-rapidfire.png',
        ],
        [
            'genre'  => 'Leaderboard',
            'title'  => 'Season League',
            'rating' => '4.9',
            'meta'   => ['Competitive'],
            'img'    => 'assets/img/game-league.png',
        ],
    ],

    // Topics + games for the contact form selects.
    'subjects' => [
        'Game Feedback',
        'Business / Partnership',
        'Bug Report',
        'Power-Up Support',
        'Other',
    ],

    'ticker' => [
        'Battery Killer Gamez',
        'Football Predictions Live',
        'Red Card Shield Bundle',
        'New Titles Dropping Soon',
        'Daily Challenges',
        'Power-Up Store Open',
        'Join the League',
        'Play. Predict. Dominate.',
    ],

    'stats' => [
        ['target' => 50000,   'label' => 'Players Online'],
        ['target' => 12,      'label' => 'Games Available'],
        ['target' => 1400000, 'label' => 'Matches Predicted'],
        ['target' => 99,      'label' => '% Uptime'],
    ],

    // Default About content. This is only used to SEED the database on first
    // run — after that, edit it in the admin under "About".
    'about' => [
        'html' =>
            "<p><strong>Battery Killer Gamez</strong> was born from a simple obsession — making games that are impossible to put down. We build fast, addictive, sports-driven experiences that push every session to the limit.</p>\n" .
            "<p>From intense <strong>street football</strong> to data-driven <strong>prediction leagues</strong>, every title we ship is designed to drain your battery and leave you wanting more. We believe gaming should feel electric — raw, competitive, and alive.</p>\n" .
            "<p>Powered by a lean, passionate team, we ship fast and iterate faster. Community feedback shapes everything we build. <strong>Your input. Our engine. One goal: the perfect game.</strong></p>",
        'tags' => ['Football', 'Predictions', 'Arcade', 'Power-Ups', 'Daily Challenges', 'Leaderboards'],
    ],

    // Default standalone pages, seeded on first run (footer links to these).
    'pages' => [
        [
            'slug'    => 'privacy-policy',
            'title'   => 'Privacy Policy',
            'in_menu' => 0,
            'body'    => "<p>This is a placeholder Privacy Policy for Battery Killer Gamez. Edit it in the admin under <strong>Pages</strong>.</p><p>Describe here what data you collect (e.g. contact form submissions), how it is used, and how visitors can reach you with privacy questions.</p>",
        ],
        [
            'slug'    => 'terms-of-service',
            'title'   => 'Terms of Service',
            'in_menu' => 0,
            'body'    => "<p>This is a placeholder Terms of Service for Battery Killer Gamez. Edit it in the admin under <strong>Pages</strong>.</p><p>Spell out the rules for using your games and site here.</p>",
        ],
    ],
];
