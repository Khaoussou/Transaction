<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function allTransact(Request $request)
    {
        $transact = new Transaction();
        $montant = $request->montant;
        $fraisOmWv = ($montant * 1) / 100;
        $fraisWr = ($montant * 2) / 100;
        $fraisCb = ($montant * 5) / 100;
        $fournisseur = $request->fournisseur;
        $expediteur = $request->expediteur;
        $destinataire = $request->destinataire;
        $clientId = $transact->getDataByPhone($expediteur)->id;
        $destWrId = $transact->getDataByPhone($destinataire)->id ?? null;
        $type = $request->type;
        $destId = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $destinataire)->client_id ?? null;
        $newTransaction = [
            "expediteur_id" => $clientId,
            "destinataire_id" => $destId,
            "date" => Carbon::now(),
            "montant" => $montant,
            "type" => $type,
            "code" => null,
        ];
        if ($montant < 500) {
            return response()->json("Veuillez donner un montant supérieur ou egale à 500 !");
        } elseif ($fournisseur == "wr" && $montant < 1000) {
            return response()->json("Veuillez donner un montant supérieur ou egale à 1000 !");
        } elseif ($fournisseur == "cb" && $montant < 10000) {
            return response()->json("Veuillez donner un montant supérieur ou egale à 10000 !");
        } elseif ($fournisseur !== "cb" && $montant > 1000000) {
            return response()->json("Impossible de faire cette transaction votre fournisseur ne l'accepte pas !");
        } elseif ($clientId !== $destId && $type == "depot") {
            if ($fournisseur == "wr") {
                $newTransaction["destinataire_id"] = $destWrId;
                $code = $transact->randomCode(15);
                $newTransaction["code"] = $code;
                Transaction::create($newTransaction);
                return response()->json("Transaction réussi !");
            } else {
                return $this->depot($destId, $montant, $newTransaction);
            }
        } elseif ($clientId !== $destId && $type == "transfert-simple") {
            return $this->transfert($destinataire, $expediteur, $montant, $fournisseur, $newTransaction, $fraisOmWv);
        } elseif ($type == "depot") {
            return $this->depot($destId, $montant, $newTransaction);
        } elseif ($type == "retrait") {
            if ($montant > Compte::where("client_id", $clientId)->first()->solde) {
                return response()->json("Vous ne pouvez pas retirer ce montant !");
            } elseif ($fournisseur == "om" || "wv") {
                return $this->retrait($newTransaction, $clientId, $montant, $fraisOmWv);
            } elseif ($fournisseur == "cb") {
                return $this->retrait($newTransaction, $clientId, $montant, $fraisCb);
            }
        }
    }
    public function depot($destId, $montant, $newTransaction)
    {
        DB::beginTransaction();
        Transaction::create($newTransaction);
        $newSolde = Compte::where("client_id", $destId)->first()->solde + $montant;
        Compte::where("client_id", $destId)->update(["solde" => $newSolde]);
        DB::commit();
        return response()->json("Transaction réussi !");
    }

    public function retrait($newTransaction, $clientId, $montant, $frais)
    {
        DB::beginTransaction();
        Transaction::create($newTransaction);
        $newSolde = Compte::where("client_id", $clientId)->first()->solde - ($montant + $frais);
        Compte::where("client_id", $clientId)->update(["solde" => $newSolde]);
        DB::commit();
        return response()->json("Transaction réussi !");
    }

    public function name($numero)
    {
        $transact = new Transaction();
        $numb = $transact->getDataByPhone($numero)->prenom;
        return response()->json($numb);
    }

    public function transfert($destinataire, $expediteur, $montant, $fournisseur, $newTransaction, $frais)
    {
        $transact = new Transaction();
        $numbAccountClient = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $expediteur);
        $numbAccountDest = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $destinataire);
        if (!$numbAccountClient || !$numbAccountDest) {
            return response()->json("Impossible de faire cette transaction vos fournisseur ne correspondent pas !");
        } else {
            if ($montant > $numbAccountClient->solde) {
                return response()->json("Solde insuffisant !");
            } else {
                $this->retrait($newTransaction, $numbAccountClient->client_id, $montant, $frais);
                $this->depot($numbAccountDest->client_id, $montant, $newTransaction);
                return response()->json("Transaction réussi !");
            }
        }
    }

    public function transact($numero)
    {
        $transact = new Transaction();
        $idClient = $transact->getDataByPhone($numero)->id;
        $transactions = $transact->getTransactById($idClient);
        $valeur = [];
        for ($i = 0; $i < count($transactions); $i++) {
            $valeur[] = [
                "montant" => $transactions[$i]->montant,
                "type" => $transactions[$i]->type,
                "date" => $transactions[$i]->date,
            ];
        }
        return response()->json($valeur);
    }
    public function index()
    {
        return Transaction::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
