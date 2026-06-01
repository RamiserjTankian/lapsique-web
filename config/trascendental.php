<?php

return [
    'enabled_as_primary' => env('APP_SITE') === 'trascendental',

    'lead_notify_email' => env('TRASCENDENTAL_LEAD_NOTIFY_EMAIL'),

    'whatsapp' => env('TRASCENDENTAL_WHATSAPP', '529993389943'),

    'email' => env('TRASCENDENTAL_EMAIL', 'trascendentalbooking@gmail.com'),

    'instagram_url' => env('TRASCENDENTAL_INSTAGRAM_URL', 'https://www.instagram.com/trascendentalby/'),

    'resident_advisor_url' => env('TRASCENDENTAL_RA_URL', 'https://es-mx.ra.co/promoters/132583'),
];
