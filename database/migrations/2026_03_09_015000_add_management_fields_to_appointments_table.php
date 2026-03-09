<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('metadata');
            $table->text('cancellation_reason')->nullable()->after('admin_notes');
            $table->text('reschedule_reason')->nullable()->after('cancellation_reason');
            $table->datetime('original_from')->nullable()->after('reschedule_reason');
            $table->datetime('original_to')->nullable()->after('original_from');
            $table->string('change_request_status')->nullable()->after('original_to');
            $table->datetime('requested_new_from')->nullable()->after('change_request_status');
            $table->datetime('requested_new_to')->nullable()->after('requested_new_from');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn([
                'admin_notes',
                'cancellation_reason',
                'reschedule_reason',
                'original_from',
                'original_to',
                'change_request_status',
                'requested_new_from',
                'requested_new_to',
                'confirmed_at',
            ]);
        });
    }
};
