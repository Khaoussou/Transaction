import { tbody } from "./dom.js";

export function creatingElement(elName: string, attributs: any, elementContent: any) {
    const element = document.createElement(elName);
    for (const cle in attributs) {
        element.setAttribute(cle, attributs[cle])
    }
    element.textContent = elementContent;
    return element;
}

export function afficheMessage(message: string, container: HTMLElement) {
    const mess = creatingElement('div', { class: 'mess dflex jcc aic' }, message);
    container.append(mess);
    setTimeout(() => {
        container.removeChild(mess);

    }, 5000)
}

export function emptyField(inputs: any) {
    inputs.forEach((input: any) => {
        input.value = ''
    })
}

export function chargerData(tabs: any) {
    tbody.innerHTML = ""
    tabs.forEach((tab: any) => {
        let tr = creatingElement('tr', { class: 'tr' }, "")
        let date = creatingElement('td', { class: '' }, tab.date)
        let type = creatingElement('td', { class: '' }, tab.type)
        let montant = creatingElement('td', { class: '' }, tab.montant)
        let code = creatingElement('td', { class: '' }, tab.code)
        let annuler = creatingElement('button', { type: 'button', class: 'btn btn-secondary remove' }, "Annuler")
        if (tab.type == "transfert-simple") {
            tr.classList.add("annuler")
            tr.append(date, type, montant, code, annuler)
        } else {
            tr.append(date, type, montant, code)
        }
        tbody.append(tr)
    });
}