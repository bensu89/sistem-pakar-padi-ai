<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiagnosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');      // Lokasi foto disimpan
            $table->string('disease_name');    // Hasil diagnosa AI
            $table->float('confidence');       // Tingkat akurasi (%)
            $table->text('solution');          // Solusi yang diberikan
            $table->timestamps();              // Mencatat waktu otomatis (created_at)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diagnoses');
    }
}