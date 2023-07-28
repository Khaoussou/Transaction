<?php

namespace Database\Seeders;

use App\Models\Compte;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $compte = [
            ["numero_compte" => "WV_772673167", "solde" => 5000, "client_id" => 3],
            ["numero_compte" => "OM_770854519", "solde" => 7500, "client_id" => 1],
        ];

        Compte::insert($compte);
    }
}
