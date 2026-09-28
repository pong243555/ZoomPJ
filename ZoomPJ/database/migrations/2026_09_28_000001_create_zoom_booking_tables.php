<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zoom_connections', function (Blueprint $table) {
            $table->id();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('meeting_bookings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('zoom_meeting_id')->unique();
            $table->string('topic', 200);
            $table->text('agenda')->nullable();
            $table->dateTime('start_time');
            $table->unsignedSmallInteger('duration');
            $table->text('join_url');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
            $table->index('start_time');
        });

        Schema::create('zoom_booking_locks', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
        });

        DB::table('zoom_booking_locks')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('zoom_booking_locks');
        Schema::dropIfExists('meeting_bookings');
        Schema::dropIfExists('zoom_connections');
    }
};
