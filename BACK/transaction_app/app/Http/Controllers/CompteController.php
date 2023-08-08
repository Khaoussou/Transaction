<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComptePostRequest;
use App\Models\Client;
use App\Models\Compte;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComptePostRequest $request)
    {
        $client = new Client();
        $compte = new Compte();
        $telephone = $request->telephone;
        $fournisseur = $request->fournisseur;
        $clientExist = $client->getDataByPhone($telephone);
        $numAccount = strtoupper($fournisseur) . "_" . $telephone;
        $accountExist = $compte->getIdByNumero($numAccount);
        if (!$clientExist) {
            return response()->json("Impossible de creer ce compte car le client n'existe pas !");
        }
        if ($accountExist) {
            return response()->json("Ce compte existe deja !");
        } else {
            Compte::create([
                "numero_compte" => $numAccount,
                "client_id" => $clientExist->id,
                "solde" => 0
            ]);
            return response()->json("Insertion réussie !");
        }
    }
    public function updateState(Request $request, $numero)
    {
        $compte = new Compte();
        $client = new Client();
        if (strlen($numero) == 12) {
            $account = $compte->getIdByNumero($numero);
            if ($account) {
                Compte::where("id", $account->id)->update(["etat" => $request->etat]);
                return response()->json("L'etat de votre compte a changé !");
            }
        }
        if (strlen($numero) == 9) {
            $clientExist = $client->getDataByPhone($numero)->id;
            $account = $compte->getAccountClient($clientExist);
            if ($account) {
                Compte::where("id", $account->id)->update(["etat" => $request->etat]);
                return response()->json("L'etat de votre compte a changé !");
            }
        }
    }
    public function haveAccount($numero)
    {
        $compte = new Compte();
        $client = new Client();
        if (strlen($numero) == 12) {
            $account = $compte->getIdByNumero($numero);
            if ($account) {
                return response()->json("ok");
            }
            return response()->json("ko");
        }
        if (strlen($numero) == 9) {
            $clientExist = $client->getDataByPhone($numero)->id;
            $account = $compte->getAccountClient($clientExist);
            if ($account) {
                return response()->json("ok");
            }
            return response()->json("ko");
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compte $compte)
    {
        return $compte->update(["etat" => $request->etat]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
