<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Dados básicos
            $table->string('name');
            $table->char('cpf', 11);
            $table->string('phone', 20);
            $table->char('cep', 8);
            $table->string('street');       
            $table->string('number', 10);
            $table->string('complement')->nullable(); 
            $table->string('district');      
            $table->string('city');
            $table->char('state', 2);          
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            $table->timestamps();

            // Regra: CPF não pode repetir por usuário
            $table->unique(['user_id', 'cpf']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
