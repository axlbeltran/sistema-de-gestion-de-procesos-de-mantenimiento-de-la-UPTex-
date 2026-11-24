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
<h1>{{ $titulo }}</h1>
<section><h2>Solicitud #{{ $solicitud->id }}</h2><div class="grid"><div><strong>Solicitante</strong><p>{{ $solicitud->solicitante_nombre }} {{ $solicitud->solicitante_apellido }}</p></div><div><strong>Area</strong><p>{{ $solicitud->nombre_area }}</p></div><div><strong>Servicio</strong><p>{{ $solicitud->nombre_servicio }}</p></div><div><strong>Ubicacion</strong><p>{{ $solicitud->ubicacion }}</p></div></div><strong>Descripcion de la solicitud</strong><p class="value">{{ $solicitud->problema_descripcion }}</p></section>
<section><form method="POST" action="{{ $accion }}">
@csrf
@if($esEdicion) @method('PUT') @endif
<div class="grid">
<div><label for="origen_mantenimiento">Origen del mantenimiento</label>
<select id="origen_mantenimiento" name="origen_mantenimiento" required>
<option value="">Selecciona una opcion</option>
<option value="Interno" @selected((string) old('origen_mantenimiento', $levantamiento->origen_mantenimiento === null ? '' : $levantamiento->origen_mantenimiento) === 'Interno')>Interno</option>
<option value="Externo" @selected((string) old('origen_mantenimiento', $levantamiento->origen_mantenimiento === null ? '' : $levantamiento->origen_mantenimiento) === 'Externo')>Externo</option>
</select><label for="observaciones_origen">Observaciones</label><textarea id="observaciones_origen" name="observaciones_origen" rows="3" maxlength="3000">{{ old('observaciones_origen', $levantamiento->observaciones_origen) }}</textarea></div>
<div><label for="tipo_mantenimiento">Tipo de mantenimiento</label>
<select id="tipo_mantenimiento" name="tipo_mantenimiento" required>
<option value="">Selecciona una opcion</option>
<option value="Preventivo" @selected((string) old('tipo_mantenimiento', $levantamiento->tipo_mantenimiento === null ? '' : $levantamiento->tipo_mantenimiento) === 'Preventivo')>Preventivo</option>
<option value="Correctivo" @selected((string) old('tipo_mantenimiento', $levantamiento->tipo_mantenimiento === null ? '' : $levantamiento->tipo_mantenimiento) === 'Correctivo')>Correctivo</option>
</select><label for="observaciones_tipo">Observaciones</label><textarea id="observaciones_tipo" name="observaciones_tipo" rows="3" maxlength="3000">{{ old('observaciones_tipo', $levantamiento->observaciones_tipo) }}</textarea></div>
<div><label for="cuenta_con_insumos">Cuenta con insumos</label>
<select id="cuenta_con_insumos" name="cuenta_con_insumos" required>
<option value="">Selecciona una opcion</option>
<option value="1" @selected((string) old('cuenta_con_insumos', $levantamiento->cuenta_con_insumos === null ? '' : (int) $levantamiento->cuenta_con_insumos) === '1')>Si</option>
<option value="0" @selected((string) old('cuenta_con_insumos', $levantamiento->cuenta_con_insumos === null ? '' : (int) $levantamiento->cuenta_con_insumos) === '0')>No</option>
</select><label for="observaciones_insumos">Observaciones</label><textarea id="observaciones_insumos" name="observaciones_insumos" rows="3" maxlength="3000">{{ old('observaciones_insumos', $levantamiento->observaciones_insumos) }}</textarea></div>
<div><label for="es_urgente">Es urgente</label>
<select id="es_urgente" name="es_urgente" required>
<option value="">Selecciona una opcion</option>
<option value="1" @selected((string) old('es_urgente', $levantamiento->es_urgente === null ? '' : (int) $levantamiento->es_urgente) === '1')>Si</option>
<option value="0" @selected((string) old('es_urgente', $levantamiento->es_urgente === null ? '' : (int) $levantamiento->es_urgente) === '0')>No</option>
</select><label for="observaciones_urgencia">Observaciones</label><textarea id="observaciones_urgencia" name="observaciones_urgencia" rows="3" maxlength="3000">{{ old('observaciones_urgencia', $levantamiento->observaciones_urgencia) }}</textarea></div>
<div><label for="tiempo_aproximado">Tiempo aproximado</label>
<select id="tiempo_aproximado" name="tiempo_aproximado" required>
<option value="">Selecciona una opcion</option>
<option value="1 a 4 dias" @selected((string) old('tiempo_aproximado', $levantamiento->tiempo_aproximado === null ? '' : $levantamiento->tiempo_aproximado) === '1 a 4 dias')>1 a 4 dias</option>
<option value="5 a 10 dias" @selected((string) old('tiempo_aproximado', $levantamiento->tiempo_aproximado === null ? '' : $levantamiento->tiempo_aproximado) === '5 a 10 dias')>5 a 10 dias</option>
</select><label for="observaciones_tiempo">Observaciones</label><textarea id="observaciones_tiempo" name="observaciones_tiempo" rows="3" maxlength="3000">{{ old('observaciones_tiempo', $levantamiento->observaciones_tiempo) }}</textarea></div>
<div><label for="representa_riesgo">Representa riesgo</label>
<select id="representa_riesgo" name="representa_riesgo" required>
<option value="">Selecciona una opcion</option>
<option value="1" @selected((string) old('representa_riesgo', $levantamiento->representa_riesgo === null ? '' : (int) $levantamiento->representa_riesgo) === '1')>Si</option>
<option value="0" @selected((string) old('representa_riesgo', $levantamiento->representa_riesgo === null ? '' : (int) $levantamiento->representa_riesgo) === '0')>No</option>
</select><label for="observaciones_riesgo">Observaciones</label><textarea id="observaciones_riesgo" name="observaciones_riesgo" rows="3" maxlength="3000">{{ old('observaciones_riesgo', $levantamiento->observaciones_riesgo) }}</textarea></div>
</div>
<small>Si representa riesgo, describe el riesgo en sus observaciones.</small>
<label for="diagnostico_tecnico">Diagnostico tecnico</label><textarea id="diagnostico_tecnico" name="diagnostico_tecnico" rows="5" required minlength="10" maxlength="5000">{{ old('diagnostico_tecnico', $levantamiento->diagnostico_tecnico) }}</textarea>
<label for="materiales_utilizar">Materiales por utilizar</label><textarea id="materiales_utilizar" name="materiales_utilizar" rows="4" maxlength="5000">{{ old('materiales_utilizar', $levantamiento->materiales_utilizar) }}</textarea>
<div class="actions"><button type="submit">Guardar borrador</button><a class="btn secondary" href="{{ route('levantamientos.index') }}">Cancelar</a></div>
</form></section>
</main>
</body>
</html>
