<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('code')->primary();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('used_amount')->default(0);

            $table->integer('maximum_value')->nullable();
            $table->integer('minimum_value')->nullable();
            $table->integer('maximum_discount')->nullable();
            $table->integer('minimum_discount')->nullable();
            $table->integer('percentage_discount')->default(0);
            $table->integer('direct_discount')->default(0);
            $table->timestamp('usable_at')->nullable();
            $table->timestamp('usable_closed_at')->nullable();

            $table->integer('price')->nullable();
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('offer_closed_at')->nullable();

            $table->foreignIdFor(User::class, 'creator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignIdFor(User::class, 'latest_updater_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
