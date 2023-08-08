<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientPostRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = new Client();
        return ClientResource::collection($client->with("compte")->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientPostRequest $request)
    {
        $newClient = [
            "nom" => strtoupper($request->nom),
            "prenom" => $request->prenom,
            "telephone" => $request->telephone,
        ];
        return response()->json([
            "message" => "Insertion réussie !",
            "data" => Client::create($newClient),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
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
