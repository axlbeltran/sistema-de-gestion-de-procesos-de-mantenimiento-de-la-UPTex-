@php($titulo = 'Levantamientos tecnicos')
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $titulo }} | UPTEX</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f1f5f3;color:#24352d;font:16px Arial,sans-serif}header{background:#105c40;color:white;padding:22px max(20px,5vw);display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}header a{color:white}main{max-width:1200px;margin:28px auto;padding:0 18px}section{background:white;border:1px solid #dce7e0;border-radius:12px;padding:24px;margin-bottom:20px}h1,h2{color:#105c40}h1{margin-top:0}label{display:block;font-weight:bold;margin:12px 0 6px}input,select,textarea{font:inherit;width:100%;padding:11px;border:1px solid #aabeb2;border-radius:6px;background:white}textarea{resize:vertical}button,.btn{display:inline-block;background:#105c40;color:white;border:0;border-radius:7px;padding:11px 16px;font:inherit;text-decoration:none;cursor:pointer}.secondary{background:#e4eee8;color:#105c40}.actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:18px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}.scroll{overflow-x:auto}table{border-collapse:collapse;width:100%}th,td{text-align:left;padding:12px;border-bottom:1px solid #dce7e0}th{background:#e8f1ec}.status{display:inline-block;background:#e8f1ec;color:#105c40;padding:6px 10px;border-radius:6px}.error{background:#fce8e8;color:#942626;padding:12px;border-radius:6px}.success{background:#e3f4e9;color:#105c40;padding:12px;border-radius:6px}.value{white-space:pre-wrap;overflow-wrap:anywhere}small{color:#53675b}nav svg{width:18px;height:18px}nav a{color:#105c40}nav{margin-top:16px}a:focus,button:focus,input:focus,select:focus,textarea:focus{outline:2px solid #27865e;outline-offset:2px}
</style>
</head>
<body>
<header><strong>UPTEX | Gestion de mantenimiento</strong><a href="{{ route('dashboard') }}">Volver al dashboard</a></header>
<main>
@if(session('success'))<p class="success" role="status">{{ session('success') }}</p>@endif
@if($errors->any())<div class="error" role="alert"><strong>Revisa los siguientes datos:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<h1>Levantamientos tecnicos</h1>
<section><form method="GET" action="{{ route('levantamientos.index') }}"><div class="grid"><div><label for="buscar">Buscar solicitud</label><input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Folio, area, ubicacion o descripcion"></div><div><label for="estado">Estado del levantamiento</label><select id="estado" name="estado"><option value="">Todos</option>@foreach(['Borrador','Pendiente de Revision','Aprobado','Rechazado'] as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select></div></div><div class="actions"><button>Buscar</button><a class="btn secondary" href="{{ route('levantamientos.index') }}">Limpiar</a></div></form></section>
<section><div class="scroll"><table><thead><tr><th>Folio</th><th>Solicitante</th><th>Area</th><th>Servicio</th><th>Ubicacion</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($solicitudes as $solicitud)
<tr><td>#{{ $solicitud->id }}</td><td>{{ $solicitud->solicitante_nombre }} {{ $solicitud->solicitante_apellido }}</td><td>{{ $solicitud->nombre_area }}</td><td>{{ $solicitud->nombre_servicio }}</td><td>{{ $solicitud->ubicacion }}</td><td><span class="status">{{ $solicitud->estado_levantamiento ?? 'Sin levantamiento' }}</span></td><td>
@if($solicitud->levantamiento_id !== null)
<a class="btn secondary" href="{{ route('levantamientos.show', $solicitud->id) }}">Consultar</a>
@elseif($puedeGestionar && $solicitud->estado === 'Pendiente' && $solicitud->tecnico_asignado_id === null)
<a class="btn" href="{{ route('levantamientos.create', $solicitud->id) }}">Elaborar</a>
@endif
</td></tr>
@empty<tr><td colspan="7">No hay solicitudes disponibles.</td></tr>@endforelse
</tbody></table></div>{{ $solicitudes->links() }}</section>
</main>
</body>
</html>
