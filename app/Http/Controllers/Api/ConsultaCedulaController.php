<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultaResult;
use Illuminate\Http\JsonResponse;

class ConsultaCedulaController extends Controller
{
    /**
     * Retorna el historial completo de consultas de un afiliado por cédula,
     * ordenado del más reciente al más antiguo.
     *
     * GET /api/consulta/cedula/{cedula}
     */
    public function show(string $cedula): JsonResponse
    {
        $resultados = ConsultaResult::where('cedula', $cedula)
            ->where('encontrado', true)
            ->latest('updated_at')
            ->get();

        if ($resultados->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron resultados para la cédula proporcionada.',
                'data'    => null,
            ], 404);
        }

        $data = $resultados->map(fn (ConsultaResult $r) => [
            'cedula'            => $r->cedula,
            'tipo_documento'    => $r->tipo_documento,
            'primer_nombre'     => $r->primer_nombre,
            'segundo_nombre'    => $r->segundo_nombre,
            'primer_apellido'   => $r->primer_apellido,
            'segundo_apellido'  => $r->segundo_apellido,
            'departamento'      => $r->departamento,
            'municipio'         => $r->municipio,
            'direccion'         => $r->direccion,
            'regimen'           => $r->regimen,
            'estado_afiliado'   => $r->estado_afiliado,
            'sede'              => $r->sede,
            'ips'               => $r->ips,
            'celular'           => $r->celular,
            'telefono_fijo'     => $r->telefono_fijo,
            'correo'            => $r->correo,
            'poblacion_especial'=> $r->poblacion_especial,
            'grupo_etnico'      => $r->grupo_etnico,
            'eps_nombre'        => $r->eps_nombre,
            'fecha_nacimiento'  => $r->fecha_nacimiento,
            'edad'              => $r->edad,
            'sexo'              => $r->sexo,
            'consultado_en'     => $r->updated_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Consulta exitosa.',
            'total'   => $data->count(),
            'data'    => $data,
        ]);
    }
}
