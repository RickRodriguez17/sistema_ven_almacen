<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Empresa;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CierresCaja extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = CierreCaja::with('user')->orderByDesc('fecha_apertura');

        if (! $user->esAdmin() && ! $user->esAlmacen()) {
            $query->where('user_id', $user->id);
        }

        $cierres = $query->paginate(20);

        // Para cierres abiertos, computar totales en tiempo real
        foreach ($cierres as $c) {
            if ($c->estaAbierto()) {
                $resumen = $c->calcularResumenEnVivo();
                $c->total_ventas = $resumen['total_ventas'];
                $c->cantidad_ventas = $resumen['cantidad_ventas'];
                $c->total_gastos = $resumen['total_gastos'];
            }
        }

        $abierto = CierreCaja::abiertoDe($user->id);

        return view('modules.cierres.index', [
            'titulo' => 'Cierres de Caja',
            'cierres' => $cierres,
            'abierto' => $abierto,
        ]);
    }

    public function iniciarForm()
    {
        $abierto = CierreCaja::abiertoDe(Auth::id());

        if ($abierto) {
            return redirect()->route('ventas-nueva')
                ->with('mensaje', 'Ya tienes un turno abierto.');
        }

        return view('modules.cierres.iniciar', [
            'titulo' => 'Iniciar turno de ventas',
        ]);
    }

    public function iniciar(Request $request)
    {
        $data = $request->validate([
            'fondo_inicial' => ['required', 'numeric', 'min:0'],
        ]);

        if (CierreCaja::abiertoDe(Auth::id())) {
            return redirect()->route('ventas-nueva')
                ->with('mensaje', 'Ya tienes un turno abierto.');
        }

        CierreCaja::create([
            'user_id' => Auth::id(),
            'fecha_apertura' => now(),
            'fondo_inicial' => $data['fondo_inicial'],
            'estado' => CierreCaja::ESTADO_ABIERTO,
        ]);

        return redirect()->route('ventas-nueva')
            ->with('mensaje', 'Turno iniciado. ¡A vender!');
    }

    public function cerrarForm()
    {
        $abierto = CierreCaja::abiertoDe(Auth::id());

        if (! $abierto) {
            return redirect()->route('home')
                ->with('mensaje', 'No tienes turno abierto.');
        }

        $resumen = $this->calcularResumen($abierto);
        $gastos = $abierto->gastos()->with('user')->latest()->get();
        $anuladas = $this->ventasDelCierre($abierto)
            ->where('estado', Venta::ESTADO_ANULADA)
            ->with('anuladaPor')
            ->orderBy('anulada_at', 'desc')
            ->get();

        return view('modules.cierres.cerrar', [
            'titulo' => 'Cerrar turno de ventas',
            'cierre' => $abierto,
            'resumen' => $resumen,
            'gastos' => $gastos,
            'anuladas' => $anuladas,
        ]);
    }

    public function cerrar(Request $request)
    {
        $data = $request->validate([
            'efectivo_contado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $abierto = CierreCaja::abiertoDe(Auth::id());

        if (! $abierto) {
            return redirect()->route('home')
                ->with('mensaje', 'No tienes turno abierto.');
        }

        $resumen = $this->calcularResumen($abierto);

        $efectivoEsperado = $abierto->fondo_inicial + $resumen['total_efectivo'] - $resumen['total_gastos'];
        $diferencia = round((float) $data['efectivo_contado'] - $efectivoEsperado, 2);

        $abierto->update([
            'fecha_cierre' => now(),
            'total_ventas' => $resumen['total_ventas'],
            'total_efectivo' => $resumen['total_efectivo'],
            'total_tarjeta' => $resumen['total_tarjeta'],
            'total_transferencia' => $resumen['total_transferencia'],
            'total_yape' => $resumen['total_yape'],
            'total_plin' => $resumen['total_plin'],
            'total_otros' => $resumen['total_otros'],
            'total_gastos' => $resumen['total_gastos'],
            'cantidad_ventas' => $resumen['cantidad_ventas'],
            'efectivo_contado' => $data['efectivo_contado'],
            'diferencia' => $diferencia,
            'estado' => CierreCaja::ESTADO_CERRADO,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return redirect()->route('cierres.show', $abierto->id)
            ->with('mensaje', 'Turno cerrado correctamente.');
    }

    public function show($id)
    {
        $cierre = CierreCaja::with('user')->findOrFail($id);

        $user = Auth::user();
        if (! $user->esAdmin() && ! $user->esAlmacen() && $cierre->user_id !== $user->id) {
            abort(403);
        }

        $ventas = $this->ventasDelCierre($cierre)
            ->with(['detalles.producto', 'anuladaPor'])
            ->orderBy('created_at')
            ->get();

        $anuladas = $ventas->where('estado', Venta::ESTADO_ANULADA)->values();
        $gastos = $cierre->gastos()->with('user')->latest()->get();

        // Para turnos abiertos, calcular el resumen en tiempo real
        if ($cierre->estaAbierto()) {
            $resumen = $cierre->calcularResumenEnVivo();
            $cierre->total_ventas = $resumen['total_ventas'];
            $cierre->cantidad_ventas = $resumen['cantidad_ventas'];
            $cierre->total_efectivo = $resumen['total_efectivo'];
            $cierre->total_tarjeta = $resumen['total_tarjeta'];
            $cierre->total_yape = $resumen['total_yape'];
            $cierre->total_gastos = $resumen['total_gastos'];
        }

        return view('modules.cierres.show', [
            'titulo' => 'Cierre #'.str_pad($cierre->id, 5, '0', STR_PAD_LEFT),
            'cierre' => $cierre,
            'ventas' => $ventas,
            'anuladas' => $anuladas,
            'gastos' => $gastos,
        ]);
    }

    public function pdf($id)
    {
        $user = Auth::user();
        if (! $user->puedeImprimirReportes()) {
            abort(403);
        }

        $cierre = CierreCaja::with('user')->findOrFail($id);

        $ventas = $this->ventasDelCierre($cierre)
            ->with(['detalles.producto', 'anuladaPor'])
            ->orderBy('created_at')
            ->get();

        $anuladas = $ventas->where('estado', Venta::ESTADO_ANULADA)->values();
        $gastos = $cierre->gastos()->with('user')->latest()->get();

        $negocio = config('negocio');
        if ($empresa = Empresa::actual()) {
            $negocio = array_merge($negocio, $empresa->toNegocio());
        }

        $pdf = Pdf::loadView('modules.cierres.pdf', compact('cierre', 'ventas', 'anuladas', 'gastos', 'negocio'));

        return $pdf->stream("cierre-{$cierre->id}.pdf");
    }

    /**
     * Construye el query base de ventas asignadas a este cierre.
     * Prioriza el FK cierre_caja_id (vínculo directo). Si la columna
     * todavía es nula (datos legacy), usa el rango de fechas como fallback.
     */
    protected function ventasDelCierre(CierreCaja $cierre)
    {
        return Venta::query()
            ->where(function ($q) use ($cierre) {
                $q->where('cierre_caja_id', $cierre->id)
                    ->orWhere(function ($q2) use ($cierre) {
                        $q2->whereNull('cierre_caja_id')
                            ->where('user_id', $cierre->user_id)
                            ->whereBetween('created_at', [
                                $cierre->fecha_apertura,
                                $cierre->fecha_cierre ?? now(),
                            ]);
                    });
            });
    }

    protected function calcularResumen(CierreCaja $cierre): array
    {
        $ventas = $this->ventasDelCierre($cierre)
            ->with('pagos')
            ->where('estado', Venta::ESTADO_PAGADA)
            ->get();

        $totalGastos = round((float) $cierre->gastos()->sum('monto'), 2);

        $resumen = [
            'cantidad_ventas' => $ventas->count(),
            'total_ventas' => round($ventas->sum('total_venta'), 2),
            'total_efectivo' => 0,
            'total_tarjeta' => 0,
            'total_transferencia' => 0,
            'total_yape' => 0,
            'total_plin' => 0,
            'total_otros' => 0,
            'total_gastos' => $totalGastos,
        ];

        foreach ($ventas as $v) {
            // Si la venta tiene pagos_venta (multi-pago), usar esos.
            if ($v->pagos && $v->pagos->count() > 0) {
                foreach ($v->pagos as $p) {
                    $key = 'total_'.$p->metodo_pago;
                    if (array_key_exists($key, $resumen)) {
                        $resumen[$key] += $p->monto;
                    } else {
                        $resumen['total_otros'] += $p->monto;
                    }
                }
            } else {
                // Fallback: venta sin pagos_venta usa metodo_pago de la venta.
                $key = 'total_'.$v->metodo_pago;
                if (array_key_exists($key, $resumen)) {
                    $resumen[$key] += $v->total_venta;
                } else {
                    $resumen['total_otros'] += $v->total_venta;
                }
            }
        }

        foreach ($resumen as $k => $val) {
            if (str_starts_with($k, 'total_')) {
                $resumen[$k] = round((float) $val, 2);
            }
        }

        return $resumen;
    }
}
