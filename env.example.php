<?php

declare(strict_types=1);

$variables = [
    'PRIVATE_KEY' => '10K6MDLxhsm9cXG5lsio4oHNaEr2UU_RlzPnDV2C-fU',
    'PUBLIC_KEY' => 'BJYxEPF0eVawhwlkHyiueIZodXUtwM0bZXi6ybU9TTCsQj-8-Yc7qg9VxqWPw1uMxmyhArnC7cSkskKzs4n_E7U',
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASSWORD' => '',
    'DB_NAME' => 'subitopuntoitalert'
];
foreach ($variables as $key => $value) {
    putenv("$key=$value");
}