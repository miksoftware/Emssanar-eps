<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaResult extends Model
{
    protected $fillable = [
        'consulta_id', 'cedula', 'fecha_nacimiento', 'tipo_documento',
        'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido',
        'departamento', 'municipio', 'direccion', 'regimen',
        'poblacion_especial', 'grupo_etnico', 'paciente_riesgo', 'otros_riesgos',
        'celular', 'telefono_fijo', 'correo', 'estado_afiliado',
        'sede', 'ips', 'sexo', 'nivel_sisben', 'barrio',
        'encontrado', 'processed', 'error',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}
