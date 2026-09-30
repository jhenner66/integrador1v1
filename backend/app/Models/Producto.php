<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'id_categoria', 'codigo', 'codigo_barras', 'nombre', 'descripcion',
        'precio', 'stock', 'stock_minimo', 'fecha_vencimiento', 'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_vencimiento' => 'date',
    ];

    protected $appends = ['stock_bajo'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
    }

    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'id_producto', 'id_producto');
    }

    // RF08: indicador de stock bajo (usado por el dashboard/alertas)
    public function getStockBajoAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    // RF09: producto próximo a vencer (30 días)
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias));
    }

    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo');
    }
}
