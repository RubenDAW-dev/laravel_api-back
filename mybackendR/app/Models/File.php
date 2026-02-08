<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'peticione_id'
    ];

    public function peticion()
    {
        return $this->belongsTo(Peticione::class, 'peticione_id');
    }
}
