@extends('layouts.app')
@section('title', 'Configuración API - Emssanar')

@section('content')
<div class="card glass">
    <h2 style="margin-bottom: 0.25rem;">URL de la API Emssanar</h2>
    <p style="color: #9999bb; font-size: 0.85rem; margin-bottom: 1.5rem;">
        Configure el endpoint de la API. Cuando Emssanar cambie la dirección, actualícela aquí sin tocar el servidor.
    </p>

    {{-- URL activa --}}
    <div style="background: rgba(139,92,246,0.08); border: 1px solid rgba(139,92,246,0.25); border-radius: 10px; padding: 0.9rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
        <span style="color: #9999bb;">URL activa:</span>
        <strong id="displayUrl" style="color: #a78bfa; word-break: break-all; margin-left: 6px;">{{ $apiUrl }}</strong>
        @if(session()->has('emssanar_api_url'))
            <span style="margin-left: 8px; background: rgba(105,240,174,0.15); color: #69f0ae; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem;">sesión</span>
        @else
            <span style="margin-left: 8px; background: rgba(255,255,255,0.08); color: #9999bb; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem;">config/.env</span>
        @endif
    </div>

    {{-- Form --}}
    <div class="form-group">
        <label class="form-label">Nuevo endpoint (URL completa)</label>
        <input type="url" id="apiUrl" class="form-control"
               value="{{ $apiUrl }}"
               placeholder="https://sagasb.emssanareps.co:8083/api/pqrd-externo/validar-afiliado">
        <small style="color: #666688; font-size: 0.78rem; margin-top: 4px; display: block;">
            Incluya la ruta completa del endpoint de validación de afiliados.
        </small>
    </div>

    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1.25rem;">
        <button class="btn btn-primary" id="btnSave" onclick="saveUrl()">Guardar URL</button>
        <button class="btn btn-success" id="btnTest" onclick="testUrl()">Probar conexión</button>
        @if(session()->has('emssanar_api_url'))
        <button class="btn btn-secondary" onclick="resetUrl()">Restaurar default</button>
        @endif
    </div>

    <div id="message" style="margin-top: 1rem; display: none;"></div>
</div>

{{-- Resultado del test --}}
<div class="card glass" id="testCard" style="display: none;">
    <h3 style="margin-bottom: 0.75rem;">Resultado</h3>
    <p id="testResult" style="font-size: 0.88rem; font-family: Consolas, monospace; word-break: break-all;"></p>
</div>
@endsection

@section('scripts')
<script>
    function showMsg(text, type) {
        const colors = { success: '#69f0ae', error: '#ff6b7a', info: '#a78bfa' };
        const el = document.getElementById('message');
        el.style.display = 'block';
        el.innerHTML = `<div style="padding:0.75rem 1rem;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid ${colors[type]||colors.info}44;color:${colors[type]||colors.info};font-size:0.85rem;">${text}</div>`;
    }

    async function saveUrl() {
        const url = document.getElementById('apiUrl').value.trim();
        if (!url) { showMsg('Ingrese una URL válida.', 'error'); return; }

        const btn = document.getElementById('btnSave');
        btn.disabled = true; btn.textContent = 'Guardando...';

        try {
            const res = await fetch('{{ route("emssanar.credentials.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ api_url: url }),
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('displayUrl').textContent = url;
                showMsg('✓ URL guardada correctamente.', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showMsg(data.message || 'Error al guardar.', 'error');
            }
        } catch (e) {
            showMsg('Error: ' + e.message, 'error');
        } finally {
            btn.disabled = false; btn.textContent = 'Guardar URL';
        }
    }

    async function testUrl() {
        const btn = document.getElementById('btnTest');
        btn.disabled = true; btn.textContent = '⏳ Probando...';

        const card = document.getElementById('testCard');
        const result = document.getElementById('testResult');
        card.style.display = 'block';
        result.style.color = '#7777aa';
        result.textContent = 'Conectando a la API Emssanar...';

        try {
            const res = await fetch('{{ route("emssanar.credentials.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const data = await res.json();
            result.style.color = data.success ? '#69f0ae' : '#ff6b7a';
            result.textContent = data.message;
            showMsg((data.success ? '✓ ' : '✗ ') + data.message, data.success ? 'success' : 'error');
        } catch (e) {
            result.style.color = '#ff6b7a';
            result.textContent = 'Error: ' + e.message;
            showMsg('Error: ' + e.message, 'error');
        } finally {
            btn.disabled = false; btn.textContent = 'Probar conexión';
        }
    }

    async function resetUrl() {
        if (!confirm('¿Restaurar la URL del config/.env?')) return;
        await fetch('{{ route("emssanar.credentials.reset") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });
        location.reload();
    }
</script>
@endsection
