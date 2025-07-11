<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Roles>
 */
class RolesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roleNames = [
            'Admin Lab',
            'Kepala Lab',
            'Manajer Teknis',
            'Analis Senior',
            'Analis Junior',
            'Staff Admin',
            'Petugas Sampling',
            'Quality Control'
        ];

        $roleName = fake()->unique()->randomElement($roleNames);
        static $counter = 1;
        
        // Dashboard view mapping based on role
        $dashboardViews = [
            'Admin Lab' => 'admin.dashboard',
            'Kepala Lab' => 'kepala.dashboard',
            'Manajer Teknis' => 'manajer.dashboard',
            'Analis Senior' => 'analis.dashboard',
            'Analis Junior' => 'analis.dashboard',
            'Staff Admin' => 'admin.dashboard',
            'Petugas Sampling' => 'petugas.dashboard',
            'Quality Control' => 'qc.dashboard'
        ];

        return [
            'name' => $roleName,
            'guard_name' => 'web',
            'dashboard_view' => $dashboardViews[$roleName] ?? null,
        ];
    }

    /**
     * Configure the factory with basic permissions.
     */
    public function withBasicPermissions(): static
    {
        return $this->afterCreating(function ($role) {
            // Get or create basic permissions based on role name
            $basicPermissions = collect([
                'lihat-pengujian',
                'lihat-hasil_uji',
                'lihat-pengajuan',
                'detail-pengajuan'
            ]);
            
            $role->syncPermissions($basicPermissions);
        });
    }
}
