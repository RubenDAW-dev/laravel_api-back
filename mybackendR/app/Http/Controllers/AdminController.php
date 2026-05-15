<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peticione;

class AdminController extends Controller
{
    public function indexPeticiones()
    {
        $peticiones = Peticione::with(['categoria', 'user', 'files'])
            ->withCount('firmas')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $peticiones
        ], 200);
    }

    public function showPeticion($id)
    {
        $peticion = Peticione::with(['categoria', 'user', 'files'])
            ->withCount('firmas')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $peticion], 200);
    }

    public function updatePeticion(Request $request, $id)
    {
        $peticion = Peticione::findOrFail($id);

        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'destinatario'=> 'required|string|max:255',
            'categoria_id'=> 'required|exists:categorias,id',
            'estado'      => 'required|in:pendiente,aceptada,rechazada',
        ]);

        $peticion->titulo       = $request->titulo;
        $peticion->descripcion  = $request->descripcion;
        $peticion->destinatario = $request->destinatario;
        $peticion->categoria_id = $request->categoria_id;
        $peticion->estado       = $request->estado;
        $peticion->save();

        return response()->json(['success' => true, 'data' => $peticion], 200);
    }

    public function destroyPeticion($id)
    {
        $peticion = Peticione::findOrFail($id);
        $peticion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Petición eliminada correctamente por el administrador.'
        ], 200);
    }
}
