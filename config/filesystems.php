<?php

/*
 * Akar disk `public`.
 *
 * Bawaannya `storage/app/public`, dijangkau dari document root lewat symlink —
 * layout standar Laravel. Di server produksi (LiteSpeed) layout itu punya satu
 * akibat yang mahal: konfigurasi per-direktori DILEWATI untuk path yang dicapai
 * lewat symlink ke luar document root. Artinya tidak ada satu pun direktif
 * `.htaccess` yang berlaku untuk /storage — termasuk `Header set Cache-Control`,
 * sehingga seluruh gambar terkirim tanpa header cache dan pengunjung berulang
 * memvalidasi ulang setiap berkas satu per satu.
 *
 * Karena itu di sana disk ini diakar-kan ke direktori NYATA di dalam document
 * root (`public/storage`) lewat FILESYSTEM_PUBLIC_ROOT. Dibuat env, bukan
 * diubah langsung, karena instance smpialazka.com memakai repo yang sama dan
 * masih memakai layout symlink.
 */
$publicRoot = env('FILESYSTEM_PUBLIC_ROOT') ?: storage_path('app/public');

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => $publicRoot,
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    // Symlink hanya relevan selama disk publik masih berakar di storage/.
    // Bila sudah dipindah ke dalam public/, `storage:link` justru akan
    // bertabrakan dengan direktori nyata yang ada di sana.
    'links' => $publicRoot === storage_path('app/public')
        ? [public_path('storage') => storage_path('app/public')]
        : [],

];
