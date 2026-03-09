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
        Schema::table('events', function (Blueprint $table) {
            $table->time('time')->nullable()->after('date');
            $table->json('speakers')->nullable()->after('location');
            $table->json('about_event')->nullable()->after('speakers');
            $table->json('what_to_expect')->nullable()->after('about_event');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['time', 'speakers', 'about_event', 'what_to_expect']);
        });
    }
};
