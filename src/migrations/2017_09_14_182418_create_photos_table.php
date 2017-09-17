<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fileuploads_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('slug');
            $table->string('url')->nullable();
            $table->boolean('is_public');
            $table->integer('owner')->nullable();
            $table->string('caption')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('height')->nullable();
            $table->integer('width')->nullable();
            $table->string('alt_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fileuploads_photos');
    }
}
