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
        resolve();
    });
}
// Vai fazer a consulta no db
function getCnd (cnpj) {
    console.log("CNPJ", cnpj);
    call_sql("/cnpjTracker/cgi/cnpj.php", {"action":"cnd", "cnpj": cnpj}, undefined, function(ret) {
        ret = JSON.parse(ret);
        console.log(ret);
        let pdfWindow = window.open("");
        pdfWindow.document.write("<iframe width='100%' height='100%' src='data:application/pdf;base64, " + encodeURI(ret.Certidao.DocumentoPdf) + "'></iframe>");
    });
}

function displayForm() {
    console.log("CND FORM!");
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
        cnpj.addEventListener("blur", eventValidityCnpj);

        // Append elements
        content.appendChild(form);
        form.appendChild(title);
        form.appendChild(cnpj);
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


