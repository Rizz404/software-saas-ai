<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The company's products. Prices live here and nowhere else: the AI SaaS Consultant may
     * only quote a price it has just read through a tool call.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable();
            $table->json('prerequisites')->nullable(); // business_brief, api_access, ...
            $table->string('delivery_model')->nullable();
            $table->unsignedSmallInteger('trial_days')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_unavailable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
