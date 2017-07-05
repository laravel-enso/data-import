<?php

return [
    'validationLabels' => [
        'extra_sheets' => 'Extra Sheets',
        'missing_sheets' => 'Missing Sheets',
        'extra_columns' => 'Extra Columns',
        'missing_columns' => 'Missing Columns',
        'exists_in_sheet' => 'Value must exist in the sheet',
        'unique_in_column' => 'Value must be unique in its column',
        'duplicate_lines' => 'This sheet lines are doubles',
        'sheet_entries_limit_exceded' => 'Exceded the entries limit of'
    ],
    'configs' => [
        'contactPersons' => [
            'label' => 'Contact Persons',
            'template' => 'contactPersons.json',
            'importerClass' => 'App\\Importing\\Importers\\ContactPersonsImporter',
            'customValidatorClass' => 'App\\Importing\\Validators\\ContactPersonsValidator', // optional
            'sheetEntriesLimit' => 5000, // optional, the default value is 5000
        ],
    ],
];
