<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\File;
use App\Models\Peticione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PeticioneController extends Controller
{
    private function sendResponse($data, $message, $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    private function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function index()
    {
        try {
            $peticiones = Peticione::with(['user', 'categoria', 'files'])->get();
            return $this->sendResponse($peticiones, 'Peticiones recuperadas con éxito');
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar peticiones', $e->getMessage(), 500);
        }
    }

    public function listMine()
    {
        try {
            $user = Auth::user();
            $peticiones = Peticione::where('user_id', $user->id)
                ->with(['user', 'categoria', 'files'])
                ->get();

            return $this->sendResponse($peticiones, 'Tus peticiones recuperadas con éxito');
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar tus peticiones', $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $peticion = Peticione::with(['user', 'categoria', 'files'])->findOrFail($id);
            return $this->sendResponse($peticion, 'Petición encontrada');
        } catch (\Exception $e) {
            return $this->sendError('Petición no encontrada', [], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|max:255',
            'descripcion' => 'required',
            'destinatario' => 'required',
            'categoria_id' => 'required|exists:categorias,id',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error de validación', $validator->errors(), 422);
        }

        try {
            $file = $request->file('file');
            $path = $file->store('peticiones', 'public');

            $peticion = new Peticione($request->all());
            $peticion->user_id = Auth::id();
            $peticion->firmantes = 0;
            $peticion->estado = 'pendiente';
            $peticion->save();

            $peticion->files()->create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path

            ]);

            return $this->sendResponse($peticion->load('files'), 'Petición creada con éxito', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error al crear la petición', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);

            if ($request->user()->cannot('update', $peticion)) {
                return $this->sendError('No autorizado', [], 403);
            }

            $peticion->update($request->all());
            return $this->sendResponse($peticion, 'Petición actualizada con éxito');
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar', $e->getMessage(), 500);
        }
    }

    public function firmar(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $user = Auth::user();

            if ($peticion->firmas()->where('user_id', $user->id)->exists()) {
                return $this->sendError('Ya has firmado esta petición', [], 403);
            }

            $peticion->firmas()->attach($user->id);
            $peticion->increment('firmantes');

            return $this->sendResponse($peticion, 'Petición firmada con éxito', 201);
        } catch (\Exception $e) {
            return $this->sendError('No se pudo firmar la petición', $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);

            if ($request->user()->cannot('delete', $peticion)) {
                return $this->sendError('No autorizado', [], 403);
            }

            foreach ($peticion->files as $file) {
                Storage::disk('public')->delete($file->file_path);
            }

            $peticion->delete();

            return $this->sendResponse(null, 'Petición eliminada con éxito');
        } catch (\Exception $e) {
            return $this->sendError('Error al eliminar', $e->getMessage(), 500);
        }
    }
}
