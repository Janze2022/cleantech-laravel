<?php

$cloudDiskDefinitions = json_decode((string) env('LARAVEL_CLOUD_DISK_CONFIG', '[]'), true);
$cloudDisks = [];
$defaultCloudDiskName = null;

if (is_array($cloudDiskDefinitions)) {
    foreach ($cloudDiskDefinitions as $diskDefinition) {
        if (!is_array($diskDefinition) || empty($diskDefinition['disk'])) {
            continue;
        }

        $diskName = (string) $diskDefinition['disk'];

        $cloudDisks[$diskName] = [
            'driver' => 's3',
            'key' => $diskDefinition['access_key_id'] ?? null,
            'secret' => $diskDefinition['access_key_secret'] ?? null,
            'region' => $diskDefinition['default_region'] ?? 'auto',
            'bucket' => $diskDefinition['bucket'] ?? null,
            'url' => $diskDefinition['url'] ?? null,
            'endpoint' => $diskDefinition['endpoint'] ?? null,
            'use_path_style_endpoint' => (bool) ($diskDefinition['use_path_style_endpoint'] ?? false),
            'throw' => false,
        ];

        if (!empty($diskDefinition['is_default'])) {
            $defaultCloudDiskName = $diskName;
        }
    }
}

$envDiskName = env('PUBLIC_FILESYSTEM_DISK', env('FILESYSTEM_DISK', 'local'));
$defaultCloudDiskName = $defaultCloudDiskName ?: (array_key_exists($envDiskName, $cloudDisks) ? $envDiskName : null);

$awsCloudConfigAvailable =
    filled(env('AWS_ACCESS_KEY_ID')) &&
    filled(env('AWS_SECRET_ACCESS_KEY')) &&
    filled(env('AWS_BUCKET')) &&
    filled(env('AWS_ENDPOINT'));

$fallbackCloudDiskConfig = [
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

if ($awsCloudConfigAvailable && !isset($cloudDisks['s3'])) {
    $cloudDisks['s3'] = $fallbackCloudDiskConfig;
}

if ($awsCloudConfigAvailable && !$defaultCloudDiskName && $envDiskName !== 'local') {
    $defaultCloudDiskName = array_key_exists($envDiskName, $cloudDisks) ? $envDiskName : 's3';
}

$localPublicDiskConfig = [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => rtrim(env('APP_URL'), '/') . '/storage',
    'visibility' => 'public',
    'throw' => false,
];

$activeCloudDiskConfig = $defaultCloudDiskName && array_key_exists($defaultCloudDiskName, $cloudDisks)
    ? $cloudDisks[$defaultCloudDiskName]
    : null;

$disks = [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'throw' => false,
    ],

    // The app uses Storage::disk('public') for uploads and route-based serving.
    // On Laravel Cloud, point that disk at the attached bucket config.
    'public' => $activeCloudDiskConfig ?: $localPublicDiskConfig,

    'private' => $activeCloudDiskConfig ?: [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
        'throw' => false,
    ],
];

foreach ($cloudDisks as $diskName => $diskConfig) {
    $disks[$diskName] = $diskConfig;
}

if (!isset($disks['s3'])) {
    $disks['s3'] = $fallbackCloudDiskConfig;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */
    'default' => env('FILESYSTEM_DISK', $defaultCloudDiskName ?: 'local'),

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
