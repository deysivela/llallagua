<?php

use Illuminate\Database\Seeder;
use Caffeinated\Shinobi\Models\Permission;
use Caffeinated\Shinobi\Models\Role;

class SaludPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permisos = [
            ['name' => 'Ver módulo Salud',          'slug' => 'salud.index',       'description' => 'Lista y accede al módulo de salud'],
            ['name' => 'Editar establecimientos',   'slug' => 'salud.edit',        'description' => 'Edita datos de establecimientos de salud'],
            ['name' => 'Eliminar establecimientos', 'slug' => 'salud.destroy',     'description' => 'Elimina establecimientos de salud'],
            ['name' => 'Gestionar cronogramas',     'slug' => 'salud.cronogramas', 'description' => 'Sube y gestiona cronogramas del módulo salud'],
            ['name' => 'Ver auditoría Salud',       'slug' => 'salud.auditoria',   'description' => 'Consulta el registro de auditoría del módulo salud'],
        ];

        $ids = [];

        foreach ($permisos as $permiso) {
            $registro = Permission::firstOrCreate(
                ['slug' => $permiso['slug']],
                [
                    'name'        => $permiso['name'],
                    'description' => $permiso['description'],
                ]
            );

            $ids[$permiso['slug']] = $registro->id;
        }

        $admin = Role::firstOrCreate(
            ['slug' => 'admin_salud'],
            [
                'name'        => 'Administrador de Salud',
                'description' => 'Administra todos los establecimientos, cronogramas y la auditoría del módulo Salud.',
                'special'     => null,
            ]
        );
        $admin->permissions()->sync(array_values($ids));

        $responsable = Role::firstOrCreate(
            ['slug' => 'responsable_establecimiento'],
            [
                'name'        => 'Responsable de establecimiento de salud',
                'description' => 'Solo puede ver y editar el establecimiento de salud que tiene asignado.',
                'special'     => null,
            ]
        );
        $responsable->permissions()->sync([
            $ids['salud.index'],
            $ids['salud.edit'],
            $ids['salud.cronogramas'],
        ]);
    }
}
