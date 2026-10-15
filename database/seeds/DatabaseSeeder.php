<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CulturaPermissionsSeeder::class);
        $this->call(ZoonosisPermissionsSeeder::class);
        $this->call(ActividadesPermissionsSeeder::class);
        // $this->call(CategoriesTableSeeder::class);
        // $this->call(DocumentsTableSeeder::class);
    }
}
