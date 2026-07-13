<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('field', 64);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(
                ['translatable_type', 'translatable_id', 'language_id', 'field'],
                'translations_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};