const Url: string = "http://127.0.0.1:8000/api/";
const save = document.querySelector("#save") as HTMLElement;
let expediteur_nom = document.querySelector("#expediteur_nom") as HTMLInputElement
let destinataire_nom = document.querySelector("#destinataire_nom") as HTMLInputElement
let exp = document.querySelector("#expediteur") as HTMLInputElement
let des = document.querySelector("#destinataire") as HTMLInputElement
let montants = document.querySelector("#montant") as HTMLInputElement
let types = document.querySelector("#type_transaction") as HTMLSelectElement
let four = document.querySelector("#fournisseur") as HTMLInputElement
let transactionTitle = document.querySelector(".trans") as HTMLElement
let desTitle = document.querySelector(".des") as HTMLElement
let container = document.querySelector(".container") as HTMLElement
let formControl = document.querySelectorAll(".form-control") as NodeListOf<Element>
let destinataire = document.querySelector(".detinataire") as HTMLInputElement
let icone = document.querySelector(".icone") as HTMLElement
let tbody = document.querySelector("#tbody") as HTMLElement
let tableConte = document.querySelector(".table-conte") as HTMLElement
tableConte.style.display = "none"







enum transact {
    om = "#ff4500",
    wv = "#0088ff",
    wr = "#008044",
    cb = "#deb887"
}

function creatingElement(elName: string, attributs: any, elementContent: any) {
    const element = document.createElement(elName);
    for (const cle in attributs) {
        element.setAttribute(cle, attributs[cle])
    }
    element.textContent = elementContent;
    return element;
}

function afficheMessage(message: string, container: HTMLElement) {
    const mess = creatingElement('div', { class: 'mess dflex jcc aic' }, message);
    container.append(mess);
    setTimeout(() => {
        container.removeChild(mess);

    }, 5000)
}

async function getData(url: string) {
    const data = await fetch(url);
    const d = await data.json();
    return d;
}

function emptyField(inputs: any) {
    inputs.forEach(input => {
        input.value = ''
    })
}

function chargerData(tabs: any) {
    tbody.innerHTML = ""
    tabs.forEach(tab => {
        let tr = creatingElement('tr', { class: '' }, "")
        let date = creatingElement('td', { class: '' }, tab.date)
        let type = creatingElement('td', { class: '' }, tab.type)
        let montant = creatingElement('td', { class: '' }, tab.montant)
        tr.append(date, type, montant)
        tbody.append(tr)
    });
}

icone.addEventListener("click", () => {
    let expediteur: string = exp?.value
    let data = getData(Url + "transact/" + expediteur)
    data.then(res => {
        tableConte.style.display = "block"
        chargerData(res)
    })
})

icone.style.display = "none";

expediteur_nom.addEventListener("focus", () => {
    let expediteur: string = exp?.value
    if (expediteur_nom.value !== " ") {
        icone.style.display = "block"
    } else {
        icone.style.display = "none"
    }
    let data = getData(Url + "name/" + expediteur)
    data.then(res => {
        expediteur_nom.value = res
    }).catch(error => {
        afficheMessage("Client", expediteur_nom)
    })
})

four.addEventListener("change", () => {
    transactionTitle.style.color = "white"
    desTitle.style.color = "white"
    if (four.value == "om") {
        transactionTitle.style.backgroundColor = transact.om
        desTitle.style.backgroundColor = transact.om
    }
    if (four.value == "wv") {
        transactionTitle.style.backgroundColor = transact.wv
        desTitle.style.backgroundColor = transact.wv
    }
    if (four.value == "wr") {
        transactionTitle.style.backgroundColor = transact.wr
        desTitle.style.backgroundColor = transact.wr
    }
    if (four.value == "cb") {
        transactionTitle.style.backgroundColor = transact.cb
        desTitle.style.backgroundColor = transact.cb
    }
})

types.addEventListener("change", () => {
    if (types.value == "retrait") {
        destinataire.style.display = "none"
    } else {
        destinataire.style.display = "block"
    }
})

destinataire_nom.addEventListener("focus", () => {
    let destinataire: string = des?.value
    let data = getData(Url + "name/" + destinataire)
    data.then(res => {
        destinataire_nom.value = res
    })
})

let tab: string[] = [];

save.addEventListener("click", () => {
    let expediteur: string = exp?.value
    let montant: string = montants?.value
    let type: string = types?.value
    let destinataire: string = des?.value
    let fournisseur: string = four?.value
    tab.push(destinataire, expediteur, type, montant, fournisseur)
    const newTab = tab.reduce((acc: any, valeur, index) => {
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
    }).then(res => res.json().then(data => {
        afficheMessage(data, container)
    }
    ))
    tableConte.style.display = "none"
    // emptyField(formControl)
})