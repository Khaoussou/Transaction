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
        $compte = new Compte();
        $client = new Client();
        $montant = $request->montant;
        $fraisOmWv = ($montant * 1) / 100;
        $fraisCb = ($montant * 5) / 100;
        $fournisseur = $request->fournisseur;
        $expediteur = explode("_", $request->expediteur)[count(explode("_", $request->expediteur)) - 1];
        $destinataire = explode("_", $request->destinataire)[count(explode("_", $request->destinataire)) - 1];
        $clientId = $client->getDataByPhone($expediteur)->id;
        $destinataireId = $client->getDataByPhone($destinataire)->id ?? null;
        $accountExp = $compte->getAccountClient($clientId);
        $accountDes = $compte->getAccountClient($destinataireId) ?? null;
        $destWrId = $client->getDataByPhone($destinataire)->id ?? null;
        $type = $request->type;
        $destId = $compte->getIdByNumero(strtoupper($fournisseur) . "_" . $destinataire)->client_id ?? null;
        $newTransaction = [
            "expediteur_id" => $clientId,
            "destinataire_id" => $destId,
            "date" => Carbon::now(),
            "montant" => $montant,
            "type" => $type,
            "code" => null,
        ];
        if ($accountExp && $accountDes) {
            if ($accountExp->etat == 2 || $accountDes->etat == 2) {
                return response()->json("Cette transaction est impossible car l'un des deux compte a été fermé !");
            } elseif ($accountExp->etat == 1 && $type != "depot") {
                return response()->json("Cette transaction est impossible car votre compte a été bloqué !");
            }
        }
        if ($accountExp && !$accountDes) {
            if ($accountExp->etat == 2) {
                return response()->json("Cette transaction est impossible car l'un des deux compte a été fermé !");
            } elseif ($accountExp->etat == 1 && $type != "depot") {
                return response()->json("Cette transaction est impossible car votre compte a été bloqué !");
            }
        }
        if (!$accountExp && $type == "retrait" && ($fournisseur == "om" || $fournisseur == "wr")) {
            return $this->retraitAvecCode($newTransaction, $request->code);
        }
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
                return response()->json("Transaction réussi voici votre code de retrait : " . $code);
            } else {
                return $this->depot($destId, $montant, $newTransaction);
            }
        } elseif ($clientId !== $destId && ($type == "transfert-simple" || $type == "transfert-avec-code" || $type == "transfert-immediat")) {
            return $this->transfert($destinataire, $expediteur, $montant, $fournisseur, $newTransaction, $fraisOmWv, $type);
        } elseif ($type == "depot") {
            return $this->depot($destId, $montant, $newTransaction);
        } elseif ($type == "retrait") {
            if ($montant >= Compte::where("client_id", $clientId)->first()->solde) {
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
        return response()->json("Transaction réussi. Votre nouveau solde est de : " . $newSolde . " FCFA");
    }
    public function errorRetrait(Request $request)
    {
        $client = new Client();
        $montant = $request->montant;
        $phone = $request->exp;
        $clientId = $client->getDataByPhone($phone)->id;
        if (strlen($montant) >= 3 && $montant < 500) {
            return response()->json("Veuillez donner un montant supérieur ou egale à 500 !");
        }
        if (strlen($montant) >= 3 && $montant >= Compte::where("client_id", $clientId)->first()->solde) {
            return response()->json("Vous ne pouvez pas retirer ce montant !");
        } else {
            return response()->json("Montant disponible voici les frais : " . (($montant * 1)/100) . " FCFA");
        }
    }
    public function retrait($newTransaction, $clientId, $montant, $frais)
    {
        DB::beginTransaction();
        Transaction::create($newTransaction);
        $newSolde = Compte::where("client_id", $clientId)->first()->solde - ($montant + $frais);
        Compte::where("client_id", $clientId)->update(["solde" => $newSolde]);
        DB::commit();
        return response()->json("Transaction réussi. Votre nouveau solde est de : " . $newSolde . " FCFA");
    }
    public function retraitAvecCode($newTransaction, $code)
    {
        $transaction = new Transaction();
        $clientTrans = $transaction->getTransactByCode($code);
        if ($clientTrans->remove == 1) {
            return response()->json("Ce code de retrait n'est plus valide veuillez donner le bon code svp !");
        }
        Transaction::where("id", $clientTrans->id)->update(["remove" => 1]);
        $newTransaction["montant"] = $clientTrans->montant;
        Transaction::create($newTransaction);
        return response()->json("Vous venez de retirer: " . $clientTrans->montant . " merci de votre fidélité !");
    }
    public function name($numero)
    {
        $compte = new Compte();
        $client = new Client();
        $expediteur = explode("_", $numero);
        if (strlen($numero) == 12) {
            if (count($expediteur) > 0 && !$compte->getIdByNumero($numero)) {
                return response()->json("Ce compte utilisateur n'existe pas !");
            }
        }
        $phone = $expediteur[count($expediteur) - 1];
        $clientExist = $client->getDataByPhone($phone);
        if (strlen($phone) == 9 && !$clientExist) {
            return response()->json("Cet utilisateur n'existe pas !");
        }
        if (strlen($phone) == 9) {
            $numb = $clientExist->prenom . " " . $clientExist->nom;
            return response()->json($numb);
        }
    }
    public function transfert($destinataire, $expediteur, $montant, $fournisseur, $newTransaction, $frais, $type)
    {
        $transact = new Transaction();
        $compte = new Compte();
        $client = new Client();
        $idDest = $client->getDataByPhone($destinataire)->id;
        $accountExist = Compte::where("client_id", $idDest)->first();
        $numbAccountClient = $compte->getIdByNumero(strtoupper($fournisseur) . "_" . $expediteur);
        $numbAccountDest = $compte->getIdByNumero(strtoupper($fournisseur) . "_" . $destinataire);
        if (!$accountExist && $type == "transfert-avec-code") {
            if ($montant >= $numbAccountClient->solde) {
                return response()->json("Solde insuffisant !");
            } else {
                $code = $transact->randomCode(25);
                $newTransaction["code"] = $code;
                $newTransaction["destinataire_id"] = $idDest;
                $this->retrait($newTransaction, $numbAccountClient->client_id, $montant, $frais);
                return response()->json("Transaction réussi voici votre code de retrait : " . $code);
            }
        }
        if (!$numbAccountClient || !$numbAccountDest) {
            return response()->json("Impossible de faire cette transaction vos fournisseur ne correspondent pas !");
        } else {
            if ($montant >= $numbAccountClient->solde) {
                return response()->json("Solde insuffisant !");
            } elseif ($type == "transfert-simple") {
                $this->retrait($newTransaction, $numbAccountClient->client_id, $montant, $frais);
                $this->depot($numbAccountDest->client_id, $montant, $newTransaction);
                $lastTransact = Transaction::latest()->first();
                Transaction::where("id", $lastTransact->id)->delete();
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
        $client = new Client();
        $phone = explode("_", $numero)[count(explode("_", $numero)) - 1];
        $idClient = $client->getDataByPhone($phone)->id;
        $transactions = $transact->getTransactById($idClient);
        $valeur = [];
        for ($i = 0; $i < count($transactions); $i++) {
            $valeur[] = [
                "montant" => $transactions[$i]->montant,
                "type" => $transactions[$i]->type,
                "date" => $transactions[$i]->date,
                "exp" => $transactions[$i]->expediteur_id,
                "des" => $transactions[$i]->destinataire_id,
                "code" => $transactions[$i]->code
            ];
        }
        return response()->json($valeur);
    }
    public function annulerTransact(Request $request)
    {
        $compte = new Compte();
        $transaction = new Transaction();
        $exp = $request->exp;
        $des = $request->des;
        $montant = $request->montant;
        $fraisOmWv = ($montant * 1) / 100;
        $accountDes = $compte->getAccountClient($des);
        $accountExp = $compte->getAccountClient($exp);
        if (!$accountDes) {
            $newSolde = $accountExp->solde + $montant + $fraisOmWv;
            Compte::where("client_id", $exp)->update(["solde" => $newSolde]);
            return response()->json("Votre transaction a été annulé !");
        } else {
            if ($montant > $accountDes->solde) {
                return response()->json("Cette transaction ne peut pas etre annuler !");
            }
            $newSoldeExp = $accountExp->solde + $montant + $fraisOmWv;
            $newSoldeDes = $accountDes->solde - $montant;
            Compte::where("client_id", $exp)->update(["solde" => $newSoldeExp]);
            Compte::where("client_id", $des)->update(["solde" => $newSoldeDes]);
            return response()->json("Votre transaction a été annulé !");
        }
    }
    public function filtrerTransact()
    {
        return "bonjour";
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
