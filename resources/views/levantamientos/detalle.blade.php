@php($titulo = 'Detalle del levantamiento tecnico')
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
<h1>Detalle del levantamiento tecnico</h1>
<section><h2>Solicitud #{{ $solicitud->id }}</h2><div class="grid"><div><strong>Solicitante</strong><p>{{ $solicitud->solicitante_nombre }} {{ $solicitud->solicitante_apellido }}</p></div><div><strong>Area</strong><p>{{ $solicitud->nombre_area }}</p></div><div><strong>Servicio</strong><p>{{ $solicitud->nombre_servicio }}</p></div><div><strong>Ubicacion</strong><p>{{ $solicitud->ubicacion }}</p></div></div><strong>Descripcion de la solicitud</strong><p class="value">{{ $solicitud->problema_descripcion }}</p></section>
<section><h2>Diagnostico #{{ $levantamiento->id }}</h2><p><span class="status">{{ $levantamiento->estado }}</span></p><p>Elaborado por: {{ $levantamiento->elaborador?->nombre }} {{ $levantamiento->elaborador?->apellido }}</p><p>Fecha: {{ $levantamiento->fecha_elaboracion?->format('d/m/Y H:i') }}</p><div class="grid">
<div><strong>Origen del mantenimiento</strong><p>{{ $levantamiento->origen_mantenimiento }}</p><small>Observaciones</small><p class="value">{{ $levantamiento->observaciones_origen ?? "Sin observaciones" }}</p></div>
<div><strong>Tipo de mantenimiento</strong><p>{{ $levantamiento->tipo_mantenimiento }}</p><small>Observaciones</small><p class="value">{{ $levantamiento->observaciones_tipo ?? "Sin observaciones" }}</p></div>
<div><strong>Cuenta con insumos</strong><p>{{ $levantamiento->cuenta_con_insumos ? 'Si' : 'No' }}</p><small>Observaciones</small><p class="value">{{ $levantamiento->observaciones_insumos ?? "Sin observaciones" }}</p></div>
<div><strong>Es urgente</strong><p>{{ $levantamiento->es_urgente ? 'Si' : 'No' }}</p><small>Observaciones</small><p class="value">{{ $levantamiento->observaciones_urgencia ?? "Sin observaciones" }}</p></div>
<div><strong>Tiempo aproximado</strong><p>{{ $levantamiento->tiempo_aproximado }}</p><small>Observaciones</small><p class="value">{{ $levantamiento->observaciones_tiempo ?? "Sin observaciones" }}</p></div>
<div><strong>Representa riesgo</strong><p>{{ $levantamiento->representa_riesgo ? 'Si' : 'No' }}</p><small>Observaciones</small><p class="value">{{ $levantamiento->observaciones_riesgo ?? "Sin observaciones" }}</p></div>
</div><h2>Diagnostico tecnico</h2><p class="value">{{ $levantamiento->diagnostico_tecnico }}</p><h2>Materiales por utilizar</h2><p class="value">{{ $levantamiento->materiales_utilizar ?? 'Sin materiales registrados' }}</p>
<div class="actions"><a class="btn secondary" href="{{ route('levantamientos.index') }}">Volver al listado</a>
@if($puedeGestionar && (int) auth()->id() === (int) $levantamiento->elaborado_por && in_array($levantamiento->estado, ['Borrador', 'Rechazado'], true) && $solicitud->estado === 'Pendiente' && $solicitud->tecnico_asignado_id === null)
<a class="btn secondary" href="{{ route('levantamientos.edit', $solicitud->id) }}">{{ $levantamiento->estado === 'Rechazado' ? 'Corregir levantamiento' : 'Editar borrador' }}</a>
@if($levantamiento->estado === 'Borrador')
<form method="POST" action="{{ route('levantamientos.enviar', $solicitud->id) }}" onsubmit="return confirm('Enviar a revision? Ya no podras editar el borrador.')">@csrf @method('PATCH')<button type="submit">Enviar a revision</button></form>
@endif
@endif</div></section>
@if($puedeRevisar)
<section>
<h2>Visto bueno tecnico</h2>
<form method="POST" action="{{ route('levantamientos.resolver', $solicitud->id) }}">
@csrf
<input type="hidden" name="version" value="{{ $version }}">
<label for="decision">Decision</label>
<select id="decision" name="decision" required>
<option value="">Selecciona una decision</option>
<option value="Aprobada" @selected(old('decision') === 'Aprobada')>Aprobar</option>
<option value="Rechazada" @selected(old('decision') === 'Rechazada')>Rechazar</option>
</select>
<label for="observaciones">Observaciones (obligatorias al rechazar)</label>
<textarea id="observaciones" name="observaciones" rows="4" maxlength="5000">{{ old('observaciones') }}</textarea>
<div class="actions"><button type="submit">Registrar decision</button></div>
</form>
</section>
@endif
<section>
<h2>Historial de revisiones tecnicas</h2>
<div class="scroll"><table><thead><tr><th>Revisor</th><th>Decision</th><th>Observaciones</th><th>Fecha</th></tr></thead><tbody>
@forelse($revisiones as $revision)
<tr><td>{{ $revision->nombre }} {{ $revision->apellido }}</td><td>{{ $revision->decision }}</td><td class="value">{{ $revision->observaciones ?? 'Sin observaciones' }}</td><td>{{ $revision->fecha_autorizacion }}</td></tr>
@empty
<tr><td colspan="4">No hay revisiones registradas.</td></tr>
@endforelse
</tbody></table></div>
</section>
</main>
</body>
</html>
