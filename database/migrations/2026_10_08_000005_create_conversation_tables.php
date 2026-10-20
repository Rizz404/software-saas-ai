<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->uuid('guest_token')->unique();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('locale', 2)->default('id');
            $table->string('status')->default('active'); // active | handed_over | closed
            $table->string('current_scene', 20)->default('home');
            $table->foreignId('selected_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('selected_consultant_id')->nullable()->constrained('consultants')->nullOnDelete();
            $table->foreignId('selected_facility_id')->nullable()->constrained('company_knowledge_items')->nullOnDelete();
            $table->json('consultation_state')->nullable();
            $table->text('handover_summary')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // guest | assistant | staff | system
            $table->text('content')->nullable();
            $table->json('ui_payload')->nullable(); // products cards, consultants cards, confirmations rendered to the guest
            $table->json('tool_calls')->nullable();
            $table->timestamps();
        });

        Schema::create('handover_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('reason'); // special_request | complaint | group_consultation | scope_review | custom_proposal | contract_question | low_confidence
            $table->text('summary');
            $table->string('status')->default('open'); // open | resolved
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_requests');
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
    }
};
