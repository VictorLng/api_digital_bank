<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('customer_accounts');
            $table->string('type'); // deposit, withdrawal, transfer_in, transfer_out, account_opening
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->string('reference_id')->nullable()->index(); // Para agrupar transações relacionadas
            $table->foreignId('related_account_id')->nullable()->constrained('customer_accounts');
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
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
        Schema::dropIfExists('transactions');
    }
}
