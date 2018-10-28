<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataImportsTable extends Migration
{
    public function up()
    {
        Schema::create('data_imports', function (Blueprint $table) {
            $table->increments('id');

            $table->string('type');

            $table->json('summary');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_imports');
    }
}
