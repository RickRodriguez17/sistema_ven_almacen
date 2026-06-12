@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Ingresos por día</h1></div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
          <div class="col-md-3"><label class="form-label">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="form-control"></div>
          <div class="col-md-2"><button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button></div>
        </form>

        <div id="chartIngresos" style="min-height: 320px;"></div>

        <div class="table-responsive mt-3">
          <table class="table table-sm">
            <thead><tr><th>Fecha</th><th>Ventas</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              @forelse($rows as $r)
                <tr><td>{{ $r->fecha }}</td><td>{{ $r->cantidad }}</td><td class="text-end">{{ $negocio['moneda'] }} {{ number_format($r->total, 2) }}</td></tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted">Sin datos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const data = @json($rows);
  if (!data.length) return;
  new ApexCharts(document.querySelector('#chartIngresos'), {
    chart: { type: 'bar', height: 320, toolbar: { show: false } },
    series: [{ name: 'Ingresos', data: data.map(d => Number(d.total)) }],
    xaxis: { categories: data.map(d => d.fecha) },
    colors: ['#EA3323'],
    dataLabels: { enabled: false },
  }).render();
});
</script>
@endpush
