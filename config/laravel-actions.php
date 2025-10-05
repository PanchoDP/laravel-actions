<?php

// Here you can define the configuration for your Laravel Actions package.
declare(strict_types=1);

return [

    /*
   |--------------------------------------------------------------------------
   | Base Actions Folder
   |--------------------------------------------------------------------------
   |
   | This option defines the base folder where your actions are stored.
   | By default in app\Actions your actions will be stored.
   | You can change Actions folder to another name here.
   */

    'base_folder' => 'Actions',

    /*
    |--------------------------------------------------------------------------
    | Principal Method Name
    |--------------------------------------------------------------------------
    |
    | This option defines the method name that will be used in your action classes.
    | By default, it is set to 'handle'. You can change it to any other name
    */

    'method_name' => 'handle',

    /*
    |--------------------------------------------------------------------------
    | Directory Permissions
    |--------------------------------------------------------------------------
    |
    | This option defines the permissions for newly created directories.
    | By default, it is set to 0750 (owner: read/write/execute, group: read/execute, others: none).
    | You can change it to 0755 for more permissive access if needed.
    */

    'directory_permissions' => 0750,

];
