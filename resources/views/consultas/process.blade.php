@extends('layouts.app')
@section('title', 'Procesando Consulta #' . $consulta->id)
@section('content')
<div class="card">
    <div class="flex justify-between items-center">
        <h2>Procesando: {{ $consulta->filename }}</h2>
        <div class="flex gap-1">
            <button id="btn-pause" class="btn btn-warning btn-sm" onclick="pauseProcess()">Pausar</button>
            <a href="/consultas" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#ccc">Volver</a>
        </div>
    </div>

    <div style="margin:1rem 0">
        <div class="flex justify-between" style="font-size:0.85rem;color:#888">
            <span>Progreso: <strong id="counter" style="color:#c4b5fd">{{ $consulta->processed }}</strong> / {{ $consulta->total_records }}</span>
            <span id="percent">{{ $consulta->total_records > 0 ? round($consulta->processed / $consulta->total_records * 100) : 0 }}%</span>
        </div>
        <div class="progress-bar">
            <div class="fill" id="progress" style="width:{{ $consulta->total_records > 0 ? ($consulta->processed / $consulta->total_records * 100) : 0 }}%"></div>
        </div>
        <div style="font-size:0.8rem;color:#666;margin-top:0.3rem">
            Encontrados: <span id="found-count" style="color:#6ee7b7">0</span> |
            No encontrados: <span id="notfound-count" style="color:#fca5a5">0</span> |
            Errores: <span id="error-count" style="color:#fcd34d">0</span>
        </div>
    </div>
</div>

<div class="card" id="results-container">
    <h3>Resultados en Tiempo Real</h3>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr><th>Cédula</th><th>Nombre</th><th>Apellido</th><th>Municipio</th><th>Régimen</th><th>Estado</th></tr>
            </thead>
            <tbody id="results-body"></tbody>
        </table>
    </div>
</div>

<script>
let running = true;
let found = 0, notFound = 0, errors = 0;
let retryCount = 0;
const maxRetries = 5;
const delay = {{ config('emssanar.delay') }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function showConnectionError(msg) {
    let banner = document.getElementById('conn-error-banner');
    if (!banner) {
        banner = document.createElement('div');
        banner.id = 'conn-error-banner';
        banner.style.cssText = 'background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);color:#fca5a5;padding:0.8rem 1.2rem;border-radius:10px;margin-bottom:1rem;font-size:0.9rem;';
        document.getElementById('results-container').before(banner);
    }
    banner.innerHTML = '⚠️ ' + msg + ' — Reintentando automáticamente... (intento ' + retryCount + '/' + maxRetries + ')';
}

function hideConnectionError() {
    const banner = document.getElementById('conn-error-banner');
    if (banner) banner.remove();
    retryCount = 0;
}

async function processNext() {
    if (!running) return;

    try {
        const res = await fetch('/consultas/{{ $consulta->id }}/process-next', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();

        if (data.done) {
            hideConnectionError();
            document.getElementById('btn-pause').textContent = 'Completado';
            document.getElementById('btn-pause').disabled = true;
            document.getElementById('btn-pause').classList.remove('btn-warning');
            document.getElementById('btn-pause').classList.add('btn-success');
            updateProgress({{ $consulta->total_records }}, {{ $consulta->total_records }});
            return;
        }

        // Error de conexión: reintentar sin marcar como procesado
        if (data.connection_error) {
            retryCount++;
            showConnectionError(data.error_message || 'Error de conexión con la API');

            if (retryCount >= maxRetries) {
                running = false;
                let banner = document.getElementById('conn-error-banner');
                if (banner) {
                    banner.innerHTML = '❌ No se pudo conectar a la API después de ' + maxRetries + ' intentos. Verifica tu conexión y presiona <strong>Reanudar</strong> en el historial de consultas.';
                    banner.style.borderColor = 'rgba(220,38,38,0.6)';
                }
                document.getElementById('btn-pause').textContent = 'Detenido';
                document.getElementById('btn-pause').disabled = true;
                // Pausar la consulta en el servidor
                fetch('/consultas/{{ $consulta->id }}/pause', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                return;
            }

            // Esperar más tiempo entre reintentos (backoff)
            setTimeout(processNext, delay * 3 * retryCount);
            return;
        }

        // Éxito: resetear contador de reintentos
        hideConnectionError();

        const r = data.result;
        updateProgress(data.processed, data.total);

        if (r.error) { errors++; }
        else if (r.encontrado) { found++; }
        else { notFound++; }

        document.getElementById('found-count').textContent = found;
        document.getElementById('notfound-count').textContent = notFound;
        document.getElementById('error-count').textContent = errors;

        const row = document.createElement('tr');
        row.className = 'result-row ' + (r.encontrado ? 'found' : 'not-found');
        row.innerHTML = `
            <td>${r.cedula}</td>
            <td>${r.primer_nombre || ''} ${r.segundo_nombre || ''}</td>
            <td>${r.primer_apellido || ''} ${r.segundo_apellido || ''}</td>
            <td>${r.municipio || '-'}</td>
            <td>${r.regimen || '-'}</td>
            <td>${r.encontrado ? '<span class="badge badge-completed">Encontrado</span>' : '<span class="badge badge-paused">' + (r.error || 'No encontrado') + '</span>'}</td>
        `;
        document.getElementById('results-body').prepend(row);

        setTimeout(processNext, delay);
    } catch (e) {
        console.error('Error fetch:', e);
        retryCount++;
        if (retryCount >= maxRetries) {
            showConnectionError('Error de red. Verifica tu conexión.');
            running = false;
            return;
        }
        setTimeout(processNext, delay * 3 * retryCount);
    }
}

function updateProgress(processed, total) {
    const pct = total > 0 ? Math.round(processed / total * 100) : 0;
    document.getElementById('counter').textContent = processed;
    document.getElementById('percent').textContent = pct + '%';
    document.getElementById('progress').style.width = pct + '%';
}

function pauseProcess() {
    running = false;
    fetch('/consultas/{{ $consulta->id }}/pause', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    });
    document.getElementById('btn-pause').textContent = 'Pausado';
    document.getElementById('btn-pause').disabled = true;
}

processNext();
</script>
@endsection
