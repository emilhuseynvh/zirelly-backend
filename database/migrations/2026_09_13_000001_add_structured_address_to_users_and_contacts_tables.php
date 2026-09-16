<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address_city', 100)->nullable()->after('address');
            $table->string('address_district', 100)->nullable()->after('address_city');
            $table->string('address_street')->nullable()->after('address_district');
            $table->string('address_building', 50)->nullable()->after('address_street');
            $table->string('address_apartment', 50)->nullable()->after('address_building');
            $table->string('address_note', 500)->nullable()->after('address_apartment');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('address_city', 100)->nullable()->after('address');
            $table->string('address_district', 100)->nullable()->after('address_city');
            $table->string('address_street')->nullable()->after('address_district');
            $table->string('address_building', 50)->nullable()->after('address_street');
            $table->string('address_apartment', 50)->nullable()->after('address_building');
            $table->string('address_note', 500)->nullable()->after('address_apartment');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address_city',
                'address_district',
                'address_street',
                'address_building',
                'address_apartment',
                'address_note',
            ]);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'address_city',
                'address_district',
                'address_street',
                'address_building',
                'address_apartment',
                'address_note',
            ]);
        });
    }
};
