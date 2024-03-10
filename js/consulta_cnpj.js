'use strict';
export function cnpjConsultaInit(menu) {
    console.log(menu);
    menu.addItem({"name" : "Consultar CNPJ", "float" : "left", "callback" : displayForm});
    // abre o css do componente
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = "/cnpjTracker/css/main.css";
    document.head.appendChild(link);

}
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
function apiError (ejson) {
    let gridDiv = document.createElement("div");
    let title   = document.createElement ("h4");
    let msg = ejson["description"];
        switch (ejson["code"]) {
        case 3 :
            msg = ejson["error"];
            break;
        case "404" :
            msg = ejson["description"] + ". NÃO HÁ INFORMAÇÕES SOBRE O CNPj";
            break;
    }

    title.innerText = `${ejson["code"]} - ${msg}`;
    title.setAttribute("class", "fail");

    content.appendChild(gridDiv);
    gridDiv.appendChild(title);
}

function cnpjReturnDisplay (ret) {
    let content = document.getElementById("content");

    let pre = document.createElement("pre");

    let json = JSON.stringify(ret, null, 2);

    pre.textContent = json;
    pre.style.color = 'white';
    content.appendChild(pre);
}
function getCnpjInfo(cnpj) {
    call_sql("/cnpjTracker/cgi/cnpj.php", {"action":"consultaCnpj", "cnpj": cnpj}, undefined, function(ret) {
        if (ret["error"] != undefined) {
            apiError(ret);
        } else {
            cnpjReturnDisplay(ret);
        }
    });

}
function displayForm() {
    console.log("Aqui");;
    cnpjInit().then(function () {
        let content = document.getElementById("content");
        let form    = document.createElement ("form");
        form.setAttribute("class", "form_search");
        let title   = document.createElement ("h3");
        title.innerText = "Consulta de CNPJ";
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
                getCnpjInfo(f);
            }
        }
        cnpj.addEventListener("change", eventValidityCnpj);

        // Append elements
        content.appendChild(form);
        form.appendChild(title);
        form.appendChild(cnpj);

    });
}
