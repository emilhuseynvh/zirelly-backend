<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('button_link')->nullable();
            $table->unsignedSmallInteger('delay_seconds')->default(5);
            $table->boolean('is_active')->default(false);
            $table->boolean('show_once')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
