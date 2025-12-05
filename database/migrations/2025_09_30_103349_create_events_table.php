<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('created_by');
            $table->foreign('club_id')->references('id')->on('clubs');
            $table->foreign('created_by')->references('id')->on('users');            
            $table->string('title',255)->nullable(false);
            $table->string('slug',255)->unique();
            $table->text('description');
            $table->dateTime('start_time')->nullable(false);
            $table->dateTime('end_time')->nullable(false);
            $table->enum('mode',['public','private'])->default('private');
            $table->integer('max_participants')->nullable();
            $table->enum('status',['pending','approved','rejected','active','canceled','completed']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
