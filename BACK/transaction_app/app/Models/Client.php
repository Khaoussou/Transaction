<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $guarded = [
        "id"
    ];
    public function compte(): HasMany
    {
        return $this->hasMany(Compte::class);
    }
    public function getDataByPhone($phone)
    {
        return Client::where("telephone", $phone)
            ->first();
    }
}
