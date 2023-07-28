<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer("montant");
            $table->foreignId("expediteur_id")->constrained("clients");
            $table->foreignId("distinataire_id")->constrained("clients");
            $table->dateTime("date");
            $table->enum("type", ["depot", "retrait", "transfert sans code", "transfert avec code", "transfert immediat"]);
            $table->string("code")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
