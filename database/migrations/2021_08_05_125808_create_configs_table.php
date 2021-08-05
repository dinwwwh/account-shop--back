<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->json('data')->nullable();
            $table->json('rules_of_data')->default(json_encode([]));
            $table->string('structure_description')->nullable();
            $table->string('description')->nullable();
            $table->boolean('public')->default(false);

            $table->foreignIdFor(User::class, 'creator_id')->nullable();
            $table->foreignIdFor(User::class, 'latest_updater_id')->nullable();

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
        Schema::dropIfExists('configs');
    }
}
