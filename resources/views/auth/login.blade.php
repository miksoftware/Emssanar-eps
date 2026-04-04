@extends('layouts.app')
@section('title', 'Iniciar Sesión')
@section('content')
<div style="max-width:400px;margin:4rem auto">
    <div class="card" style="text-align:center">
        <h2>🏥 Emssanar</h2>
        <p style="color:#888;margin-bottom:2rem">Consulta de Afiliados</p>
        <form method="POST" action="/login">
            @csrf
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem">Ingresar</button>
        </form>
    </div>
</div>
@endsection
