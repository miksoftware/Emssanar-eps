@extends('layouts.app')
@section('title', 'Archivos Exportables')
@section('content')
<div class="card">
    <h2>Consultas Completadas</h2>
    <p style="color:#888;font-size:0.85rem;margin-bottom:1rem">Descarga los resultados en formato Excel</p>

    @if($consultas->isEmpty())
        <p style="color:#666">No hay consultas completadas.</p>
    @else
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr><th>#</th><th>Archivo Original</th><th>Registros</th><th>Usuario</th><th>Fecha</th><th>Acción</th></tr>
            </thead>
            <tbody>
            @foreach($consultas as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->filename }}</td>
                    <td>{{ $c->total_records }}</td>
                    <td>{{ $c->user->name }}</td>
                    <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
                    <td><a href="/consultas/{{ $c->id }}/export" class="btn btn-success btn-sm">Descargar Excel</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
