<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('phone', 30)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->date('birth_date')->nullable();
            $table->string('channel', 30)->default('website');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crm_user_id')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_notes');
        Schema::dropIfExists('contacts');
    }
};
