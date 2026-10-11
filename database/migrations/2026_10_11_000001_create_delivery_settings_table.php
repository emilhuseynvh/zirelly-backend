<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('baku_fee', 8, 2)->default(5);
            $table->decimal('baku_free_threshold', 8, 2)->default(70);
            $table->decimal('region_fee', 8, 2)->default(10);
            $table->decimal('nakhchivan_fee', 8, 2)->default(15);
            $table->decimal('karabakh_fee', 8, 2)->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_settings');
    }
};
