<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRejectedImportsTable extends Migration
{
    public function up()
    {
        Schema::create('rejected_imports', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('data_import_id')->unsigned();
            $table->foreign('data_import_id')->references('id')->on('data_imports')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rejected_imports');
    }
}
