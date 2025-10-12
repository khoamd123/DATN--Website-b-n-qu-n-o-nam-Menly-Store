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
        Schema::create('event_member_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('evaluator_id');
            $table->unsignedBigInteger('member_id');
            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('club_id')->references('id')->on('clubs');
            $table->foreign('evaluator_id')->references('id')->on('users');
            $table->foreign('member_id')->references('id')->on('users');
            $table->integer('score');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_member_evaluations');
    }
};
