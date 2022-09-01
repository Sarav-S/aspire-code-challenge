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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->float('amount', 8, 2);
            $table->tinyInteger('term');

            $table->tinyInteger('is_approved')->default(0);
            $table->dateTime('is_approved_on')->nullable();
            $table->foreignId('is_approved_by')
                ->nullable()
                ->references('id')
                ->on('admins');

            $table->string('status', 10)->default('PENDING');
            $table->dateTime('settled_on')->nullable();
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
        Schema::dropIfExists('loans');
    }
};
