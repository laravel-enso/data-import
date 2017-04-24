<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->unsigned();
            $table->string('original_name');
            $table->string('saved_name');
            $table->string('comment')->nullable();
            $table->json('summary');
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_imports');
    }
}
