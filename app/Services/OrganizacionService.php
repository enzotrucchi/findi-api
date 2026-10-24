<?php

namespace App\Services;

use App\DTOs\Organizacion\OrganizacionDTO;
use App\Models\Organizacion;
use InvalidArgumentException;

/**
 * Servicio de Organizaciones
 */
class OrganizacionService
{
    /**
     * Crear una nueva organización.
     *
     * @throws InvalidArgumentException
     */
    public function crear(OrganizacionDTO $dto): Organizacion
    {
        if ($dto->esPrueba && $dto->fechaFinPrueba === null) {
            throw new InvalidArgumentException('Las organizaciones de prueba deben tener una fecha de fin de prueba.');
        }

        return Organizacion::create([
            'nombre' => $this->normalizarNombre($dto->nombre),
            'fecha_alta' => $dto->fechaAlta ?? now()->format('Y-m-d'),
            'es_prueba' => $dto->esPrueba ?? false,
            'fecha_fin_prueba' => $dto->fechaFinPrueba,
            'codigo_acceso' => $this->generarCodigoAcceso(),
        ]);
    }

    public function actualizarNombre(Organizacion $organizacion, string $nombre): Organizacion
    {
        $organizacion->update(['nombre' => $this->normalizarNombre($nombre)]);

        return $organizacion->fresh();
    }

    /**
     * Generar código de acceso alfanumérico de 6 caracteres.
     */
    public function generarCodigoAcceso(): string
    {
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $codigo = '';
        $longitudCaracteres = strlen($caracteres);

        for ($i = 0; $i < 6; $i++) {
            $codigo .= $caracteres[random_int(0, $longitudCaracteres - 1)];
        }

        return $codigo;
    }

    public function regenerarCodigoAcceso(int $id): Organizacion
    {
        $organizacion = Organizacion::findOrFail($id);
        $organizacion->update(['codigo_acceso' => $this->generarCodigoAcceso()]);

        return $organizacion->fresh();
    }

    /**
     * Actualizar código de acceso personalizado para una organización.
     *
     * @throws InvalidArgumentException
     */
    public function actualizarCodigoAcceso(int $id, string $codigo): Organizacion
    {
        $organizacion = Organizacion::findOrFail($id);
        $codigoNormalizado = strtoupper(trim($codigo));

        if (strlen($codigoNormalizado) !== 6 || ! ctype_alnum($codigoNormalizado)) {
            throw new InvalidArgumentException('El código debe tener exactamente 6 caracteres alfanuméricos.');
        }

        $organizacion->update(['codigo_acceso' => $codigoNormalizado]);

        return $organizacion->fresh();
    }

    /**
     * Normalizar nombre (capitalizar cada palabra).
     */
    private function normalizarNombre(string $nombre): string
    {
        return mb_convert_case(mb_strtolower(trim($nombre)), MB_CASE_TITLE, 'UTF-8');
    }
}
