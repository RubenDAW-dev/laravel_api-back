<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse
    {
        $cats = Categoria::query()
            ->select(['id', 'nombre'])   // ajusta a tu esquema real
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $cats,
            'message' => 'Categorías recuperadas con éxito'
        ]);
    }
}