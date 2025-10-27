<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubResourceImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_resource_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_resource_id')->constrained('club_resources')->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_name');
            $table->string('image_type');
            $table->bigInteger('image_size'); // in bytes
            $table->string('thumbnail_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
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
        Schema::dropIfExists('club_resource_images');
    }
}
