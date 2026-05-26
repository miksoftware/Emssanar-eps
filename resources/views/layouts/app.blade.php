<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Emssanar Consultas')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh; color: #e0e0e0;
        }
        .navbar {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 0.6rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar .brand {
            font-size: 1.2rem; font-weight: 700; color: #7c3aed;
            text-decoration: none; display: flex; align-items: center; gap: 0.4rem;
        }
        .navbar .nav-center {
            display: flex; gap: 0.3rem; align-items: center;
            position: absolute; left: 50%; transform: translateX(-50%);
        }
        .navbar .nav-center a {
            color: #9ca3af; text-decoration: none; font-size: 0.85rem;
            padding: 0.45rem 1rem; border-radius: 8px; transition: all 0.2s;
            display: flex; align-items: center; gap: 0.4rem;
        }
        .navbar .nav-center a:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .navbar .nav-center a.active {
            color: #fff; background: #7c3aed;
        }
        .navbar .nav-right {
            display: flex; align-items: center; gap: 0.8rem;
        }
        .navbar .nav-right .user-name {
            font-size: 0.8rem; color: #9ca3af;
        }
        .navbar .nav-right .role-badge {
            font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.5rem;
            border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em;
        }
        .role-badge.admin { background: #7c3aed; color: #fff; }
        .role-badge.consulta { background: rgba(5,150,105,0.3); color: #6ee7b7; }
        .btn-logout {
            background: #dc2626; color: #fff; border: none; padding: 0.35rem 0.8rem;
            border-radius: 6px; font-size: 0.8rem; cursor: pointer; font-weight: 600;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: #b91c1c; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        .card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 2rem; margin-bottom: 1.5rem;
        }
        h1, h2, h3 { color: #c4b5fd; margin-bottom: 1rem; }
        .btn {
            display: inline-block; padding: 0.6rem 1.4rem; border-radius: 10px;
            border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-primary { background: #7c3aed; color: #fff; }
        .btn-primary:hover { background: #6d28d9; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-warning { background: #d97706; color: #fff; }
        .btn-warning:hover { background: #b45309; }
        .btn-sm { padding: 0.35rem 0.8rem; font-size: 0.8rem; }
        input, select {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 8px; padding: 0.6rem 1rem; color: #e0e0e0;
            font-size: 0.9rem; width: 100%;
        }
        input:focus, select:focus { outline: none; border-color: #7c3aed; }
        input[type="file"] { padding: 0.5rem; }
        label { display: block; margin-bottom: 0.4rem; color: #a5b4fc; font-size: 0.85rem; }
        .form-group { margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 0.7rem 1rem; text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        th { color: #a5b4fc; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
        td { font-size: 0.88rem; }
        tr:hover { background: rgba(255,255,255,0.03); }
        .alert {
            padding: 0.8rem 1.2rem; border-radius: 10px; margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .alert-success { background: rgba(5,150,105,0.2); border: 1px solid rgba(5,150,105,0.3); color: #6ee7b7; }
        .alert-error { background: rgba(220,38,38,0.2); border: 1px solid rgba(220,38,38,0.3); color: #fca5a5; }
        .badge {
            display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px;
            font-size: 0.75rem; font-weight: 600;
        }
        .badge-completed { background: rgba(5,150,105,0.2); color: #6ee7b7; }
        .badge-processing { background: rgba(124,58,237,0.2); color: #c4b5fd; }
        .badge-pending { background: rgba(217,119,6,0.2); color: #fcd34d; }
        .badge-paused { background: rgba(220,38,38,0.2); color: #fca5a5; }
        .badge-admin { background: rgba(124,58,237,0.2); color: #c4b5fd; }
        .badge-consulta { background: rgba(5,150,105,0.2); color: #6ee7b7; }
        .progress-bar {
            width: 100%; height: 8px; background: rgba(255,255,255,0.1);
            border-radius: 4px; overflow: hidden; margin: 0.5rem 0;
        }
        .progress-bar .fill {
            height: 100%; background: linear-gradient(90deg, #7c3aed, #a78bfa);
            border-radius: 4px; transition: width 0.3s;
        }
        .pagination { display: flex; gap: 0.3rem; margin-top: 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.85rem;
            text-decoration: none; color: #c4b5fd;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
        }
        .pagination span.current { background: #7c3aed; color: #fff; }
        .flex { display: flex; } .gap-1 { gap: 0.5rem; } .gap-2 { gap: 1rem; }
        .items-center { align-items: center; } .justify-between { justify-content: space-between; }
        .text-right { text-align: right; } .mt-1 { margin-top: 0.5rem; } .mt-2 { margin-top: 1rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
            .navbar .nav-center { position: static; transform: none; }
            .navbar { flex-wrap: wrap; gap: 0.5rem; justify-content: center; }
        }
        .result-row.found { border-left: 3px solid #059669; }
        .result-row.not-found { border-left: 3px solid #dc2626; }
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6);
            z-index: 100; justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #1e1b4b; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 2rem; width: 90%; max-width: 500px;
        }
        .modal h3 { margin-bottom: 1.5rem; }
        /* Afiliado detail card */
        .afiliado-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; padding: 1.2rem 1.5rem; margin-bottom: 1rem;
        }
        .afiliado-card .card-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1rem; padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .afiliado-card .card-header .name {
            font-size: 1.1rem; font-weight: 700; color: #e0e0e0;
        }
        .afiliado-card .card-header .doc {
            font-size: 0.85rem; color: #9ca3af; margin-left: 0.5rem;
        }
        .afiliado-card .info-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0.6rem 1.5rem;
        }
        .afiliado-card .info-item {
            font-size: 0.82rem; color: #9ca3af;
        }
        .afiliado-card .info-item span {
            color: #e0e0e0; font-weight: 500;
        }
    </style>
</head>
<body>
    @auth
    <div class="navbar" style="position:relative">
        <a href="/consultas" class="brand">🏥 Emssanar</a>
        <div class="nav-center">
            <a href="/consultas" class="{{ request()->is('consultas') && !request()->is('consultas/*') ? 'active' : '' }}">📋 Consultas</a>
            <a href="/consultas/search" class="{{ request()->is('consultas/search*') ? 'active' : '' }}">🔍 Buscar</a>
            @if(auth()->user()->isAdmin())
                <a href="/files" class="{{ request()->is('files') ? 'active' : '' }}">📁 Archivos</a>
                <a href="/users" class="{{ request()->is('users') ? 'active' : '' }}">👥 Usuarios</a>
                <a href="{{ route('emssanar.credentials') }}" class="{{ request()->routeIs('emssanar.*') ? 'active' : '' }}" style="display:inline-flex;align-items:center;gap:6px;">
                    🔗 API
                    @if(session()->has('emssanar_api_url'))
                        <span style="width:8px;height:8px;border-radius:50%;background:#69f0ae;display:inline-block;" title="URL personalizada activa"></span>
                    @else
                        <span style="width:8px;height:8px;border-radius:50%;background:#9999bb;display:inline-block;" title="Usando URL por defecto"></span>
                    @endif
                </a>
            @endif
        </div>
        <div class="nav-right">
            <span class="user-name">{{ auth()->user()->name }}</span>
            <span class="role-badge {{ auth()->user()->role }}">{{ strtoupper(auth()->user()->role) }}</span>
            <form action="/logout" method="POST" style="display:inline;margin:0">
                @csrf
                <button type="submit" class="btn-logout">Salir</button>
            </form>
        </div>
    </div>
    @endauth

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
