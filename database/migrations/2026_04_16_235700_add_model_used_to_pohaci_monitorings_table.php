<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModelUsedToPohaciMonitoringsTable extends Migration
{
    public function up()
    {
        Schema::table('pohaci_monitorings', function (Blueprint $table) {
            $table->string('model_used')->nullable()->after('solution');
        });
    }

    public function down()
    {
        Schema::table('pohaci_monitorings', function (Blueprint $table) {
            $table->dropColumn('model_used');
        });
    }
}
