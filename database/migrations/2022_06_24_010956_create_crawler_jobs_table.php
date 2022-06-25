<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawler_jobs', function (Blueprint $table) {
            $table->id();
            $table->text('entry_point');
            $table->float('start_time', 14, 4);
            $table->float('end_time', 14, 4);
            $table->boolean('status');
            $table->text('response_code');
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
        Schema::dropIfExists('crawler_jobs');
    }
};
