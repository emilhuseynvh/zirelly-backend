<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_page_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('products_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('image_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('link')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('products_page_upload')) {
            $now = now();

            DB::table('products_page_upload')
                ->orderBy('position')
                ->get()
                ->each(function ($row) use ($now) {
                    DB::table('products_page_slides')->insert([
                        'products_page_id' => $row->products_page_id,
                        'image_id' => $row->upload_id,
                        'position' => $row->position,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });

            Schema::dropIfExists('products_page_upload');
        }
    }

    public function down(): void
    {
        Schema::create('products_page_upload', function (Blueprint $table) {
            $table->id();
            $table->foreignId('products_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);

            $table->unique(['products_page_id', 'upload_id']);
        });

        Schema::dropIfExists('products_page_slides');
    }
};
