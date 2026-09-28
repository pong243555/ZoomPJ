<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_bookings', function (Blueprint $table) {
            $table->string('status', 20)->default('booked')->after('join_url');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->integer('cancelled_by_user_id')->nullable()->after('cancelled_at');
            $table->foreign('cancelled_by_user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['status', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::table('meeting_bookings', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by_user_id']);
            $table->dropIndex(['status', 'start_time']);
            $table->dropColumn(['status', 'cancelled_at', 'cancelled_by_user_id']);
        });
    }
};
