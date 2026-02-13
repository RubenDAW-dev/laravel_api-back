<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private function sendResponse($data, $message = 'OK', $code = 200)
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message
        ], $code);
    }

    private function sendError($error, $errorMessages = [], $code = 400)
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

    /**
     * POST /api/register
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'min:6'],
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return $this->sendResponse(['user' => $user], 'Usuario registrado. Inicia sesión.', 201);
        } catch (ValidationException $e) {
            // 422
            return $this->sendError('Error de validación', $e->errors(), 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // 409: conflicto por email duplicado en bases con constraint
            if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
                return $this->sendError('Este correo electrónico ya está registrado.', [], 409);
            }
            return $this->sendError('Error al registrar', $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->sendError('Error al registrar', $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            // credenciales incorrectas → 401 (para que Angular pinte el mensaje)
            return $this->sendError('Credenciales inválidas', [], 401);
        }

        $user = auth('api')->user();

        return response()->json([
            'success'      => true,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // en segundos (si configuraste TTL)
            'user'         => $user
        ]);
    }

    /**
     * GET /api/me (auth:api)
     */
    public function me()
    {
        return $this->sendResponse(auth('api')->user(), 'Perfil');
    }

    /**
     * POST /api/logout (auth:api)
     */
    public function logout()
    {
        try {
            auth('api')->logout(); // invalida el token (si usas blacklist)
        } catch (\Exception $e) {
            // si no hay blacklist, ignorar
        }

        return $this->sendResponse(null, 'Sesión cerrada');
    }

    /**
     * POST /api/refresh (auth:api)
     * Requiere un token válido (aunque esté casi caducado).
     */
    public function refresh()
    {
        try {
            $newToken = auth('api')->refresh();
            return response()->json([
                'success'      => true,
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            // Si falla el refresh, el front debe hacer logout
            return $this->sendError('No se pudo refrescar el token', [], 401);
        }
    }
}