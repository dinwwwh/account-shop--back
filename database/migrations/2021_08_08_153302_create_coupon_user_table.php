<?php

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_user', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->constrained('users')->onDelete('cascade');
            $table->foreignIdFor(Coupon::class)->constrained('coupons')->onDelete('cascade');

            $table->unsignedSmallInteger('amount');
            $table->unsignedSmallInteger('used_amount')->default(0);

            $table->timestamps();

            $table->primary(['coupon_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_user');
    }
}
