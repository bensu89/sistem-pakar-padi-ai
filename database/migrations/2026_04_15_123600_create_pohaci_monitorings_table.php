<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePohaciMonitoringsTable extends Migration
{
    public function up()
    {
        Schema::create('pohaci_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reporter_name')->nullable()->index();
            $table->string('reporter_email')->nullable()->index();
            $table->string('image_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('coordinate_source')->nullable()->comment('gps, exif, manual, request, none');
            $table->string('location_label')->nullable();
            $table->string('disease_name')->nullable()->index();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->text('solution')->nullable();
            $table->decimal('ndvi_value', 8, 5)->nullable();
            $table->string('satellite_source')->nullable();
            $table->string('analysis_mode')->default('standard')->index();
            $table->text('recommendation')->nullable();
            $table->string('followup_status')->default('pending')->index();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pohaci_monitorings');
    }
}
