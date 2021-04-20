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
            $table->integer('price')->default(0);
            $table->boolean('buyable')->default(false);
            $table->string('name')->nullable();
            $table->string('description')->nullable();

            $table->integer('maximum_price')->nullable();
            $table->integer('minimum_price')->nullable();
            $table->integer('maximum_discount')->nullable();
            $table->integer('minimum_discount')->nullable();
            $table->integer('percentage_discount')->default(0);
            $table->integer('direct_discount')->default(0);
            $table->timestamp('usable_at')->nullable();
            $table->timestamp('usable_closed_at')->nullable();
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('offer_closed_at')->nullable();

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
