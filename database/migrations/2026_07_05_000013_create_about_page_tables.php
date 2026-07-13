<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->foreignId('section_image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('about_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_items');
        Schema::dropIfExists('about_pages');
    }
};