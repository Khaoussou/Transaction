import { save, expediteur_nom, destinataire_nom, exp, des, montants, types, four, transactionTitle, desTitle, container, formControl, destinataire, icone, tableConte, code, codeRetrait, blocModal, blocModal1, btnClose, valider, errorDes, errorExp, errorMont, etat, dropdown, fournisseur, user, ajoutUser, closeAjout, nom, prenom, phone, filtre } from "./dom.js";
import { Transaction, User } from "./Transaction.js";
import { creatingElement, afficheMessage, emptyField, chargerData } from "./function.js";

const Url: string = "http://127.0.0.1:8000/api/";

enum transact {
    om = "#ff4500",
    wv = "#0088ff",
    wr = "#008044",
    cb = "#deb887"
}

async function getData(url: string) {
    const data = await fetch(url);
    const d = await data.json();
    return d;
}

valider.addEventListener("click", () => {
    fetch(Url + "comptes", {
        method: "POST",
        headers: {
            "Content-type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify({ "telephone": exp?.value, "fournisseur": fournisseur?.value })
    }).then(res => res.json().then(data => {
        afficheMessage(data, container)
    }))
    blocModal.style.display = "none";
})

btnClose.addEventListener("click", () => {
    blocModal.style.display = "none";
})

user.addEventListener("click", () => {
    blocModal1.style.display = "block"
})

ajoutUser.addEventListener("click", () => {
    let newUser: User = {
        nom: nom.value,
        prenom: prenom.value,
        telephone: phone.value,
    }
    fetch(Url + "clients", {
        method: "POST",
        headers: {
            "Content-type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(newUser)
    }).then(response => response.json().then(data => {
        afficheMessage(data.message, container)
    }))
    blocModal1.style.display = "none"
    emptyField(formControl)
})

closeAjout.addEventListener("click", () => {
    blocModal1.style.display = "none"
})

icone.style.display = "none";

exp.addEventListener("input", () => {
    let expediteur: string = exp?.value
    let data = getData(Url + "name/" + expediteur)
    data.then(res => {
        if (res == "Cet utilisateur n'existe pas !" || res == "Ce compte utilisateur n'existe pas !") {
            afficheMessage(res, errorExp)
            expediteur_nom.value = ""
            dropdown.style.display = "none"
            icone.style.display = "none"
        } else {
            let bool = getData(Url + "bool/" + expediteur)
            bool.then(res => {
                if (res == "ok") {
                    dropdown.style.display = "block"
                } else {
                    etat.innerHTML = ""
                    let option = creatingElement("option", { value: "" }, "Faites votre choix");
                    let option1 = creatingElement("option", { value: "add" }, "Ouverture de compte");
                    etat.append(option, option1)
                }
            })
            expediteur_nom.value = res
            dropdown.style.display = "block"
            icone.style.display = "block"
        }
    }).catch(error => {
        afficheMessage("Client", expediteur_nom)
    })
})

etat.addEventListener("change", () => {
    let expediteur: string = exp?.value
    if (etat.value != "1" && etat.value != "0" && etat.value != "2") {
        blocModal.style.display = "block"
    } else {
        fetch(Url + "etat/" + expediteur, {
            method: "PUT",
            headers: {
                "Content-type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ "etat": etat.value })
        }).then(res => res.json().then(data => {
            afficheMessage(data, container)
        }))
    }
})

icone.addEventListener("click", () => {
    let expediteur: string = exp?.value
    let data = getData(Url + "transact/" + expediteur)
    let responses: any
    data.then(response => {
        chargerData(response)
        filtre.addEventListener("click", () => {
            if (filtre.value == "date") {
                responses = response.sort((a: any, b: any) => new Date(a.date).getTime() - new Date(b.date).getTime())
                chargerData(responses)
            } else if (filtre.value == "mont") {
                responses = response.sort((a: any, b: any) => (a.montant) - (b.montant))
                chargerData(responses)
            }
            responses.forEach((res: any) => {
                if (res.type == "transfert-simple") {
                    let cancel = document.querySelector(".remove") as HTMLButtonElement
                    let tr = document.querySelector(".annuler") as HTMLElement
                    cancel?.addEventListener("click", () => {
                        fetch(Url + "annulertransact", {
                            method: "POST",
                            headers: {
                                "Content-type": "application/json",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                "exp": res.exp,
                                "des": res.des,
                                "montant": res.montant,
                            })
                        }).then(response => response.json().then(data => {
                            afficheMessage(data, container)
                            cancel.style.backgroundColor = "red"
                            tr.removeChild(cancel)
                        }))
                    })
                }
            })
        })
        tableConte.classList.toggle("active")
    })
})

four.addEventListener("change", () => {
    transactionTitle.style.color = "white"
    desTitle.style.color = "white"
    if (four.value == "om") {
        types.innerHTML = ""
        transactionTitle.style.backgroundColor = transact.om
        desTitle.style.backgroundColor = transact.om
        let optionDefault = creatingElement("option", { value: "" }, "Choisis un type de transaction");
        let depot = creatingElement("option", { value: "depot" }, "Dépot");
        let retrait = creatingElement("option", { value: "retrait" }, "Retrait");
        let tranSimple = creatingElement("option", { value: "transfert-simple" }, "Transfert simple");
        let tranAvecCode = creatingElement("option", { value: "transfert-avec-code" }, "Transfert avec code");
        types.append(optionDefault, depot, retrait, tranSimple, tranAvecCode)
    }
    if (four.value == "wv") {
        types.innerHTML = ""
        let optionDefault = creatingElement("option", { value: "" }, "Choisis un type de transaction");
        let depot = creatingElement("option", { value: "depot" }, "Dépot");
        let retrait = creatingElement("option", { value: "retrait" }, "Retrait");
        let tranSimple = creatingElement("option", { value: "transfert-simple" }, "Transfert simple");
        types.append(optionDefault, depot, retrait, tranSimple)
        transactionTitle.style.backgroundColor = transact.wv
        desTitle.style.backgroundColor = transact.wv
    }
    if (four.value == "wr") {
        types.innerHTML = ""
        let optionDefault = creatingElement("option", { value: "" }, "Choisis un type de transaction");
        let depot = creatingElement("option", { value: "depot" }, "Dépot");
        let retrait = creatingElement("option", { value: "retrait" }, "Retrait");
        let tranSimple = creatingElement("option", { value: "transfert-simple" }, "Transfert simple");
        types.append(optionDefault, depot, retrait, tranSimple)
        transactionTitle.style.backgroundColor = transact.wr
        desTitle.style.backgroundColor = transact.wr
    }
    if (four.value == "cb") {
        types.innerHTML = ""
        let optionDefault = creatingElement("option", { value: "" }, "Choisis un type de transaction");
        let depot = creatingElement("option", { value: "depot" }, "Dépot");
        let retrait = creatingElement("option", { value: "retrait" }, "Retrait");
        let tranSimple = creatingElement("option", { value: "transfert-simple" }, "Transfert simple");
        let option = creatingElement("option", { value: "transfert-immediat" }, "Transfert immediat");
        types.append(optionDefault, depot, retrait, tranSimple, option)
        transactionTitle.style.backgroundColor = transact.cb
        desTitle.style.backgroundColor = transact.cb
    }
})

types.addEventListener("change", () => {
    let expediteur: string = exp?.value
    if (types.value == "retrait") {
        montants.addEventListener("input", () => {
            if (montants.value.length >= 3) {
                fetch(Url + "errorRetrait", {
                    method: "POST",
                    headers: {
                        "Content-type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ "exp": exp?.value, "montant": montants?.value })
                }).then(res => res.json().then(data => {
                    if (data != "Montant disponible voici les frais : " + (+montants?.value * 1) / 100 + " FCFA") {
                        errorMont.style.color = "red"
                        montants.classList.toggle("couleur")
                        console.log(montants);
                        errorMont.innerHTML = data
                    } else {
                        errorMont.innerHTML = " "
                        errorMont.style.color = "green"
                        afficheMessage(data, errorMont)
                    }
                }
                ))
            }
        })
        if (four.value == "wr" || four.value == "om") {
            let bool = getData(Url + "bool/" + expediteur)
            bool.then(res => {
                if (res == "ko") {
                    code.style.display = "block"
                    montants.disabled = true
                    destinataire.style.display = "none"
                } else {
                    code.style.display = "none"
                    montants.disabled = false
                    destinataire.style.display = "none"
                }
            })
        } else {
            destinataire.style.display = "none"
        }
    } else {
        code.style.display = "none"
        montants.disabled = false
        destinataire.style.display = "block"
    }
})

des.addEventListener("input", () => {
    let destinataire: string = des?.value
    let data = getData(Url + "name/" + destinataire)
    data.then(res => {
        if (res == "Cet utilisateur n'existe pas !" || res == "Ce compte utilisateur n'existe pas !") {
            afficheMessage(res, errorDes)
            destinataire_nom.value = ""
        } else {
            destinataire_nom.value = res
        }
    }).catch(error => {
        afficheMessage("Client", destinataire_nom)
    })
})

save.addEventListener("click", () => {
    let newTransact: Transaction = {
        destinataire: des?.value,
        expediteur: exp?.value,
        type: types?.value,
        montant: montants?.value,
        fournisseur: four?.value,
        code: codeRetrait?.value,
    }
    fetch(Url + "transaction", {
        method: "POST",
        headers: {
            "Content-type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(newTransact)
    }).then(res => res.json().then(data => {
        afficheMessage(data, container)
    }
    ))
    dropdown.style.display = "none"
    emptyField(formControl)
})