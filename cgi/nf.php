<?php
    include("../../auth/cgi/psql.php");
    include("serpro.php");
    sessionVerify();

    // Função para converter um array em XML de forma recursiva
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
    }

    switch ($_GET["action"]) {
        case "nfXml" :
            if (!isset($_GET["chave"]) || empty($_GET["chave"])) {
                $error = new stdClass();
                $error->error = "Não foi enviado o CNPJ no GET";
                echo json_encode($error);
                break;
            }
            $ret = doSql("SELECT * FROM cnpj__get_nf_xml_document($1)", array($_GET["chave"]));

            $ret = json_decode($ret->result[0]->cnpj__get_nf_xml_document, true);
            header('Content-type: application/xml');
            $xml = new SimpleXMLElement('<nfeProc/>');
            array_to_xml($ret["nfeProc"], $xml);
            $xml->NFe->infNFe->addAttribute('Id', 'NFe' . $_GET["chave"]);
            $xml->NFe->infNFe->addAttribute('versao', '4.00');
            echo $xml->asXML();
            break;
        
        case "nfeUpdate" :
            if (!isset($_GET["chave"]) || empty($_GET["chave"])) {
                $error = new stdClass();
                $error->error = "Não foi enviada a chave da nfe no GET";
                echo json_encode($error);
                break;
            }
            // Remove os caracteres especiais da chave para poder enviar na api
            $chave = trim($_GET["chave"]);
            $chave = str_replace(array('.','-','/'), "", $chave);
            if (strlen($chave) != 44) {
                $error = new stdClass();
                $error->error = "A chave tem 44 dígitos";
                echo json_encode($error);
                break;
            }
            if (preg_match('/[a-zA-Z]/', $chave)) {
                $error = new stdClass();
                $error->error = "A chave só pode conter números";
                echo json_encode($error);
                break;
            }
            // Busca as chaves 
            // TESTES
            //$contract_uuid = "778b2c55-f73c-40ec-b6ee-21eb1f0af935";
            // PRODUÇÃO
            $contract_uuid = "f0a6af8f-584f-4650-9702-3e327f2134f2";
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
            if ($ret == null || isset($ret[0]["message"])) {
                $error = new stdClass();
                $error->error   = $ret[0]["message"];
                echo json_encode($error);
                break;
            }
            if (isset($ret["nfeProc"]["protNFe"]["infProt"]["chNFe"])) {
                $result = doSql("SELECT * FROM cnpj__serpro_nfe_save($1)", array(pg_escape_string(json_encode($ret))));

                $result = json_decode($result->result[0]->cnpj__serpro_nfe_save);

                // Default return
                $ret = new stdClass();
                $ret->result = array();
                $ret->result[0] = new stdClass();
                $ret->result[0] = $result;
                echo json_encode($ret);
            } else {
                $error = new stdClass();
                if (isset($ret->message)) {
                    $error->error   = $ret->message;
                    $error->code    = $ret->code;
                    $error->description     = $ret->description;
                } elseif (isset($ret->Mensagem)) {
                    $error->error       = $ret->Mensagem;
                    $error->code        = $ret->Status;
                }else {
                    $error->error = "Não houve o retorno do documento NFe para a chave: " . $chave;
                }
                echo json_encode($error);
            }
            break;
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
            if (strlen($chave) != 44) {
                $error = new stdClass();
                $error->error = "A chave tem 44 dígitos";
                echo json_encode($error);
                break;
            }
            if (preg_match('/[a-zA-Z]/', $chave)) {
                $error = new stdClass();
                $error->error = "A chave só pode conter números";
                echo json_encode($error);
                break;
            }
            $cache = serproConsultNfeCache($chave);
            if (isset($cache)){
                echo json_encode($cache);
                break;
            }
           
            // Busca as chaves 
            // TESTES
            //$contract_uuid = "778b2c55-f73c-40ec-b6ee-21eb1f0af935";
            // PRODUÇÃO
            $contract_uuid = "f0a6af8f-584f-4650-9702-3e327f2134f2";
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
            if (isset($ret["nfeProc"]["protNFe"]["infProt"]["chNFe"])) {
                $result = doSql("SELECT * FROM cnpj__serpro_nfe_save($1)", array(pg_escape_string(json_encode($ret))));

                $result = json_decode($result->result[0]->cnpj__serpro_nfe_save);

                // Default return
                $ret = new stdClass();
                $ret->result = array();
                $ret->result[0] = new stdClass();
                $ret->result[0] = $result;
                echo json_encode($ret);
            } else {
                $error = new stdClass();
                if (isset($ret->message)) {
                    $error->error   = $ret->message;
                    $error->code    = $ret->code;
                    $error->description     = $ret->description;
                } elseif (isset($ret->Mensagem)) {
                    $error->error       = $ret->Mensagem;
                    $error->code        = $ret->Status;
                }else {
                    $error->error = "Não houve o retorno do documento pdf para o cnpj: " . $cnpj;
                }
                echo json_encode($error);
            }
            
            break;
    }
