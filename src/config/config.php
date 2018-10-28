<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ignored Files
    |--------------------------------------------------------------------------
    |
    | Maneuver will check .gitignore for ignore files, but you can conveniently
    | add here additional files to be ignored.
    |
    */
    'ignored' => [],

    /*
    |--------------------------------------------------------------------------
    | Forced Files and Folders
    |--------------------------------------------------------------------------
    |
    | If you need to upload files/folders in rare moments, like to update a
    | vendor folder, configure this array with te files/folders list.
    | Use --with-forced-files to include this files/folders to the list of files
    | and folders to upload.
    |
    | To add full vendor using one of this options:
    | 'composer.json'
    | 'vendor'
    | 'vendor/'
    |
    */
    'forced' => [],

    /*
    |--------------------------------------------------------------------------
    | Default server
    |--------------------------------------------------------------------------
    |
    | Default server to deploy to when running 'deploy' without any arguments.
    | If this options isn't set, deployment will be run to all servers.
    |
    */
    'default' => 'development',

    /*
    |--------------------------------------------------------------------------
    | Connections List
    |--------------------------------------------------------------------------
    |
    | Servers available for deployment. Specify one or more connections, such
    | as: 'deployment', 'production', 'staging'; each with its own credentials.
    |
    */

    'connections' => [

        'development' => [
            'scheme'    => 'ftp',
            'host'      => 'yourdevserver.com',
            'user'      => 'user',
            'pass'      => 'myawesomepass',
            'path'      => '/path/to/server/',
            'port'      => 21,
            'passive'   => true
        ],

        'production' => [
            'scheme'    => 'ftp',
            'host'      => 'yourserver.com',
            'user'      => 'user',
            'pass'      => 'myawesomepass',
            'path'      => '/path/to/server/',
            'port'      => 21,
            'passive'   => true
        ],

    ],

];