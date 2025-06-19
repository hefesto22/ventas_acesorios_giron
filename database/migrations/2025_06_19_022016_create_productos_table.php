<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('productos', function (Blueprint $table) {
        $table->id();

        $table->string('nombre')->unique();
        $table->text('descripcion')->nullable();

        $table->integer('stock')->default(0);
        $table->integer('stock_minimo')->default(0);

        $table->decimal('precio_compra', 10, 2);
        $table->decimal('precio_venta', 10, 2);
        $table->decimal('precio_venta_con_isv', 10, 2);
        $table->decimal('isv', 5, 2)->default(0);

        $table->decimal('ganancia_unidad', 10, 2)->nullable();
        $table->integer('vendidos')->default(0);

        $table->decimal('precio_mayorista', 10, 2)->nullable(); // NUEVO
        $table->integer('cantidad_mayorista')->nullable(); // NUEVO

        $table->string('imagen')->nullable();

        $table->string('categoria')->nullable();
        $table->string('marca')->nullable();

        $table->boolean('estado')->default(true);

        $table->foreignId('propietario_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps();
    });
}


    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
