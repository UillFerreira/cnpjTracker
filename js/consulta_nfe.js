'use strict';
export function nfeConsultaInit(menu) {
    console.log(menu);
    menu.addItem({"name" : "Consultar NFe", "float" : "left", "callback" : displayForm});
    // abre o css do componente
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = "/cnpjTracker/css/main.css";
    document.head.appendChild(link);

}
function componentInit() {
    return new Promise(async function(resolve, reject) {
        // Vai importar o cnd e adicionar o item no menu
        await import("/wtk/js/table.js").then(function (module) {
            for (var mod in module) {
                if (typeof module[mod] == "function") {
                    // Enabling function to be used in Window context
                    window[module[mod].name] = module[mod];
                } else {
                    window[mod] = module[mod];
                }
            }
        });
        resolve();
    });
}

function apiError (ejson) {
    let gridDiv = document.createElement("div");
    let title   = document.createElement ("h4");
    let msg = ejson["description"];
        switch (ejson["code"]) {
        case 3 :
            msg = ejson["error"];
            break;
        case "404" :
            msg = ejson["description"] + ". NÃO HÁ CERTIDÃO EMITIDA PARA O ESTABELECIMENTO ";
            break;
    }

    title.innerText = `${ejson["code"]} - ${msg}`;
    title.setAttribute("class", "fail");

    content.appendChild(gridDiv);
    gridDiv.appendChild(title);
}
function openXml () {
    let url = window.location.origin;
    let chave = this["chave"];
    let pdfWindow = window.open(url + `/cnpjTracker/cgi/nf.php?action=nfXml&chave=${chave}`);
    
}
function nfeReturnDisplay (nfeRet) {
    console.log(nfeRet);
    let content = document.getElementById("content");
    let gridDiv = document.createElement("div");
    apiError(nfeRet, gridDiv);
    gridDiv.setAttribute("class", "gridDiv")
//cndRet["result"][0]["Mensagem"]
    let divSub = document.createElement("div");
    divSub.setAttribute("class", "gridDiv");

    let subTitle = document.createElement ("h5");

    let emissao = new Date(nfeRet["result"][0]["atualizado"]);
    emissao = emissao.toLocaleString('pt-BR');
    let table = new tTable(
        {
            "content": content,
            "caption": "Nfe",
            "header": ["chave", "Atualizado", "xml"],
            "list": [
                {"chave": nfeRet["result"][0]["chave"], "Atualizado": emissao, "xml": {"icon": "pdf", "callback": openXml}}
            ]
        }
    );

    content.appendChild(gridDiv);
    gridDiv.appendChild(divSub);
    divSub.appendChild(subTitle);

}

function getNfe(value) {
//    let url = window.location.origin;
//    let pdfWindow = window.open(url + `/cnpjTracker/cgi/nf.php?action=nfe&chave=${value}`);

    call_sql("/cnpjTracker/cgi/nf.php", {"action":"nfe", "chave": value}, undefined, function(ret) {
        if (ret["error"] != undefined) {
            apiError(ret);
        } else {
            nfeReturnDisplay(ret);
        }
    });

}

function displayForm() {
    componentInit().then(function () {
        let content = document.getElementById("content");
        let form    = document.createElement ("form");
        form.setAttribute("class", "form_search");
        let title   = document.createElement ("h3");
        title.innerText = "Consulta de NFe";
        let nfe    = document.createElement ("input");
        nfe.setAttribute("type", "text");
        nfe.setAttribute("required", "true");
        nfe.setAttribute("placeholder", "Chave de acceso de NF-e (44 dígitos, sem espaços)");
        nfe.setAttribute("pattern", "[0-9]{44}");

            // Todos os eventos que interage com o input do cnpj passa por essa função para validar o campo
            function eventValidityNfe (e) {
                let f = e.target.value;
                console.log(e.target);
                let isValid = e.target.checkValidity();
                // Vai buscar o documento caso seja um cnpj válido
                if (isValid) {
                    getNfe(f);
                } else {
                    e.target.reportValidity();
                    e.target.setCustomValidity('Chave de acceso de NF-e (44 dígitos, sem espaços)');
                }
            }
            nfe.addEventListener("change", eventValidityNfe)


        // Append elements
        content.appendChild(form);
        form.appendChild(title);
        form.appendChild(nfe);
    });
}

