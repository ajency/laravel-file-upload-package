<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fileupload_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('object_type')->nullable();
            $table->integer('object_id')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('file_id')->nullable();
            $table->string('type');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fileupload_mapping');
    }
}
