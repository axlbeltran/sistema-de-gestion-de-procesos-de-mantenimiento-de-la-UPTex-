@include('levantamientos.formulario', [
    'titulo' => 'Editar levantamiento tecnico',
    'accion' => route('levantamientos.update', $solicitud->id),
    'esEdicion' => true
])
