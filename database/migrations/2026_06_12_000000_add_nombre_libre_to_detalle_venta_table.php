<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->string('nombre_libre', 200)->nullable()->after('notas');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->dropColumn('nombre_libre');
        });
    }
};
