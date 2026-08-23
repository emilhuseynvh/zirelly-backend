<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('channel', 30)->default('website')->after('status')->index();
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('total');
            $table->text('address')->nullable()->after('promocode_code');
            $table->text('note')->nullable()->after('address');
            $table->softDeletes();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropColumn(['channel', 'delivery_fee', 'address', 'note', 'deleted_at']);
        });
    }
};
