<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $empresas = [
            [
                'nombre' => 'Lioren',
                'slug' => 'lioren',
                'descripcion' => 'Sistema de gestión empresarial Lioren',
                'disponible' => true,
            ],
            [
                'nombre' => 'Bsale',
                'slug' => 'bsale',
                'descripcion' => 'Plataforma de ventas y facturación Bsale',
                'disponible' => false,
            ],
            [
                'nombre' => 'Nubox',
                'slug' => 'nubox',
                'descripcion' => 'Sistema contable y de facturación Nubox',
                'disponible' => false,
            ],
            [
                'nombre' => 'Mercado Libre',
                'slug' => 'mercado-libre',
                'descripcion' => 'Marketplace Mercado Libre',
                'disponible' => false,
            ],
        ];

        foreach ($empresas as $empresa) {
            Empresa::create($empresa);
        }
    }
}
