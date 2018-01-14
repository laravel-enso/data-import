<?php

return [
    'example' => [
        'label' => 'Example import',
        'template' => 'app/Importing/Templates/exampleTemplate.json',
        'importerClass' => 'App\\Importing\\Importers\\ExampleImporter',
        'customValidatorClass' => 'App\\Importing\\Validators\\CustomValidator', // optional
        'sheetEntriesLimit' => 5000, // optional, the default value is 5000
        'stopOnErrors' => false, // optional, the default value is false
    ],
];
