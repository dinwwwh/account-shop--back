<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRechargePhonecardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recharge_phonecards', function (Blueprint $table) {
            $table->id();
            $table->string('serial');
            $table->string('code');
            $table->string('telco');
            $table->integer('face_value');
            $table->integer('real_face_value')->nullable();
            $table->integer('received_value')->nullable();
            $table->unsignedTinyInteger('status');

            $table->unsignedTinyInteger('port');
            $table->json('data')->nullable();

            $table->foreignIdFor(User::class, 'approver_id')->nullable();
            $table->foreignIdFor(User::class, 'creator_id')->nullable();
            $table->foreignIdFor(User::class, 'latest_updater_id')->nullable();

            $table->timestamp('paid_at')->nullable();
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
        Schema::dropIfExists('recharge_phonecards');
    }
}
