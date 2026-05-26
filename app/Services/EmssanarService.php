<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class EmssanarService
{
    /**
     * Normaliza cualquier formato de fecha a yyyy-mm-dd.
     * Soporta: dd/mm/aaaa, aaaa/mm/dd, dd-mm-aaaa, aaaa-mm-dd,
     * dd.mm.aaaa, números seriales de Excel, y variantes con 2 dígitos de año.
     */
    public function normalizarFecha(string $fecha): string
    {
        $fecha = trim($fecha);

        if (empty($fecha)) {
            return '';
        }

        // Si es un número serial de Excel (ej: 44927, 25720)
        if (is_numeric($fecha) && (int)$fecha > 1000) {
            try {
                // Excel serial: días desde 1899-12-30
                $carbon = Carbon::createFromFormat('Y-m-d', '1899-12-30')->addDays((int)$fecha);
                return $carbon->format('Y-m-d');
            } catch (\Exception $e) {
                // Si falla, seguir intentando otros formatos
            }
        }

        // Reemplazar cualquier separador (/, -, .) por -
        $normalized = preg_replace('/[\/\.\-]/', '-', $fecha);
        $parts = explode('-', $normalized);

        if (count($parts) !== 3) {
            // Intentar parseo genérico con Carbon
            try {
                return Carbon::parse($fecha)->format('Y-m-d');
            } catch (\Exception $e) {
                return $fecha;
            }
        }

        $p0 = trim($parts[0]);
        $p1 = trim($parts[1]);
        $p2 = trim($parts[2]);

        // Determinar el formato basado en la longitud de las partes
        // Si el primer segmento tiene 4 dígitos: aaaa-mm-dd
        if (strlen($p0) === 4) {
            $year = $p0;
            $month = str_pad($p1, 2, '0', STR_PAD_LEFT);
            $day = str_pad($p2, 2, '0', STR_PAD_LEFT);
        }
        // Si el tercer segmento tiene 4 dígitos: dd-mm-aaaa
        elseif (strlen($p2) === 4) {
            $day = str_pad($p0, 2, '0', STR_PAD_LEFT);
            $month = str_pad($p1, 2, '0', STR_PAD_LEFT);
            $year = $p2;
        }
        // Si el tercer segmento tiene 2 dígitos: dd-mm-aa
        elseif (strlen($p2) === 2) {
            $day = str_pad($p0, 2, '0', STR_PAD_LEFT);
            $month = str_pad($p1, 2, '0', STR_PAD_LEFT);
            $yearNum = (int)$p2;
            $year = $yearNum > 50 ? '19' . str_pad($p2, 2, '0', STR_PAD_LEFT)
                                  : '20' . str_pad($p2, 2, '0', STR_PAD_LEFT);
        }
        // Fallback: asumir dd-mm-aaaa
        else {
            $day = str_pad($p0, 2, '0', STR_PAD_LEFT);
            $month = str_pad($p1, 2, '0', STR_PAD_LEFT);
            $year = $p2;
        }

        // Validar que mes y día sean razonables, si no, intentar invertir
        if ((int)$month > 12 && (int)$day <= 12) {
            [$month, $day] = [$day, $month];
        }

        $result = "{$year}-{$month}-{$day}";

        // Validar que la fecha sea real
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            return ''; // Fecha inválida
        }

        return $result;
    }

    public function consultarAfiliado(string $cedula, string $fechaNacimiento): array
    {
        $fechaApi = $this->normalizarFecha($fechaNacimiento);

        if (empty($fechaApi)) {
            return ['error' => 'Fecha de nacimiento inválida: ' . $fechaNacimiento];
        }        $apiUrl = session('emssanar_api_url', config('emssanar.api_url'));
        $baseUrl = preg_replace('#/api/.*$#', '', $apiUrl);

        $payload = [
            'tipoDocumento' => [
                'id' => '666c9e0f71433d6611f1cfdc',
                'descripcion' => 'Cedula de ciudadania',
                'fechaInsert' => '2024-06-13',
                'fechaBaja' => null,
                'codigo' => 'CC',
            ],
            'numeroDocumento' => $cedula,
            'fechaNacimiento' => $fechaApi,
        ];

        \Log::info('Emssanar API Request', ['cedula' => $cedula, 'fecha_original' => $fechaNacimiento, 'fecha_normalizada' => $fechaApi, 'payload' => $payload]);

        try {
            $response = Http::timeout(config('emssanar.timeout'))
                ->withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                    'Origin' => $baseUrl,
                    'Referer' => $baseUrl . '/',
                ])
                ->post($apiUrl, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Emssanar connection error', ['error' => $e->getMessage()]);
            return ['connection_error' => true, 'error' => 'Error de conexión: ' . $e->getMessage()];
        } catch (\Exception $e) {
            \Log::error('Emssanar exception', ['error' => $e->getMessage()]);
            return ['connection_error' => true, 'error' => 'Error: ' . $e->getMessage()];
        }

        \Log::info('Emssanar API Response', ['status' => $response->status(), 'body' => $response->body()]);

        if (!$response->successful()) {
            $body = $response->json();
            $msg = $body['message'] ?? $body['error'] ?? ('Error HTTP: ' . $response->status());
            return ['error' => $msg . ' (HTTP ' . $response->status() . ')'];
        }

        $data = $response->json();

        if (empty($data) || (isset($data['existe']) && !$data['existe'])) {
            return [
                'encontrado' => false,
                'estado_afiliado' => $data['mensaje'] ?? 'No encontrado',
            ];
        }

        return $this->parseResponse($data);
    }

    protected function parseResponse(array $data): array
    {
        $primerNombre = $data['primerNombre'] ?? null;
        $segundoNombre = $data['segundoNombre'] ?? null;
        $primerApellido = $data['primerApellido'] ?? null;
        $segundoApellido = $data['segundoApellido'] ?? null;

        if ($primerNombre && !$primerApellido) {
            $parts = explode(' ', trim($primerNombre));
            if (count($parts) >= 4) {
                $primerNombre = $parts[0];
                $segundoNombre = $parts[1];
                $primerApellido = $parts[2];
                $segundoApellido = implode(' ', array_slice($parts, 3));
            } elseif (count($parts) === 3) {
                $primerNombre = $parts[0];
                $segundoNombre = null;
                $primerApellido = $parts[1];
                $segundoApellido = $parts[2];
            }
        }

        return [
            'encontrado' => true,
            'tipo_documento' => 'CC',
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'departamento' => $data['departamentoResidenciaOrigen'] ?? null,
            'municipio' => $data['municipioResidenciaOrigen'] ?? null,
            'direccion' => $data['direccion'] ?? $data['direccionResidencia'] ?? null,
            'regimen' => $data['plan'] ?? null,
            'poblacion_especial' => $data['grupoPoblacional'] ?? null,
            'grupo_etnico' => $data['origenEtnico'] ?? null,
            'paciente_riesgo' => null,
            'otros_riesgos' => null,
            'celular' => $data['celular'] ?? null,
            'telefono_fijo' => null,
            'correo' => $data['correo'] ?? $data['correoElectronico'] ?? null,
            'estado_afiliado' => $data['mensaje'] ?? 'Afiliado encontrado',
            'sede' => null,
            'ips' => null,
            'sexo' => $data['sexo'] ?? null,
            'nivel_sisben' => $data['nivelSisben'] ?? null,
            'barrio' => $data['barrio'] ?? null,
        ];
    }
}
