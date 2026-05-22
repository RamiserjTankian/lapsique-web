<?php

return [
    'availability_days' => 11,
    'default_start_time' => '14:00',
    'default_end_time' => '17:00',
    'default_duration_minutes' => 120,
    'default_advance_hours' => 24,
    'dj_set_price' => 12000,

    'skip_payment_hosts' => [
        'localhost',
        '127.0.0.1',
    ],
    'skip_payment_host_suffixes' => [
        '.test',
    ],
];
