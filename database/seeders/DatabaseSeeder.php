<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear los roles
        $roles = [
            'Admin',
            'Propietario',
            'Vendedor',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Obtener el rol Admin
        $adminRole = Role::where('name', 'Admin')->first();

        // Crear el usuario Admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'estado' => true, // Siempre activo
        ]);
    }
}
