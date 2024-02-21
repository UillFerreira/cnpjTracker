var login = new login ();
if (login.signIn()) {
    // Importa o rquivo do menu
    // Fiz dessa forma para só aparecer outro componente quando o login for feito
    import("/wtk/js/menu.js").then(function (module) {
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
                "list" : [
                    {"name" : "Consultar CNPJ", "float" : "left"},
                    {"name" : "CNPJ salvo"},
                    {"name" : "Logout", "float" : "right", "callback" : login.logout.bind(login)}
                ]
            }
        );
    }, function (err) {
        console.error("Import do menu", err);
    });
} 
