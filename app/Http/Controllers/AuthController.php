<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer"
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Autenticación"},
     *     summary="Registrar un nuevo usuario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Piero Llanos"),
     *             @OA\Property(property="email", type="string", format="email", example="pierodanielllanossanchez@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registrado correctamente"),
     *     @OA\Response(response=422, description="Error en la validación"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ], [
                'name.regex' => 'El nombre solo puede contener letras y espacios.',
                'email.email' => 'El correo electrónico debe tener un formato válido.',
                'email.unique' => 'El correo electrónico ya está registrado.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json(['message' => 'Registrado correctamente'], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = collect($e->errors())->flatten()->first();
            return response()->json(['error' => $errorMessage], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el registro: ' . $e->getMessage()], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Autenticación"},
     *     summary="Iniciar sesión y obtener token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="pierodanielllanossanchez@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token de acceso generado"),
     *     @OA\Response(response=401, description="Credenciales incorrectas"),
     *     @OA\Response(response=422, description="Error en la validación")
     * )
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.email' => 'El correo electrónico debe tener un formato válido.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'password.required' => 'La contraseña es obligatoria.'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Credenciales incorrectas'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = collect($e->errors())->flatten()->first();
            return response()->json(['error' => $errorMessage], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el inicio de sesión: ' .  $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Autenticación"},
     *     summary="Cerrar sesión y revocar token",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Sesión cerrada correctamente"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}