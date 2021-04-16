<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('discount_code')->unique();
            $table->integer('type_code')->default(0);

            $table->integer('maximum_price')->nullable();
            $table->integer('minimum_price')->nullable();
            $table->integer('maximum_discount')->nullable();
            $table->integer('minimum_discount')->nullable();
            $table->integer('percentage_discount')->default(0);
            $table->integer('direct_discount')->default(0);

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
        Schema::dropIfExists('discount_codes');
    }
}
