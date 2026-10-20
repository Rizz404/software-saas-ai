<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->string('specialty')->default('solution');
            $table->unsignedTinyInteger('min_participants')->default(1);
            $table->unsignedTinyInteger('max_participants')->default(6);
            $table->json('features')->nullable();
            $table->decimal('consultation_fee', 12, 2)->default(0);
            $table->decimal('project_budget_from', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('consultant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultant_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        /**
         * Consultation capacity data: the one consultation the AI is NEVER allowed to
         * answer from memory. A consultation scheduling adapter can replace
         * it later without touching the AI or knowledge-base layers.
         */
        Schema::create('consultation_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultant_id')->constrained()->cascadeOnDelete();
            $table->date('consultation_date');
            $table->string('time_slot', 5); // HH:MM, the start of the slot
            $table->unsignedSmallInteger('total_sessions')->default(1);
            $table->unsignedSmallInteger('reserved_sessions')->default(0);
            $table->timestamps();
            $table->unique(['consultant_id', 'consultation_date', 'time_slot'], 'consultation_slots_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_slots');
        Schema::dropIfExists('consultant_images');
        Schema::dropIfExists('consultants');
    }
};
