<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'stock',
        'stock_minimo',
        'precio_compra',
        'precio_venta',
        'precio_venta_con_isv',
        'isv',
        'precio_mayorista',
        'cantidad_mayorista',
        'ganancia_unidad',
        'vendidos',
        'imagen',
        'categoria',
        'marca',
        'estado',
        'propietario_id',
        'created_by',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'vendidos' => 'integer',
        'precio_compra' => 'float',
        'precio_venta' => 'float',
        'precio_venta_con_isv' => 'float',
        'ganancia_unidad' => 'float',
        'isv' => 'float',
    ];

    // 🔁 Relaciones

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'propietario_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 🔎 Scope útil para filtrar productos activos
    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }
}
