<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [
        "id"
    ];
    public function getDataByPhone($phone)
    {
        return Client::where("telephone", $phone)
            ->first();
    }
    public function getIdByNumero($numero)
    {
        return Compte::where("numero_compte", $numero)
            ->first();
    }
    public function getTransactById($id)
    {
        return Transaction::where("expediteur_id", $id)
            ->get();
    }
    public function randomCode($length)
    {
        $number = "0123456789";
        return substr(str_shuffle(str_repeat($number, $length)), 0, $length);
    }
}
