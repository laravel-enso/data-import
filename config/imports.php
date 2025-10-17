<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validations
    |--------------------------------------------------------------------------
    | This flag sets whether validations are also executed in production
    | or only in local/development.
    | Values: 'always/local/yourEnv...'
    |
    */

    'validations' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Chunk Size
    |--------------------------------------------------------------------------
    | The default row chunk for the import queued jobs. Can be overwritten
    | per template.
    |
    */

    'chunkSize' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Queues
    |--------------------------------------------------------------------------
    | Specifies the queue for each type of job during the import process.
    | The splitting process will be the longest and should be set
    | to a larger value.
    */

    'queues' => [
        'splitting' => 'heavy',
        'processing' => 'light',
        'rejected' => 'heavy',
        'notifications' => 'notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flexible
    |--------------------------------------------------------------------------
    | Sets if the structure should be strict or may have additional sheets
    | or columns which will be ignored.
    |
    */

    'flexible' => false,

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    | Sets the default timeout used for chunk splitting jobs & rejected
    | summary export. It can be overwritten in the import's template.
    |
    */

    'timeout' => 60 * 5,

    /*
    |--------------------------------------------------------------------------
    | Error Column
    |--------------------------------------------------------------------------
    | Each import with failed entries will generate a rejected xlsx report
    | with the same structure as the import and an extra errors column.
    | This flag sets the name of the errors column.
    |
    */

    'errorColumn' => '_errors',

    /*
    |--------------------------------------------------------------------------
    | Unknown Import Error Message
    |--------------------------------------------------------------------------
    | If the developer misses covering with validations an error scenario
    | when that scenario is met the importer will report and unknown
    | error. Here you can customize that error message.
     */

    'unknownError' => 'Undetermined import error',

    /*
    |--------------------------------------------------------------------------
    | Notification channels
    |--------------------------------------------------------------------------
    | After each import the user will be notified by email. Additionally
    | a notification can be broadcasted to the user.
    |
    */

    'notifications' => ['broadcast', 'database'],

    /*
    |--------------------------------------------------------------------------
    | Seeder path
    |--------------------------------------------------------------------------
    |
    | The path for excel files used for the ExcelSeeder class inside the
    |
    */

    'seederPath' => database_path('seeders/xlsx'),

    /*
    |--------------------------------------------------------------------------
    | Notifiable Ids
    |--------------------------------------------------------------------------
    | You can add here user ids separated by comma if you want to use post
    | finalize notifications to other users than the importer.
    |
    */

    'notifiableIds' => env('DATA_IMPORT_NOTIFIABLE_IDS', null),

    /*
    |--------------------------------------------------------------------------
    | Retain imports for a number of days
    |--------------------------------------------------------------------------
    | The default period in days for retaining imports, used by the
    | enso:data-import:purge command. Leave it 0 if you want to
    | retain files forever.
     */

    'retainFor' => (int) env('IMPORT_RETAIN_FOR', 0),

    /*
    |--------------------------------------------------------------------------
    | How many hours until cancelling stuck imports
    |--------------------------------------------------------------------------
    | The number of hours after the enso:data-import:reset-stuck command resets
    | stuck imports to cancel.
    |
    */

    'cancelStuckAfter' => (int) env('IMPORT_CANCEL_STUCK_AFTER', 24),

    /*
    |--------------------------------------------------------------------------
    | Configurations
    |--------------------------------------------------------------------------
    | Holds your import configuration. 'label' is used for the main page select
    | and template is the full path to your import template JSON.
    |
    */

    'configs' => [
        'userGroups' => [
            'label' => 'User Groups',
            'template' => 'vendor/laravel-enso/data-import/src/Tests/userGroups.json',
        ],
    ],

];
