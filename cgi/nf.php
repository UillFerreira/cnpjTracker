<?php
    include("../../auth/cgi/psql.php");
    include("serpro.php");
    sessionVerify();

    function array_to_xml($array, &$xml) {
        foreach ($array as $chave => $valor) {
            if (is_array($valor)) {
                if (!is_numeric($chave)){
                    $sub_xml = $xml->addChild("$chave");
                    array_to_xml($valor, $sub_xml);
                } else {
                    array_to_xml($valor, $xml);
                }
            } else {
                $xml->addChild("$chave",htmlspecialchars("$valor"));
            }
        }
        return $xml;
    }

    switch ($_GET["action"]) {
        case "nfe" :
            if (!isset($_GET["chave"]) || empty($_GET["chave"])) {
                $error = new stdClass();
                $error->error = "Não foi enviada a chave da nfe no GET";
                echo json_encode($error);
                break;
            }
            // Remove os caracteres especiais da chave para poder enviar na api
            $chave = trim($_GET["chave"]);
            $chave = str_replace(array('.','-','/'), "", $chave);
           

            // Busca as chaves 
            // TESTES
            $contract_uuid = "778b2c55-f73c-40ec-b6ee-21eb1f0af935";
            // PRODUÇÃO
            //$contract_uuid = "2f3aa007-c341-4342-b826-08a8873209bf";
            $params = doSql("SELECT * FROM cnpj__serpro_param($1)", array($contract_uuid));
            $url    = $params->result[0]->url;
            // Busca no webservice de autenticação o bearer. Se fizer a requisição repetidas vezes, ele só autera o expired time
            $bearer = serproAuth($params->result[0]->key, $params->result[0]->secret, $contract_uuid);
            $bearer = $bearer->access_token;
            // Trata para caso não conseguir pegar o bearer
            if (isset($bearer->error_description)) {
                $error = new stdClass();
                $error->error = $bearer->error_description;
                echo json_encode($error);
                break;
            }
            $ret = serproConsultNfe($url, $bearer, $chave, $chave);
            if ($ret == null) {
                $error = new stdClass();
                $error->error   = "Não achou nenhuma Nfe";
                echo json_encode($error);
                break;
            }

$xml = new SimpleXMLElement('<root/>');
$a = array_to_xml($ret, $xml);
echo $a;

//          echo json_encode($ret);
        break;
    }
