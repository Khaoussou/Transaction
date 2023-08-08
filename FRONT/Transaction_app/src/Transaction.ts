export interface Transaction {
    destinataire: string,
    expediteur: string,
    type: string,
    montant: string,
    fournisseur: string,
    code: string,
}

export interface User {
    nom: string,
    prenom: string,
    telephone: string,
}