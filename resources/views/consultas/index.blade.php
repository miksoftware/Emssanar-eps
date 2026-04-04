@extends('layouts.app')
@section('title', 'Consultas')
@section('content')

@if(auth()->user()->isAdmin())
<div class="card">
    <h3>Subir Archivo de Cédulas</h3>
    <p style="color:#888;font-size:0.85rem;margin-bottom:1rem">Sube un archivo Excel o CSV con las columnas: <strong style="color:#a5b4fc">cedula</strong> y <strong style="color:#a5b4fc">fecha_de_nacimiento</strong> (formato dd/mm/aaaa)</p>
    <form method="POST" action="/consultas/upload" enctype="multipart/form-data" class="flex gap-2 items-center">
        @csrf
        <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required style="flex:1">
        <button type="submit" class="btn btn-primary">Subir y Procesar</button>
    </form>
</div>
@endif

<div class="card">
    <h3>Historial de Consultas</h3>
    @if($consultas->isEmpty())
        <p style="color:#666">No hay consultas registradas.</p>
    @else
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr><th>#</th><th>Archivo</th><th>Usuario</th><th>Registros</th><th>Procesados</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            @foreach($consultas as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->filename }}</td>
                    <td>{{ $c->user->name }}</td>
                    <td>{{ $c->total_records }}</td>
                    <td>{{ $c->processed }}</td>
                    <td><span class="badge badge-{{ $c->status }}">{{ ucfirst($c->status) }}</span></td>
                    <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
                    <td class="flex gap-1">
                        <a href="/consultas/{{ $c->id }}" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#c4b5fd">Ver</a>
                        @if(auth()->user()->isAdmin())
                            @if(in_array($c->status, ['paused', 'pending', 'processing']))
                                <a href="/consultas/{{ $c->id }}/process" class="btn btn-success btn-sm">Reanudar</a>
                            @endif
                            @if($c->status === 'completed')
                                <a href="/consultas/{{ $c->id }}/export" class="btn btn-primary btn-sm">Exportar</a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination mt-2">{{ $consultas->links('pagination') }}</div>
    @endif
</div>
@endsection
