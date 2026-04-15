<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIotSensorDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iot_sensor_data', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index()->comment('ID Perangkat IoT');
            
            // Pengukuran Tanah
            $table->decimal('soil_moisture', 5, 2)->nullable()->comment('Kelembapan Tanah (%)');
            $table->decimal('soil_ph', 4, 2)->nullable()->comment('pH Tanah (0-14)');
            $table->decimal('soil_temperature', 5, 2)->nullable()->comment('Suhu Tanah (°C)');
            
            // NPK Sensor (Nitrogen, Fosfor, Kalium) jika ada
            $table->decimal('nitrogen', 6, 2)->nullable()->comment('Kadar Nitrogen (mg/kg)');
            $table->decimal('phosphorus', 6, 2)->nullable()->comment('Kadar Fosfor (mg/kg)');
            $table->decimal('potassium', 6, 2)->nullable()->comment('Kadar Kalium (mg/kg)');
            
            // Lingkungan / Udara (Optional)
            $table->decimal('ambient_temperature', 5, 2)->nullable()->comment('Suhu Udara Sekitar (°C)');
            $table->decimal('ambient_humidity', 5, 2)->nullable()->comment('Kelembapan Udara (%)');
            
            // Konteks Tambahan
            $table->string('soil_type')->nullable()->comment('Jenis Tanah (jika dikonfigurasi)');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iot_sensor_data');
    }
}
