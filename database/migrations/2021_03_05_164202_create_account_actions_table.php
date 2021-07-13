<?php

use App\Models\AccountType;
use App\Models\Rule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('video_path')->nullable();
            $table->foreignIdFor(AccountType::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Rule::class)->constrained();
            $table->unsignedBigInteger('latest_updater_id')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
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
        Schema::dropIfExists('account_actions');
    }
}
