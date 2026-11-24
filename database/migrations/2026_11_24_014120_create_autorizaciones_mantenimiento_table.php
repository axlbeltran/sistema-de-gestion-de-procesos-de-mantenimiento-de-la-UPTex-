
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autorizaciones_mantenimiento', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('solicitud_id');

            $table->integer('autorizador_id');

            $table->enum('etapa', [
                'Autorizacion Inicial',
                'Visto Bueno Tecnico',
                'Autorizacion Orden',
                'Liberacion'
            ]);

            $table->enum('decision', [
                'Aprobada',
                'Rechazada'
            ]);

            $table->text('observaciones')->nullable();

            $table->timestamp('fecha_autorizacion')
                ->useCurrent();

            $table->foreign('solicitud_id')
                ->references('id')
                ->on('solicitudes_mantenimiento')
                ->restrictOnDelete();

            $table->foreign('autorizador_id')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->index([
                'solicitud_id',
                'etapa'
            ], 'idx_autorizacion_solicitud_etapa');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizaciones_mantenimiento');
    }
};
