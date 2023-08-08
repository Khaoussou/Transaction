<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $guarded = [
        "id"
    ];
    public function getIdByNumero($numero)
    {
        return Compte::where("numero_compte", $numero)
            ->first();
    }
    public function getAccountClient($id)
    {
        return Compte::where("client_id", $id)
            ->first();
    }
    public function changeState($id, $state)
    {
        return Compte::where("client_id", $id)
            ->update(["etat" => $state]);
    }
}
