<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$routes = app('router')->getRoutes();

$collection = [
    'info' => [
        'name' => 'REOS Mobile APIs Auto-Generated',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
    ],
    'variable' => [
        ['key' => 'base_url', 'value' => 'http://127.0.0.1:8000/api', 'type' => 'string']
    ],
    'auth' => [
        'type' => 'bearer',
        'bearer' => [
            ['key' => 'token', 'value' => '{{token}}', 'type' => 'string']
        ]
    ],
    'item' => []
];

foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'api') !== 0) continue;
    
    $method = $route->methods()[0];
    
    // Grouping
    $parts = explode('/', $uri);
    $folderName = 'General';
    if (isset($parts[1]) && in_array($parts[1], ['sales', 'manager', 'broker', 'auth'])) {
        $folderName = ucfirst($parts[1]);
    }

    $folderIndex = -1;
    foreach ($collection['item'] as $idx => $item) {
        if ($item['name'] === $folderName) {
            $folderIndex = $idx;
            break;
        }
    }
    
    if ($folderIndex === -1) {
        $collection['item'][] = ['name' => $folderName, 'item' => []];
        $folderIndex = count($collection['item']) - 1;
    }
    
    $cleanUri = preg_replace('/^api\/?/', '', $uri);
    $pathArray = array_values(array_filter(explode('/', $cleanUri)));
    
    $req = [
        'name' => $uri,
        'request' => [
            'method' => $method,
            'header' => [
                ['key' => 'Accept', 'value' => 'application/json']
            ],
            'url' => [
                'raw' => "{{base_url}}/" . $cleanUri,
                'host' => ["{{base_url}}"],
                'path' => $pathArray
            ]
        ]
    ];
    
    $collection['item'][$folderIndex]['item'][] = $req;
}

file_put_contents(__DIR__.'/REOS_Postman_Collection.json', json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Postman collection created at REOS_Postman_Collection.json\n";
