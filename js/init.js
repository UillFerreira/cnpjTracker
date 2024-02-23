async function init () {
    let l = new tLogin ();
    if (l.signIn()) {
        // Importa o rquivo do menu
        // Fiz dessa forma para só aparecer outro componente quando o login for feito
        await import("/wtk/js/menu.js").then(async function (module) {
            for (var mod in module) {
                console.log(mod, module); 
                if (typeof module[mod] == "function") {
                    // Enabling function to be used in Window context
                    window[module[mod].name] = module[mod];
                }
            }
            // Chama a função que vai criar o menu na tela.
            var menu = new tMenu(
                {
                    "display" : "horizontal",
                    "content": document.getElementById("nav_content"),
                    "list" : [
                        //{"name" : "Consultar CND", "float" : "left"},
                        //{"name" : "CNPJ salvo"},
                        {"name" : "Logout", "float" : "right", "callback" : l.logout.bind(l)}
                    ]
                }
            );
            // Vai importar o cnd e adicionar o item no menu
            await import("/cnpjTracker/js/cnd.js").then(function (module) {
                for (var mod in module) {
                    if (typeof module[mod] == "function") {
                        // Enabling function to be used in Window context
                        window[module[mod].name] = module[mod];
                    }
                }
            });
            // passa o contexto do menu para poder adicionar o item
            cndInit(menu);
        }, function (err) {
            console.error("Import do menu", err);
        });
    } 
}
init();
