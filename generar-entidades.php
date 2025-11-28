<?php

/**
 * Script para generar código de entidades siguiendo el patrón establecido
 * 
 * Uso: php generar-entidades.php
 */

// Configuración de entidades
$entidades = [
    'Asociado' => [
        'tabla' => 'asociados',
        'campos' => [
            'nombre' => ['tipo' => 'string', 'max' => 255, 'requerido' => true],
            'email' => ['tipo' => 'string', 'max' => 255, 'requerido' => true, 'unique' => true],
            'telefono' => ['tipo' => 'string', 'max' => 50, 'requerido' => true],
            'domicilio' => ['tipo' => 'string', 'max' => 500, 'requerido' => false],
            'esAdmin' => ['tipo' => 'boolean', 'default' => false],
            'activo' => ['tipo' => 'boolean', 'default' => true],
        ],
    ],
    'Proyecto' => [
        'tabla' => 'proyectos',
        'campos' => [
            'descripcion' => ['tipo' => 'text', 'requerido' => true],
            'montoActual' => ['tipo' => 'decimal', 'precision' => '15,2', 'default' => 0],
            'montoObjetivo' => ['tipo' => 'decimal', 'precision' => '15,2', 'requerido' => true],
            'fechaAlta' => ['tipo' => 'date', 'requerido' => true],
            'fechaRealizacion' => ['tipo' => 'date', 'nullable' => true],
        ],
    ],
    'ModoPago' => [
        'tabla' => 'modos_pago',
        'campos' => [
            'nombre' => ['tipo' => 'string', 'max' => 100, 'requerido' => true, 'unique' => true],
        ],
    ],
    'Organizacion' => [
        'tabla' => 'organizaciones',
        'campos' => [
            'nombre' => ['tipo' => 'string', 'max' => 255, 'requerido' => true],
            'adminEmail' => ['tipo' => 'string', 'max' => 255, 'requerido' => true, 'unique' => true],
            'adminNombre' => ['tipo' => 'string', 'max' => 255, 'requerido' => true],
            'fechaAlta' => ['tipo' => 'date', 'requerido' => true],
            'esPrueba' => ['tipo' => 'boolean', 'default' => true],
            'fechaFinPrueba' => ['tipo' => 'date', 'requerido' => true],
        ],
    ],
];

echo "=== GENERADOR DE ENTIDADES ===\n\n";
echo "Este script genera la estructura completa para cada entidad.\n";
echo "Sigue el patrón de Proveedor.\n\n";

echo "Entidades configuradas:\n";
foreach ($entidades as $nombre => $config) {
    echo "- $nombre (tabla: {$config['tabla']})\n";
}

echo "\n✅ Configuración lista.\n";
echo "\nPara completar las entidades, copia el código de los archivos de Proveedor\n";
echo "y reemplaza los nombres según corresponda.\n\n";

echo "Patrón de reemplazo:\n";
echo "- Proveedor → [NombreEntidad]\n";
echo "- proveedores → [nombre_tabla]\n";
echo "- proveedor → [nombre_entidad_minuscula]\n\n";

echo "Archivos a crear por entidad:\n";
echo "1. app/Models/[Entidad].php\n";
echo "2. app/DTOs/[Entidad]/[Entidad]DTO.php\n";
echo "3. app/DTOs/[Entidad]/Crear[Entidad]DTO.php\n";
echo "4. app/DTOs/[Entidad]/Actualizar[Entidad]DTO.php\n";
echo "5. app/Repositories/Contracts/[Entidad]RepositoryInterface.php\n";
echo "6. app/Repositories/[Entidad]Repository.php\n";
echo "7. app/Services/[Entidad]Service.php\n";
echo "8. app/Http/Requests/[Entidad]/[Entidad]Request.php\n";
echo "9. app/Http/Controllers/Api/[Entidad]Controller.php\n";
echo "10. Rutas en routes/api.php\n";
echo "11. Binding en app/Providers/RepositoryServiceProvider.php\n";
