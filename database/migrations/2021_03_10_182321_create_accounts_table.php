<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->integer('cost');
            $table->integer('status_code');
            $table->string('description')->nullable();
            $table->integer('account_type_id');
            $table->bigInteger('censor_id')->nullable();
            $table->bigInteger('buyer_id')->nullable();
            $table->integer('sold_at_price')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->bigInteger('last_updated_editor_id');
            $table->bigInteger('creator_id');
            $table->string('last_role_key_editor_used');
            $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('accounts');
    }
}
