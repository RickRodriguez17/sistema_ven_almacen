<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->foreignId('combo_id')->nullable()->after('producto_id')->constrained('combos')->nullOnDelete();
            $table->unsignedInteger('cantidad_combos')->nullable()->after('combo_id');
            $table->text('notas')->nullable()->after('subtotal');
        });

        // Hacer producto_id nullable para permitir filas que representen un combo en lugar de un producto.
        // En MySQL/MariaDB hay que eliminar primero la FK para poder modificar la columna y volver a crearla.
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
        });

        DB::statement('ALTER TABLE detalle_venta MODIFY producto_id BIGINT UNSIGNED NULL');

        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->foreign('producto_id')
                ->references('id')
                ->on('productos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->dropForeign(['combo_id']);
            $table->dropColumn(['combo_id', 'cantidad_combos', 'notas']);
        });
    }
};
