<?php

$cloudDiskName = env('PUBLIC_FILESYSTEM_DISK', env('FILESYSTEM_DISK', 'local'));
$cloudCredentialsPresent =
    filled(env('AWS_ACCESS_KEY_ID')) &&
    filled(env('AWS_SECRET_ACCESS_KEY')) &&
    filled(env('AWS_BUCKET')) &&
    filled(env('AWS_ENDPOINT'));

$useCloudBackedPublicDisk = $cloudCredentialsPresent && !empty($cloudDiskName) && $cloudDiskName !== 'local';

$cloudDiskConfig = [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', env('AWS_REGION', 'auto')),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'throw' => false,
];

$localPublicDiskConfig = [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => rtrim(env('APP_URL'), '/') . '/storage',
    'visibility' => 'public',
    'throw' => false,
];

$disks = [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'throw' => false,
    ],

    // Keep the "public" disk as the app's unified upload disk so controllers
    // can read/write the same way in local dev and on Laravel Cloud.
    'public' => $useCloudBackedPublicDisk ? $cloudDiskConfig : $localPublicDiskConfig,

    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
        'throw' => false,
    ],

    's3' => $cloudDiskConfig,
];

if ($useCloudBackedPublicDisk && !array_key_exists($cloudDiskName, $disks)) {
    $disks[$cloudDiskName] = $cloudDiskConfig;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */
    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */
    'disks' => $disks,

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
