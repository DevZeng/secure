<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->commit('User Id');
            $table->unsignedInteger('category_id')->commit('category_id');
            $table->string('notify_id')->commit('notify_id');
            $table->string('name')->commit('StoreName');
            $table->string('manager')->commit('StoreManager');
            $table->string('phone')->unique()->commit();
            $table->float('lat',10,6)->default(0)->commit('latitude');
            $table->float('lon',10,6)->default(0)->commit('longitude');
            $table->string('address')->commit('address');
            $table->string('document')->commit('document');
            $table->tinyInteger('state')->default(0)->commit('State');
            $table->softDeletes();
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
        Schema::dropIfExists('stores');
    }
}
