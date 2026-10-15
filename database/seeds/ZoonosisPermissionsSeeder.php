<?php

use Illuminate\Database\Seeder;
use Caffeinated\Shinobi\Models\Permission;
use Caffeinated\Shinobi\Models\Role;

class ZoonosisPermissionsSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name' => 'Navegar Zoonosis',
                'slug' => 'zoonosis.index',
                'description' => 'Lista actividades y cronogramas de Zoonosis',
            ],
            [
                'name' => 'Crear Zoonosis',
                'slug' => 'zoonosis.create',
                'description' => 'Sube actividades y cronogramas de Zoonosis',
            ],
            [
                'name' => 'Ver detalle Zoonosis',
                'slug' => 'zoonosis.show',
                'description' => 'Ve el detalle de una actividad de Zoonosis',
            ],
            [
                'name' => 'Editar Zoonosis',
                'slug' => 'zoonosis.edit',
                'description' => 'Edita y publica actividades de Zoonosis',
            ],
            [
                'name' => 'Eliminar Zoonosis',
                'slug' => 'zoonosis.destroy',
                'description' => 'Elimina actividades de Zoonosis',
            ],
        ];

        $permissionIds = [];
        foreach ($items as $item) {
            $permission = Permission::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                ]
            );
            $permissionIds[] = $permission->id;
        }

        $role = Role::firstOrCreate(
            ['slug' => 'editor-zoonosis'],
            [
                'name' => 'Editor Zoonosis',
                'description' => 'Solo puede administrar actividades y cronogramas de la Unidad de Zoonosis',
                'special' => null,
            ]
        );

        $role->permissions()->sync($permissionIds);
    }
}
