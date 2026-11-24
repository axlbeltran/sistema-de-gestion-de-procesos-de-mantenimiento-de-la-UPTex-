@include('levantamientos.formulario', [
    'titulo' => 'Registrar levantamiento tecnico',
    'accion' => route('levantamientos.store', $solicitud->id),
    'esEdicion' => false
])
