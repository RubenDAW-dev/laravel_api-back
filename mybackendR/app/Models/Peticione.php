<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peticione extends Model
{
    protected $fillable = [
        'titulo',
        'descripcion',
        'destinatario',
        'firmantes',
        'estado',
        'user_id',
        'categoria_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function firmas()
    {
        return $this->belongsToMany(User::class, 'peticione_user');
    }
}
