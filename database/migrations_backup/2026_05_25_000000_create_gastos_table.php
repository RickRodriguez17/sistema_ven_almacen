<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('cierre_caja_id')->nullable()
                ->constrained('cierres_caja')->nullOnDelete();
            $table->string('concepto', 255);
            $table->decimal('monto', 10, 2);
            $table->timestamps();

            $table->index('cierre_caja_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
