@extends('layouts.app')
@section('title', 'Buscar por Cédula')
@section('content')
<div class="card">
    <h2>🔍 Buscar Afiliado por Cédula</h2>
    <label>Número de cédula</label>
    <form method="GET" action="/consultas/search" class="flex gap-2 items-center">
        <input type="text" name="cedula" value="{{ $cedula ?? '' }}" placeholder="Ingresa el número de cédula..." style="flex:1" autofocus>
        <button type="submit" class="btn btn-primary">🔍 Buscar</button>
    </form>
</div>

@if(isset($results))
<div class="card">
    @if($results->isEmpty())
        <p style="color:#888">No se encontraron registros para cédula {{ $cedula }}.</p>
    @else
        <p style="color:#9ca3af;font-size:0.9rem;margin-bottom:1.2rem">
            Se encontraron <span style="color:#c4b5fd;font-weight:600">{{ $results->total() }}</span> resultado(s) para cédula <span style="color:#e0e0e0;font-weight:600">{{ $cedula }}</span>
        </p>

        @foreach($results as $r)
        <div class="afiliado-card">
            <div class="card-header">
                <div>
                    <span class="name">{{ $r->primer_nombre }} {{ $r->segundo_nombre }} {{ $r->primer_apellido }} {{ $r->segundo_apellido }}</span>
                    <span class="doc">CC {{ $r->cedula }}</span>
                </div>
                @if($r->encontrado)
                    <span class="badge badge-completed">Encontrado</span>
                @elseif($r->error)
                    <span class="badge badge-paused">Error</span>
                @else
                    <span class="badge badge-paused">No encontrado</span>
                @endif
            </div>
            <div class="info-grid">
                <div class="info-item">EPS: <span>EMSSANAR EPS-S</span></div>
                <div class="info-item">Régimen: <span>{{ $r->regimen ?: '—' }}</span></div>
                <div class="info-item">Departamento: <span>{{ $r->departamento ?: '—' }}</span></div>
                <div class="info-item">Municipio: <span>{{ $r->municipio ?: '—' }}</span></div>
                <div class="info-item">Dirección: <span>{{ $r->direccion ?: '—' }}</span></div>
                <div class="info-item">Barrio: <span>{{ $r->barrio ?: '—' }}</span></div>
                <div class="info-item">Celular: <span>{{ $r->celular ?: '—' }}</span></div>
                <div class="info-item">Teléfono: <span>{{ $r->telefono_fijo ?: '—' }}</span></div>
                <div class="info-item">Correo: <span>{{ $r->correo ?: '—' }}</span></div>
                <div class="info-item">Fecha Nac.: <span>{{ $r->fecha_nacimiento ?: '—' }}</span></div>
                <div class="info-item">Sexo: <span>{{ $r->sexo === 'M' ? 'Masculino' : ($r->sexo === 'F' ? 'Femenino' : ($r->sexo ?: '—')) }}</span></div>
                <div class="info-item">Nivel Sisben: <span>{{ $r->nivel_sisben ?: '—' }}</span></div>
                <div class="info-item">Grupo Étnico: <span>{{ $r->grupo_etnico ?: '—' }}</span></div>
                <div class="info-item">Población: <span>{{ $r->poblacion_especial ?: '—' }}</span></div>
                <div class="info-item">Estado: <span>{{ $r->estado_afiliado ?: '—' }}</span></div>
                <div class="info-item">Sede: <span>{{ $r->sede ?: '—' }}</span></div>
                <div class="info-item">IPS: <span>{{ $r->ips ?: '—' }}</span></div>
                <div class="info-item">Consulta: <span><a href="/consultas/{{ $r->consulta_id }}" style="color:#a5b4fc">#{{ $r->consulta_id }}</a> — {{ $r->created_at->format('d/m/Y') }}</span></div>
            </div>
            @if($r->error)
                <div style="margin-top:0.8rem;padding:0.5rem 0.8rem;background:rgba(220,38,38,0.1);border-radius:6px;font-size:0.8rem;color:#fca5a5">
                    Error: {{ $r->error }}
                </div>
            @endif
        </div>
        @endforeach

        <div class="pagination mt-2">{{ $results->appends(['cedula' => $cedula])->links('pagination') }}</div>
    @endif
</div>
@endif
@endsection
