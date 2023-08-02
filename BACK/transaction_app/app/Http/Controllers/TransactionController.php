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
        $fraisCb = ($montant * 5) / 100;
        $fournisseur = $request->fournisseur;
        $expediteur = explode("_", $request->expediteur)[count(explode("_", $request->expediteur)) - 1];
        $destinataire = explode("_", $request->destinataire)[count(explode("_", $request->destinataire)) - 1];
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
                return response()->json("Transaction réussi votre code a été généré !");
            } else {
                return $this->depot($destId, $montant, $newTransaction);
            }
        } elseif ($clientId !== $destId && ($type == "transfert-simple" || $type == "transfert-avec-code" || $type == "transfert-immediat")) {
            return $this->transfert($destinataire, $expediteur, $montant, $fournisseur, $newTransaction, $fraisOmWv, $type);
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
        $expediteur = explode("_", $numero);
        if (strlen($numero) == 12) {
            if (count($expediteur) > 0 && !$transact->getIdByNumero($numero)) {
                return response()->json("Ce compte utilisateur n'existe pas !");
            }
        }
        $phone = $expediteur[count($expediteur) - 1];
        $client = $transact->getDataByPhone($phone);
        if (strlen($phone) == 9 && !$client) {
            return response()->json("Cet utilisateur n'existe pas !");
        }
        if (strlen($phone) == 9) {
            $numb = $client->prenom . " " . $client->nom;
            return response()->json($numb);
        }
    }
    public function transfert($destinataire, $expediteur, $montant, $fournisseur, $newTransaction, $frais, $type)
    {
        $transact = new Transaction();
        $idDest = $transact->getDataByPhone($destinataire)->id;
        $accountExist = Compte::where("client_id", $idDest)->first();
        $numbAccountClient = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $expediteur);
        $numbAccountDest = $transact->getIdByNumero(strtoupper($fournisseur) . "_" . $destinataire);
        if (!$accountExist && $type == "transfert-avec-code") {
            if ($montant > $numbAccountClient->solde) {
                return response()->json("Solde insuffisant !");
            } else {
                $code = $transact->randomCode(25);
                $newTransaction["code"] = $code;
                $newTransaction["destinataire_id"] = $idDest;
                $this->retrait($newTransaction, $numbAccountClient->client_id, $montant, $frais);
                return response()->json("Transaction réussi votre code a été généré !");
            }
        }
        if (!$numbAccountClient || !$numbAccountDest) {
            return response()->json("Impossible de faire cette transaction vos fournisseur ne correspondent pas !");
        } else {
            if ($montant > $numbAccountClient->solde) {
                return response()->json("Solde insuffisant !");
            } elseif ($type == "transfert-simple") {
                $this->retrait($newTransaction, $numbAccountClient->client_id, $montant, $frais);
                $this->depot($numbAccountDest->client_id, $montant, $newTransaction);
                return response()->json("Transaction réussi !");
            } elseif ($type == "transfert-immediat") {
                $code = $transact->randomCode(30);
                $newTransaction["code"] = $code;
                $this->retrait($newTransaction, $numbAccountClient->client_id, $montant, $frais);
                $this->depot($numbAccountDest->client_id, $montant, $newTransaction);
                return response()->json("Transaction réussi veuillez retirer l'argent dans les prochaines 24h merci !");
            } elseif ($type == "transfert-avec-code") {
                return response()->json("Désolé cette transaction ne peut marcher, veuillez faire une transaction simple svp !");
            }
        }
    }
    public function transact($numero)
    {
        $transact = new Transaction();
        $phone = explode("_", $numero)[count(explode("_", $numero)) - 1];
        $idClient = $transact->getDataByPhone($phone)->id;
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
