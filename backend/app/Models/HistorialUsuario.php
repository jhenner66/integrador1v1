<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialUsuario extends Model
{
    public $timestamps = false;

    protected $table = 'historial_usuario';
    protected $primaryKey = 'id_historial';

    protected $fillable = ['id_usuario', 'accion', 'detalle', 'fecha'];

    protected $casts = ['fecha' => 'datetime'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
