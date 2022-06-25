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
        Schema::create('webpages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crawler_id');
            $table->text('url');
            $table->longText('content');
            $table->float('loads', 14, 4);
            $table->timestamps();
            $table->integer('level');
            $table->text('response_code');
            $table->foreign('crawler_id')->references('id')->on('crawler_jobs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webpages');
    }
};
