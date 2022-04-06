<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('data_imports', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('file_id')->nullable()->unique();
            $table->foreign('file_id')->references('id')->on('files')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->string('batch')->nullable();
            $table->foreign('batch')->references('id')->on('job_batches')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->string('type')->index();

            $table->json('params')->nullable();

            $table->integer('successful');
            $table->integer('failed');

            $table->tinyInteger('status');

            $table->integer('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_imports');
    }
};
