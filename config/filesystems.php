<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY', 'AKIAWCS5XPAGYPCAYUJA'),
            'secret' => env('AWS_SECRET', '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'),
            'region' => env('AWS_REGION', 'ap-south-1'),
            'bucket' => env('S3_BUCKET', 'myrajasthanclub'),
        ],

        'azure' => [
            'driver'    => 'azure',
            'name'      => env('AZURE_STORAGE_NAME','mrcstaging'),
            'key'       => env('AZURE_STORAGE_KEY','ZpKe+LBkKTm8/QrIMPv6eBxRbo0MRc1Xf7EnytQcgIvOiz1BUH8IDNSQ0t4ukBgQe+HWwf2yVW3Y7n89K3V3XA=='),
            'container' => env('AZURE_STORAGE_CONTAINER','laravel-images'),
            'url'       => env('AZURE_STORAGE_URL','https://mrcstaging.blob.core.windows.net/laravel-images'),
            'prefix'    => null,
        ],


    ],

];
