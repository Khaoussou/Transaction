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
        $numbAccountClient = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $expediteur)->numero_compte ?? null;
        $numbAccountDest = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $destinataire)->numero_compte ?? null;
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
                $this->depot($destId, $montant, $newTransaction);
                return response()->json("Transaction réussi !");
            }
        } elseif ($type == "depot") {
            $this->depot($destId, $montant, $newTransaction);
            return response()->json("Transaction réussi !");
        } elseif ($type == "retrait") {
            if ($montant > Compte::where("client_id", $clientId)->first()->solde) {
                return response()->json("Vous ne pouvez pas retirer ce montant !");
            } elseif ($fournisseur == "om" || "wv") {
                $this->retrait($newTransaction, $clientId, $montant, $fraisOmWv);
                return response()->json("Transaction réussi !");
            } elseif ($fournisseur == "cb") {
                $this->retrait($newTransaction, $clientId, $montant, $fraisCb);
                return response()->json("Transaction réussi !");
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
    }

    public function retrait($newTransaction, $clientId, $montant, $frais)
    {
        DB::beginTransaction();
        Transaction::create($newTransaction);
        $newSolde = Compte::where("client_id", $clientId)->first()->solde - ($montant + $frais);
        Compte::where("client_id", $clientId)->update(["solde" => $newSolde]);
        DB::commit();
    }

    public function name($numero)
    {
        $transact = new Transaction();
        $numb = $transact->getDataByPhone($numero)->prenom;
        return response()->json($numb);
    }

    public function transfert(Request $request)
    {
        $destinataire = $request->destinataire;
        $montant = $request->montant;
        $fournisseur = $request->fournisseur;
        $expediteur = $request->expediteur;
        $destinataire = $request->destinataire;
        $type = $request->type;
        $haveAccount = Compte::where("client_id", $expediteur)->first();
        if ($montant <= 0) {
            return "Transfert impossible !";
        } elseif ($fournisseur !== "Wari" && !$haveAccount) {
            return "Cet utilisateur n'a pas de compte !";
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
        $expediteur = Client::find($request->expediteur)->telephone;
        $destinataire = Compte::find($request->destinataire);
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
