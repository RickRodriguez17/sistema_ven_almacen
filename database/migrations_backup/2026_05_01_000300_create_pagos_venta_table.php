<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->string('metodo_pago', 30);
            $table->float('monto');
            $table->float('efectivo_recibido')->default(0);
            $table->float('cambio')->default(0);
            $table->string('referencia', 100)->nullable();
            $table->timestamps();
            $table->index('venta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_venta');
    }
};
