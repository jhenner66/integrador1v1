<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedidos';
    protected $primaryKey = 'id_detalle';

    protected $fillable = ['id_pedido', 'id_producto', 'cantidad', 'precio_unitario'];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function getSubtotalAttribute(): float
    {
        return round($this->cantidad * $this->precio_unitario, 2);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
