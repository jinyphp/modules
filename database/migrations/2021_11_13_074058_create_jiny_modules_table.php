<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJinyModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jiny_modules', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('enable')->default(1);

            $table->string('type')->nullable();

            $table->string('code')->nullable();
            $table->string('image')->nullable();
            $table->string('title')->nullable();

            $table->string('url')->nullable();
            $table->string('version')->nullable();
            $table->string('installed')->nullable();

            $table->string('description')->nullable();

            // 작업자ID
            $table->unsignedBigInteger('user_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jiny_modules');
    }
}
