<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('numero_ticket', 50)->nullable()->after('cliente_id');
            $table->string('metodo_pago', 30)->default('efectivo')->after('numero_ticket');
            $table->float('efectivo_recibido')->default(0)->after('total_venta');
            $table->float('cambio')->default(0)->after('efectivo_recibido');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['numero_ticket', 'metodo_pago', 'efectivo_recibido', 'cambio']);
        });
    }
};
