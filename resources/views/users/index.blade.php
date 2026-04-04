@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
<div class="flex justify-between items-center" style="margin-bottom:1rem">
    <h2>Usuarios</h2>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-create').classList.add('active')">+ Nuevo Usuario</button>
</div>

<div class="card" style="overflow-x:auto">
    <table>
        <thead>
            <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                <td class="flex gap-1">
                    <button class="btn btn-warning btn-sm" onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}')">Editar</button>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="/users/{{ $user->id }}" onsubmit="return confirm('¿Eliminar usuario?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Crear -->
<div class="modal-overlay" id="modal-create">
    <div class="modal">
        <h3>Nuevo Usuario</h3>
        <form method="POST" action="/users">
            @csrf
            <div class="form-group"><label>Nombre</label><input name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Contraseña</label><input type="password" name="password" required></div>
            <div class="form-group">
                <label>Rol</label>
                <select name="role"><option value="consulta">Consulta</option><option value="admin">Admin</option></select>
            </div>
            <div class="flex gap-1" style="justify-content:flex-end">
                <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#ccc" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Crear</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <h3>Editar Usuario</h3>
        <form method="POST" id="edit-form">
            @csrf @method('PUT')
            <div class="form-group"><label>Nombre</label><input name="name" id="edit-name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" id="edit-email" required></div>
            <div class="form-group"><label>Nueva contraseña (dejar vacío para no cambiar)</label><input type="password" name="password"></div>
            <div class="form-group">
                <label>Rol</label>
                <select name="role" id="edit-role"><option value="consulta">Consulta</option><option value="admin">Admin</option></select>
            </div>
            <div class="flex gap-1" style="justify-content:flex-end">
                <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#ccc" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editUser(id, name, email, role) {
    document.getElementById('edit-form').action = '/users/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-role').value = role;
    document.getElementById('modal-edit').classList.add('active');
}
</script>
@endsection
