<?php

namespace App\Http\Controllers;

use App\Services\EmssanarService;
use Illuminate\Http\Request;

class EmssanarCredentialController extends Controller
{
    public function index()
    {
        return view('emssanar.credentials', [
            'apiUrl' => session('emssanar_api_url', config('emssanar.api_url')),
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'api_url' => ['required', 'url', 'max:500'],
        ]);

        session(['emssanar_api_url' => rtrim($request->api_url, '/')]);

        return response()->json(['success' => true]);
    }

    public function test(Request $request)
    {
        $url = session('emssanar_api_url', config('emssanar.api_url'));

        try {
            $service = new EmssanarService();
            // Usar cédula y fecha ficticias — si la API responde JSON (aunque sea error), la URL es válida
            $result = $service->consultarAfiliado('000000000', '01/01/2000');

            if (array_key_exists('error', $result) || array_key_exists('success', $result) || array_key_exists('data', $result)) {
                return response()->json([
                    'success' => true,
                    'message' => 'La URL responde correctamente: ' . $url,
                    'url'     => $url,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'La URL respondió con formato inesperado.',
                'url'     => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar: ' . $e->getMessage(),
                'url'     => $url,
            ]);
        }
    }

    public function reset(Request $request)
    {
        session()->forget('emssanar_api_url');

        return response()->json(['success' => true]);
    }
}
