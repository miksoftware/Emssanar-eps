@extends('layouts.app')
@section('title', 'Consulta #' . $consulta->id)
@section('content')
<div class="card">
    <div class="flex justify-between items-center">
        <div>
            <h2>Consulta #{{ $consulta->id }}</h2>
            <p style="color:#888;font-size:0.85rem">
                {{ $consulta->filename }} — {{ $consulta->processed }}/{{ $consulta->total_records }} procesados
                — <span class="badge badge-{{ $consulta->status }}">{{ ucfirst($consulta->status) }}</span>
            </p>
        </div>
        <div class="flex gap-1">
            @if(auth()->user()->isAdmin() && $consulta->status === 'completed')
                <a href="/consultas/{{ $consulta->id }}/export" class="btn btn-success btn-sm">Exportar Excel</a>
            @endif
            <a href="/consultas" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#ccc">Volver</a>
        </div>
    </div>
</div>

<div class="card" style="overflow-x:auto">
    <table>
        <thead>
            <tr>
                <th>Cédula</th><th>Nombre Completo</th><th>Departamento</th><th>Municipio</th>
                <th>Dirección</th><th>Régimen</th><th>Celular</th><th>Correo</th><th>Estado</th>
            </tr>
        </thead>
        <tbody>
        @foreach($results as $r)
            <tr class="result-row {{ $r->encontrado ? 'found' : 'not-found' }}">
                <td>{{ $r->cedula }}</td>
                <td>{{ $r->primer_nombre }} {{ $r->segundo_nombre }} {{ $r->primer_apellido }} {{ $r->segundo_apellido }}</td>
                <td>{{ $r->departamento ?? '-' }}</td>
                <td>{{ $r->municipio ?? '-' }}</td>
                <td>{{ $r->direccion ?? '-' }}</td>
                <td>{{ $r->regimen ?? '-' }}</td>
                <td>{{ $r->celular ?? '-' }}</td>
                <td>{{ $r->correo ?? '-' }}</td>
                <td>
                    @if($r->error)
                        <span class="badge badge-paused" title="{{ $r->error }}">Error</span>
                    @elseif($r->encontrado)
                        <span class="badge badge-completed">Encontrado</span>
                    @else
                        <span class="badge badge-paused">No encontrado</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="pagination mt-2">{{ $results->links('pagination') }}</div>
</div>
@endsection
