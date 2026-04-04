<?php

namespace App\Exports;

use App\Models\Consulta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(protected Consulta $consulta) {}

    public function headings(): array
    {
        return [
            'Cédula', 'Tipo Doc', 'Primer Nombre', 'Segundo Nombre',
            'Primer Apellido', 'Segundo Apellido', 'Departamento', 'Municipio',
            'Dirección', 'Régimen', 'Población Especial', 'Grupo Étnico',
            'Paciente Riesgo', 'Otros Riesgos', 'Celular', 'Teléfono Fijo',
            'Correo', 'Estado Afiliado', 'Sede', 'IPS', 'Sexo',
            'Nivel Sisben', 'Barrio', 'Encontrado',
        ];
    }

    public function collection()
    {
        return $this->consulta->results->map(fn($r) => [
            $r->cedula, $r->tipo_documento, $r->primer_nombre, $r->segundo_nombre,
            $r->primer_apellido, $r->segundo_apellido, $r->departamento, $r->municipio,
            $r->direccion, $r->regimen, $r->poblacion_especial, $r->grupo_etnico,
            $r->paciente_riesgo, $r->otros_riesgos, $r->celular, $r->telefono_fijo,
            $r->correo, $r->estado_afiliado, $r->sede, $r->ips, $r->sexo,
            $r->nivel_sisben, $r->barrio, $r->encontrado ? 'Sí' : 'No',
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2D6A4F']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 10, 'C' => 18, 'D' => 18,
            'E' => 18, 'F' => 18, 'G' => 20, 'H' => 20,
            'I' => 30, 'J' => 10, 'K' => 22, 'L' => 18,
            'M' => 18, 'N' => 18, 'O' => 15, 'P' => 15,
            'Q' => 30, 'R' => 20, 'S' => 15, 'T' => 15,
            'U' => 8, 'V' => 15, 'W' => 20, 'X' => 12,
        ];
    }
}
