<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePohaciAiTables extends Migration
{
    public function up()
    {
        Schema::create('pohaci_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->default('chat')->comment('chat, image, whatsapp, api');
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('pohaci_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('pohaci_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sender_type')->comment('farmer, ai, system');
            $table->longText('content');
            $table->boolean('has_attachment')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('pohaci_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('pohaci_conversations')->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('pohaci_messages')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('source')->comment('request, exif, manual, default');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('label')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('pohaci_satellite_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('pohaci_conversations')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('pohaci_locations')->cascadeOnDelete();
            $table->string('satellite_source')->default('COPERNICUS/S2_SR_HARMONIZED');
            $table->decimal('ndvi_value', 8, 5)->nullable();
            $table->date('captured_from')->nullable();
            $table->date('captured_to')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('pohaci_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('pohaci_conversations')->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('pohaci_messages')->nullOnDelete();
            $table->string('mode')->comment('spatial, standard, spatial_fallback');
            $table->string('risk_level')->nullable();
            $table->text('result_text');
            $table->text('fertilizer_suggestion')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pohaci_recommendations');
        Schema::dropIfExists('pohaci_satellite_observations');
        Schema::dropIfExists('pohaci_locations');
        Schema::dropIfExists('pohaci_messages');
        Schema::dropIfExists('pohaci_conversations');
    }
}
