
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levantamientos_tecnicos', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('solicitud_id')->unique();

            $table->integer('elaborado_por');

            $table->enum('origen_mantenimiento', [
                'Interno',
                'Externo'
            ]);

            $table->text('observaciones_origen')->nullable();

            $table->enum('tipo_mantenimiento', [
                'Preventivo',
                'Correctivo'
            ]);

            $table->text('observaciones_tipo')->nullable();

            $table->boolean('cuenta_con_insumos');

            $table->text('observaciones_insumos')->nullable();

            $table->boolean('es_urgente');

            $table->text('observaciones_urgencia')->nullable();

            $table->enum('tiempo_aproximado', [
                '1 a 4 dias',
                '5 a 10 dias'
            ]);

            $table->text('observaciones_tiempo')->nullable();

            $table->boolean('representa_riesgo');

            $table->text('observaciones_riesgo')->nullable();

            $table->text('diagnostico_tecnico');

            $table->text('materiales_utilizar')->nullable();

            $table->enum('estado', [
                'Borrador',
                'Pendiente de Revision',
                'Aprobado',
                'Rechazado'
            ])->default('Borrador');

            $table->timestamp('fecha_elaboracion')
                ->useCurrent();

            $table->timestamp('fecha_actualizacion')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->foreign('solicitud_id')
                ->references('id')
                ->on('solicitudes_mantenimiento')
                ->restrictOnDelete();

            $table->foreign('elaborado_por')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->index('estado');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levantamientos_tecnicos');
    }
};
