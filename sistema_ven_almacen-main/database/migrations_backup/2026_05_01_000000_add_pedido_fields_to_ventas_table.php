<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('tipo_pedido', 20)->default('llevar')->after('cliente_id');
            $table->string('mesa', 30)->nullable()->after('tipo_pedido');
            $table->text('direccion_delivery')->nullable()->after('mesa');
            $table->string('estado', 20)->default('pagada')->after('direccion_delivery');
            $table->text('motivo_anulacion')->nullable()->after('estado');
            $table->timestamp('anulada_at')->nullable()->after('motivo_anulacion');
            $table->foreignId('anulada_por_user_id')->nullable()->after('anulada_at')->constrained('users')->nullOnDelete();
            $table->text('notas')->nullable()->after('anulada_por_user_id');
            $table->string('nombre_cliente_libre', 120)->nullable()->after('notas');
            $table->index('estado');
            $table->index('tipo_pedido');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['anulada_por_user_id']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['tipo_pedido']);
            $table->dropColumn([
                'tipo_pedido', 'mesa', 'direccion_delivery', 'estado',
                'motivo_anulacion', 'anulada_at', 'anulada_por_user_id', 'notas',
                'nombre_cliente_libre',
            ]);
        });
    }
};
