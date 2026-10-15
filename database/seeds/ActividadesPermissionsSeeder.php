<?php

use Illuminate\Database\Seeder;
use Caffeinated\Shinobi\Models\Permission;
use Caffeinated\Shinobi\Models\Role;

class ActividadesPermissionsSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name' => 'Navegar Actividades',
                'slug' => 'actividad.index',
                'description' => 'Lista actividades formativas del municipio',
            ],
            [
                'name' => 'Crear Actividades',
                'slug' => 'actividad.create',
                'description' => 'Sube actividades formativas del municipio',
            ],
            [
                'name' => 'Ver detalle Actividades',
                'slug' => 'actividad.show',
                'description' => 'Ve el detalle de una actividad formativa',
            ],
            [
                'name' => 'Editar Actividades',
                'slug' => 'actividad.edit',
                'description' => 'Edita y publica actividades formativas',
            ],
            [
                'name' => 'Eliminar Actividades',
                'slug' => 'actividad.destroy',
                'description' => 'Elimina actividades formativas',
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
            ['slug' => 'editor-actividades'],
            [
                'name' => 'Editor Actividades',
                'description' => 'Solo puede administrar actividades formativas del municipio',
                'special' => null,
            ]
        );

        $role->permissions()->sync($permissionIds);
    }
}
