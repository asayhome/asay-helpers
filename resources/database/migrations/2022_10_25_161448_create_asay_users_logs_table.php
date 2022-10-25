<?php

use AsayHome\AsayHelpers\Models\UserModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asay_users_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(UserModel::class, 'user_id');
            $table->integer('operation');
            $table->string('device_type')->nullable();
            $table->string('os_type')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('location')->nullable();
            $table->string('ip_address')->nullable();
            $table->longText('notes')->nullable();
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
        Schema::dropIfExists('asay_users_logs');
    }
};
