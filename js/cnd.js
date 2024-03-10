'use strict';
console.log("init do CND");
function cnpjInit() {
    return new Promise(async function(resolve, reject) {
        // Vai importar o cnd e adicionar o item no menu
        await import("/cnpjTracker/js/cnpj.js").then(function (module) {
            for (var mod in module) {
                if (typeof module[mod] == "function") {
                    // Enabling function to be used in Window context
                    window[module[mod].name] = module[mod];
                } else {
                    window[mod] = module[mod];
                }
            }
        });
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
function openPdf (row) {
    console.log("Row", this);
    let url = window.location.origin;
    let cnpj = this["CNPJ"];
    let pdfWindow = window.open(url + `/cnpjTracker/cgi/cnpj.php?action=cndPdf&cnpj=${cnpj}`);
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
function cnfReturnDisplay (cndRet) {
    let content = document.getElementById("content");
    let gridDiv = document.createElement("div");
    apiError(cndRet, gridDiv);
    gridDiv.setAttribute("class", "gridDiv")
//cndRet["result"][0]["Mensagem"]
    let divSub = document.createElement("div");
    divSub.setAttribute("class", "gridDiv");
    
    let subTitle = document.createElement ("h5");
    //subTitle.innerText = `CNPJ: ${cndRet["Certidao"]["ContribuinteCertidao"]}  Emissao: ${emissao} Válidade: ${validade}`;

    let emissao = new Date(cndRet["result"][0]["DataEmissao"]);
    emissao = emissao.toLocaleString('pt-BR');
    let validade = new Date(cndRet["result"][0]["DataValidade"]);
    validade = validade.toLocaleString('pt-BR');

    let table = new tTable(
        {
            "content": content,
            "caption": cndRet["Mensagem"],  
            "header": ["CNPJ", "Emissão", "Válidade", "CND"], 
            "list": [
                {"CNPJ": cndRet["result"][0]["cnpj"], "Emissão": emissao, "Válidade": validade, "CND": {"icon": "pdf", "callback": openPdf}}
            ]
        }
    );

    content.appendChild(gridDiv);
    gridDiv.appendChild(divSub);
    divSub.appendChild(subTitle);

}
// Vai fazer a consulta no db
function getCnd (cnpj) {
    call_sql("/cnpjTracker/cgi/cnpj.php", {"action":"cnd", "cnpj": cnpj}, undefined, function(ret) {
        if (ret["error"] != undefined) {
            apiError(ret);
        } else {
            cnfReturnDisplay(ret);
        }
        //let pdfWindow = window.open("");
        //pdfWindow.document.write("<iframe width='100%' height='100%' src='data:application/pdf;base64, " + encodeURI(ret.Certidao.DocumentoPdf) + "'></iframe>");
    });
}

function displayForm() {
    cnpjInit().then(function () {
        let content = document.getElementById("content");
        let form    = document.createElement ("form");
        form.setAttribute("class", "form_search");
        let title   = document.createElement ("h3");
        title.innerText = "Consulta de certidão negativa de débito (CND)";
        let cnpj    = document.createElement ("input");
        cnpj.setAttribute("type", "text");
        cnpj.setAttribute("required", "true");
        cnpj.setAttribute("placeholder", "Inserir o CNPJ");
        // Todos os eventos que interage com o input do cnpj passa por essa função para validar o campo
        function eventValidityCnpj (e) {
            // Test
            if (e.target.value == '00000000000001') {
                getCnd(e.target.value);
                return true;
            }
            let f = formatCNPJ(e.target.value);
            if (f != '') {
                e.target.value = f; 
            } else {
                e.target.value = ""; 
            }
            let isValid = e.target.checkValidity();
            // Vai buscar o documento caso seja um cnpj válido
            if (isValid) {
                getCnd(f);
            }
        }
        cnpj.addEventListener("change", eventValidityCnpj);

        // Append elements
        content.appendChild(form);
        form.appendChild(title);
        form.appendChild(cnpj);


//    let teste = {"Certidao":{"CodigoControle":"XXXXXXXXXXXXXXXX","ContribuinteCertidao":"00000000000001","DataEmissao":"2021-05-05T10:56:41","DataValidade":"2021-11-01","DocumentoPdf":"CkRvY3VtZW50b1BkZg==","TipoCertidao":1,"TipoContribuinte":1},"Mensagem":"Processamento OK - Certidão Encontrada.","Status":1};
    //cnfReturnDisplay(teste);
//console.log(teste);
    });
}
export function cndInit(menu) {
    menu.addItem({"name" : "Consultar CND", "float" : "left", "callback" : displayForm});
    // abre o css do componente
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = "/cnpjTracker/css/main.css";
    document.head.appendChild(link);

}
