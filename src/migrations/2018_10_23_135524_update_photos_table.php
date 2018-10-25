<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fileupload_photos', function (Blueprint $table) {
            $table->json('dimensions')->nullable();
            $table->json('image_size')->nullable();
            $table->json('photo_attributes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fileupload_photos', function (Blueprint $table) {
            $table->dropColumn('dimensions');
            $table->dropColumn('image_size');
            $table->dropColumn('photo_attributes');
        });
    }
}
