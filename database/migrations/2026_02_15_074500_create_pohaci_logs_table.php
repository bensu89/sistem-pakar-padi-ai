<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pohaci_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // ID User (jika login)
            $table->text('user_question');            // Pertanyaan petani
            $table->string('target_url')->nullable(); // Link artikel (jika ada)

            // Simpan teks mentah website (PENTING untuk dataset AI masa depan)
            $table->longText('raw_context')->nullable();

            $table->longText('ai_answer');            // Jawaban Pohaci

            // Status: success, error, warning
            $table->string('status')->default('success');

            // Metadata teknis (Model, Token, IP)
            $table->json('meta_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pohaci_logs');
    }
};