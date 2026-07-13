<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('banner_link')->nullable();
            $table->timestamps();
        });

        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('link')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('home_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_page_id')->constrained()->cascadeOnDelete();
            $table->string('value', 50);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_page_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('home_stats');
        Schema::dropIfExists('home_slides');
        Schema::dropIfExists('home_pages');
    }
};