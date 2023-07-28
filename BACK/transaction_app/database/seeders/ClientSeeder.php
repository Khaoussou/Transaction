<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = [
            ["nom" => "Diallo", "prenom" => "Khaoussou", "telephone" => "770854519"],
            ["nom" => "Ndiaye", "prenom" => "Khadija", "telephone" => "777967105"],
            ["nom" => "Ly", "prenom" => "Cheikh Tydjane", "telephone" => "772673167"],
        ];

        Client::insert($client);
    }
}
