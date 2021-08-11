<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponnablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('couponnables', function (Blueprint $table) {
            $table->foreignUuid('coupon_code')
                ->references('code')
                ->on('coupons')
                ->onDelete('cascade');

            $table->morphs('couponnable');
            $table->unsignedSmallInteger('type')->nullable();

            $table->timestamps();

            $table->primary(['coupon_code', 'couponnable_id', 'couponnable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('couponnables');
    }
}
