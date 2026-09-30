<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id_detalle';

    protected $fillable = ['id_venta', 'id_producto', 'cantidad', 'precio_unitario'];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function getSubtotalAttribute(): float
    {
        return round($this->cantidad * $this->precio_unitario, 2);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
