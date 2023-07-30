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

    public function depot(TransactionRequest $request)
    {
        $transact = new Transaction();
        $destinataire = $request->destinataire;
        $montant = $request->montant;
        $fournisseur = $request->fournisseur;
        $expediteur = $request->expediteur;
        $destinataire = $request->destinataire;
        $type = $request->type;
        $destId = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $destinataire)->client_id;
        $expId = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $expediteur)->client_id;
        if ($montant < 500) {
            return response()->json("Veuillez donner un montant supérieur ou egale à 500 !");
        } elseif ($fournisseur == "wr" && $montant < 1000) {
            return response()->json("Veuillez donner un montant supérieur ou egale à 1000 !");
        } elseif ($fournisseur == "cb" && $montant < 10000) {
            return response()->json("Veuillez donner un montant supérieur ou egale à 10000 !");
        } elseif ($fournisseur !== "cb" && $montant > 1000000) {
            return response()->json("Impossible de faire cette transaction votre fournisseur ne l'accepte pas !");
        } elseif ($destId !== $expId) {
            return response()->json("Les données ne correspondent pas !");
        } else {
            $newTransaction = [
                "expediteur_id" => $expId,
                "destinataire_id" => $destId,
                "date" => Carbon::now(),
                "montant" => $montant,
                "type" => $type
            ];
            DB::beginTransaction();
            Transaction::create($newTransaction);
            $newSolde = Compte::where("client_id", $destId)->first()->solde + $montant;
            Compte::where("client_id", $destId)->update(["solde" => $newSolde]);
            DB::commit();
            return response()->json("Transaction réussi !");
        }
    }

    public function name($numero)
    {
        $transact = new Transaction();
        $numb = $transact->getNameByPhone($numero)->prenom;
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
        $idClient = $transact->getNameByPhone($numero)->id;
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
