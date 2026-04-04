<?php

namespace App\Http\Controllers;

use App\Exports\ResultsExport;
use App\Imports\CedulasImport;
use App\Models\Consulta;
use App\Models\ConsultaResult;
use App\Services\EmssanarService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ConsultaController extends Controller
{
    public function index()
    {
        $consultas = Consulta::with('user')
            ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('status', 'completed'))
            ->latest()
            ->paginate(20);

        return view('consultas.index', compact('consultas'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file',
        ]);

        $file = $request->file('archivo');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return back()->withErrors(['archivo' => 'El archivo debe ser .xlsx, .xls o .csv']);
        }

        $import = new CedulasImport();
        Excel::import($import, $file);

        $rows = $import->rows->filter(fn($row) => !empty($row['cedula']));

        if ($rows->isEmpty()) {
            return back()->withErrors(['archivo' => 'No se encontraron cédulas en el archivo. Asegúrate de que la columna se llame "cedula".']);
        }

        $consulta = Consulta::create([
            'user_id' => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'total_records' => $rows->count(),
            'status' => 'pending',
        ]);

        foreach ($rows as $row) {
            $cedula = trim((string) $row['cedula']);
            $fechaNac = trim((string) ($row['fecha_de_nacimiento'] ?? $row['fecha_nacimiento'] ?? ''));

            ConsultaResult::create([
                'consulta_id' => $consulta->id,
                'cedula' => $cedula,
                'fecha_nacimiento' => $fechaNac,
            ]);
        }

        return redirect("/consultas/{$consulta->id}/process");
    }

    public function process(Consulta $consulta)
    {
        return view('consultas.process', compact('consulta'));
    }

    public function processNext(Consulta $consulta, EmssanarService $service)
    {
        $result = $consulta->results()->where('processed', false)->first();

        if (!$result) {
            $consulta->update(['status' => 'completed']);
            return response()->json(['done' => true, 'consulta' => $consulta->fresh()]);
        }

        if ($consulta->status !== 'processing') {
            $consulta->update(['status' => 'processing']);
        }

        try {
            $data = $service->consultarAfiliado($result->cedula, $result->fecha_nacimiento ?? '');

            // Error de conexión (DNS, timeout, etc): NO marcar como procesado para reintentar
            if (!empty($data['connection_error'])) {
                return response()->json([
                    'done' => false,
                    'connection_error' => true,
                    'error_message' => $data['error'],
                    'result' => $result,
                    'processed' => $consulta->processed,
                    'total' => $consulta->total_records,
                ]);
            }

            if (isset($data['error'])) {
                $result->update(['processed' => true, 'error' => $data['error'], 'encontrado' => false]);
            } else {
                $result->update(array_merge($data, ['processed' => true]));
            }
        } catch (\Exception $e) {
            // Errores inesperados: tampoco marcar como procesado
            return response()->json([
                'done' => false,
                'connection_error' => true,
                'error_message' => $e->getMessage(),
                'result' => $result,
                'processed' => $consulta->processed,
                'total' => $consulta->total_records,
            ]);
        }

        $consulta->increment('processed');

        return response()->json([
            'done' => false,
            'result' => $result->fresh(),
            'processed' => $consulta->fresh()->processed,
            'total' => $consulta->total_records,
        ]);
    }

    public function pause(Consulta $consulta)
    {
        $consulta->update(['status' => 'paused']);
        return response()->json(['ok' => true]);
    }

    public function show(Consulta $consulta)
    {
        $results = $consulta->results()->paginate(50);
        return view('consultas.show', compact('consulta', 'results'));
    }

    public function search(Request $request)
    {
        $cedula = $request->input('cedula');
        $results = $cedula
            ? ConsultaResult::where('cedula', 'like', "%{$cedula}%")->with('consulta')->latest()->paginate(20)
            : null;

        return view('consultas.search', compact('cedula', 'results'));
    }

    public function export(Consulta $consulta)
    {
        $filename = 'resultados_' . $consulta->id . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ResultsExport($consulta), $filename);
    }

    public function files()
    {
        $consultas = Consulta::where('status', 'completed')->with('user')->latest()->get();
        return view('consultas.files', compact('consultas'));
    }
}
