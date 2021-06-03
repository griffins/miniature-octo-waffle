<?php

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
        Schema::create('coupons', function(Blueprint $table){
            $table->increments('id');
            $table->string('code');
            $table->string('description');
            $table->decimal('amount','12',2);
            $table->enum('status',['active','in-active'])
                ->default('active');
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('radius')->nullable();
            $table->timestamp('validFrom')->nullable();
            $table->timestamp('validTo')->nullable();
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
        Schema::dropIfExists('coupons');
    }
}
