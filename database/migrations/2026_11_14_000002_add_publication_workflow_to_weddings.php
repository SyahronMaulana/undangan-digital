<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            if (!Schema::hasColumn('weddings', 'submission_status')) {
                $table->string('submission_status', 40)->default('draft')->after('status');
            }
            if (!Schema::hasColumn('weddings', 'payment_status')) {
                $table->string('payment_status', 40)->default('belum_bayar')->after('submission_status');
            }
            if (!Schema::hasColumn('weddings', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('weddings', 'payment_confirmed_at')) {
                $table->timestamp('payment_confirmed_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('weddings', 'payment_verifier_id')) {
                $table->foreignId('payment_verifier_id')->nullable()->constrained('users')->nullOnDelete()->after('payment_confirmed_at');
            }
            if (!Schema::hasColumn('weddings', 'publisher_id')) {
                $table->foreignId('publisher_id')->nullable()->constrained('users')->nullOnDelete()->after('payment_verifier_id');
            }
            if (!Schema::hasColumn('weddings', 'revision_notes')) {
                $table->text('revision_notes')->nullable()->after('publisher_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $cols = [
                'submission_status', 'payment_status', 'submitted_at',
                'payment_confirmed_at', 'payment_verifier_id', 'publisher_id', 'revision_notes'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('weddings', $col)) {
                    if (in_array($col, ['payment_verifier_id', 'publisher_id'])) {
                        $table->dropForeign([$col]);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
