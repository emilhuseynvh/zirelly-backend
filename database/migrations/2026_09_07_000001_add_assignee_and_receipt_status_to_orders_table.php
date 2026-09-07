<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('crm_assignee_id')
                ->nullable()
                ->after('contact_id')
                ->constrained('crm_users')
                ->nullOnDelete();
            $table->string('receipt_status', 20)->default('pending')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crm_assignee_id');
            $table->dropColumn('receipt_status');
        });
    }
};
