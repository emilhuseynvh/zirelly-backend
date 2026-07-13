<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('side_image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('products_page_upload', function (Blueprint $table) {
            $table->id();
            $table->foreignId('products_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);

            $table->unique(['products_page_id', 'upload_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_page_upload');
        Schema::dropIfExists('products_pages');
    }
};