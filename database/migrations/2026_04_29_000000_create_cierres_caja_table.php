<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('fondo_inicial', 10, 2)->default(0);
            $table->decimal('total_ventas', 10, 2)->default(0);
            $table->decimal('total_efectivo', 10, 2)->default(0);
            $table->decimal('total_tarjeta', 10, 2)->default(0);
            $table->decimal('total_transferencia', 10, 2)->default(0);
            $table->decimal('total_yape', 10, 2)->default(0);
            $table->decimal('total_plin', 10, 2)->default(0);
            $table->decimal('total_otros', 10, 2)->default(0);
            $table->unsignedInteger('cantidad_ventas')->default(0);
            $table->decimal('efectivo_contado', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->string('estado', 16)->default('abierto');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'estado']);
            $table->index('fecha_apertura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_caja');
    }
};
