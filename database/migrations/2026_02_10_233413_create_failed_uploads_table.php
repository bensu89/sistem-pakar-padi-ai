<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('failed_uploads', function (Blueprint $table) {
        $table->id();
        $table->string('image_path');      // Lokasi gambar
        $table->string('reason')->default('Bukan Daun Padi'); // Alasan penolakan
        $table->timestamps();              // Waktu kejadian
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_uploads');
    }
}
