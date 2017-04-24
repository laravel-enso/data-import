<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 13:51
 */

namespace App\Importing\Templates;

use LaravelEnso\DataImport\app\Classes\BaseTemplate;

class ExampleTemplate extends BaseTemplate {

	public $jsonTemplate ='{
		"sheets": [
			{
				"name": "sheet1",
				"columns": [
					{
						"name": "column_name",
						"laravelValidations": "string|date|email",
						"complexValidations": [
							{ "type": "unique_in_column" },
							{ "type": "exists_in_sheet", "sheet": "sheet2", "column: "column_name" }
						],
						"customValidations": [
							{
								"type": "cookoo"
							}
						],
						"severity": "critical"
					}
				]
			}
		]
	}';
}