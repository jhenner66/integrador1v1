<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';

    protected $fillable = ['nombre', 'contacto', 'telefono', 'email', 'direccion'];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_proveedor', 'id_proveedor');
    }
}
