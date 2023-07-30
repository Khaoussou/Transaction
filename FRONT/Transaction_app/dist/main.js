"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
const Url = "http://127.0.0.1:8000/api/";
const save = document.querySelector("#save");
let expediteur_nom = document.querySelector("#expediteur_nom");
let destinataire_nom = document.querySelector("#destinataire_nom");
let exp = document.querySelector("#expediteur");
let des = document.querySelector("#destinataire");
let montants = document.querySelector("#montant");
let types = document.querySelector("#type_transaction");
let four = document.querySelector("#fournisseur");
let transactionTitle = document.querySelector(".trans");
let desTitle = document.querySelector(".des");
let container = document.querySelector(".container");
var transact;
(function (transact) {
    transact["om"] = "#ff4500";
    transact["wv"] = "#0088ff";
    transact["wr"] = "#008044";
    transact["cb"] = "#deb887";
})(transact || (transact = {}));
function creatingElement(elName, attributs, elementContent) {
    const element = document.createElement(elName);
    for (const cle in attributs) {
        element.setAttribute(cle, attributs[cle]);
    }
    element.textContent = elementContent;
    return element;
}
function afficheMessage(message, container) {
    const mess = creatingElement('div', { class: 'mess dflex jcc aic' }, message);
    container.append(mess);
    setTimeout(() => {
        container.removeChild(mess);
    }, 5000);
}
function getData(url) {
    return __awaiter(this, void 0, void 0, function* () {
        const data = yield fetch(url);
        const d = yield data.json();
        return d;
    });
}
expediteur_nom.addEventListener("focus", () => {
    let expediteur = exp === null || exp === void 0 ? void 0 : exp.value;
    let data = getData(Url + "name/" + expediteur);
    data.then(res => {
        expediteur_nom.value = res;
    });
});
four.addEventListener("change", () => {
    transactionTitle.style.color = "white";
    desTitle.style.color = "white";
    if (four.value == "om") {
        transactionTitle.style.backgroundColor = transact.om;
        desTitle.style.backgroundColor = transact.om;
    }
    if (four.value == "wv") {
        transactionTitle.style.backgroundColor = transact.wv;
        desTitle.style.backgroundColor = transact.wv;
    }
    if (four.value == "wr") {
        transactionTitle.style.backgroundColor = transact.wr;
        desTitle.style.backgroundColor = transact.wr;
    }
    if (four.value == "cb") {
        transactionTitle.style.backgroundColor = transact.cb;
        desTitle.style.backgroundColor = transact.cb;
    }
});
destinataire_nom.addEventListener("focus", () => {
    let destinataire = des === null || des === void 0 ? void 0 : des.value;
    let data = getData(Url + "name/" + destinataire);
    data.then(res => {
        destinataire_nom.value = res;
    });
});
let tab = [];
save.addEventListener("click", () => {
    let expediteur = exp === null || exp === void 0 ? void 0 : exp.value;
    let montant = montants === null || montants === void 0 ? void 0 : montants.value;
    let type = types === null || types === void 0 ? void 0 : types.value;
    let destinataire = des === null || des === void 0 ? void 0 : des.value;
    let fournisseur = four === null || four === void 0 ? void 0 : four.value;
    tab.push(destinataire, expediteur, type, montant, fournisseur);
    const newTab = tab.reduce((acc, valeur, index) => {
        const cle = ["destinataire", "expediteur", "type", "montant", "fournisseur"][index];
        acc[cle] = valeur;
        return acc;
    }, {});
    fetch(Url + "depot", {
        method: "POST",
        headers: {
            "Content-type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(newTab)
    }).then(res => res.json()
        .then(data => console.log(data.json())));
});
