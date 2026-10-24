<?php

// A stand-in for the ads API, for clicking through the integration form
// locally (the tests fake the HTTP call instead):
//
//     php -S 127.0.0.1:8099 scripts/fake-ads-api.php
//
// with ADS_API_URL=http://127.0.0.1:8099 in .env. It accepts any key except
// ones that start with "bad".

$key = substr($_SERVER['HTTP_AUTHORIZATION'] ?? '', strlen('Bearer '));

header('Content-Type: application/json');

if ($key === '' || str_starts_with($key, 'bad')) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid_key']);

    return;
}

echo json_encode(['id' => 'acct_demo']);
