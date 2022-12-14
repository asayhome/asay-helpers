<?php

use AsayHome\AsayHelpers\Models\UserModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsayPaymentsOperationsTable extends Migration
{
    public function up()
    {
        Schema::create('asay_payments_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(UserModel::class, 'user_id');
            $table->foreignIdFor(UserModel::class, 'created_by');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('operation');
            $table->string('operation_id')->nullable();
            $table->integer('type');
            $table->integer('reason');
            $table->double('amount');
            $table->string('reference')->nullable();
            $table->text('details')->nullable();
            $table->string('status')->nullable();
            $table->string('gateway')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asay_payments_operations');
    }
}
