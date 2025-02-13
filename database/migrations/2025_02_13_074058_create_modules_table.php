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
        Schema::create('modules', function (Blueprint $table) {
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

            $table->text('description')->nullable();

            // 관리자자
            $table->string('admin')->nullable();
            $table->unsignedBigInteger('admin_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
};
