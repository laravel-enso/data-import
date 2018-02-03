<?php

return [
    'example' => [
        'label' => 'Example import',
        'template' => 'app/Imports/Templates/exampleTemplate.json',
        'importerClass' => 'App\\Imports\\Importers\\ExampleImporter',
        'validatorClass' => 'App\\Imports\\Validators\\CustomValidator', // optional
        'entryLimit' => 5000, // optional, per sheet, the default value is 5000
        'stopsOnIssues' => false, // optional, the default value is false
    ],
];
