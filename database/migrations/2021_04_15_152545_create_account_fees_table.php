<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_fees', function (Blueprint $table) {
            $table->id();
            $table->integer('maximum_price')->nullable();
            $table->integer('minimum_price')->nullable();
            $table->integer('maximum_fee')->nullable();
            $table->integer('minimum_fee')->nullable();
            $table->integer('percentage_price');
            $table->integer('account_type_id');
            $table->bigInteger('last_updated_editor_id');
            $table->bigInteger('creator_id');
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
        Schema::dropIfExists('account_fees');
    }
}
