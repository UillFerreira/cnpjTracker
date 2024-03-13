<?php
    include("../../auth/cgi/psql.php");
    include("serpro.php");
    sessionVerify();
    switch ($_GET["action"]) {
        case "cndPdf" :
            if (!isset($_GET["cnpj"]) || empty($_GET["cnpj"])) {
                $error = new stdClass();
                $error->error = "Não foi enviado o CNPJ no GET";
                echo json_encode($error);
                break;
            }
            $ret = doSql("SELECT * FROM cnpj__get_pdf_document($1)", array($_GET["cnpj"])); 
            header("Content-Type: application/pdf");
            echo base64_decode(pg_unescape_bytea($ret->result[0]->cnpj__get_pdf_document));
            break;
        case "cnd" :
            if (!isset($_GET["cnpj"]) || empty($_GET["cnpj"])) {
                $error = new stdClass();
                $error->error = "Não foi enviado o CNPJ no GET";
                echo json_encode($error);
                break;
            }
            // Remove os caracteres especiais do cnpj para poder enviar na api
            $cnpj = trim($_GET["cnpj"]);
            $cnpj = str_replace(array('.','-','/'), "", $cnpj);
            // Verifica se o cnpj já foi cadastrado, e retorna o uuid. O uuid pode ser útil para as requisições de demoram, poís pode retornar em outro momento 
            // e precisa de uma chave para poder achar a requisição
            $cnpj_uuid = doSql("select * FROM cnpj__get_uuid($1)", array($cnpj));
            if (isset($cnpj_uuid->error)) {
                echo json_encode($cnpj_uuid);
                break;
            }
            $cnpj_uuid = $cnpj_uuid->result[0]->cnpj__get_uuid;
            // Verifica se tem algo no banco de dados para não precisar chamar o webservice
            // Se já tiver, ele verifica se está vencido o documento, se não estiver, devolve as informações direto do banco de dados
            $cache = serproConsultCndCache($cnpj);
            if (isset($cache)){
                $data_verificar = $cache->result[0]->DataValidade;
                // Criar um objeto DateTime para a data atual
                $data_atual = new DateTime();
                // Criar um objeto DateTime para a data a ser verificada
                $data_verificar_obj = new DateTime($data_verificar);
                // Se tiver vencido tem q buscar online
                if ($data_verificar_obj > $data_atual) {
                    echo json_encode($cache);
                    break;
                } 
            }
            // Busca as chaves 
            // TESTES
            //$contract_uuid = "71deded7-218e-4d58-b745-770cbdd23aa9";
            // PRODUÇÃO
            $contract_uuid = "90eb2c8c-7d9a-4b60-9871-607a02698536";
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
            $ret = serproConsultCnd($url, $bearer, $cnpj, $cnpj_uuid);

            if (isset($ret->Certidao->DocumentoPdf)) {
                $bytea_data = pg_escape_bytea(($ret->Certidao->DocumentoPdf));
                $ret->Certidao->DocumentoPdf = $bytea_data;
                $ret->cnpj_uuid = $cnpj_uuid;
                $result = doSql("SELECT * FROM cnpj__serpro_cnd_save($1)", array(pg_escape_string(json_encode($ret))));

                $result = json_decode($result->result[0]->cnpj__serpro_cnd_save);
                $result->cnpj = $cnpj;
                        
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
        case "consultaCnpj" :
            if (!isset($_GET["cnpj"]) || empty($_GET["cnpj"])) {
                $error = new stdClass();
                $error->error = "Não foi enviado o CNPJ no GET";
                echo json_encode($error);
                break;
            }
            // Remove os caracteres especiais do cnpj para poder enviar na api
            $cnpj = trim($_GET["cnpj"]);
            $cnpj = str_replace(array('.','-','/'), "", $cnpj);
            // Verifica se o cnpj já foi cadastrado, e retorna o uuid. O uuid pode ser útil para as requisições de demoram, poís pode retornar em outro momento 
            // e precisa de uma chave para poder achar a requisição
            $cnpj_uuid = doSql("select * FROM cnpj__get_uuid($1)", array($cnpj));
            if (isset($cnpj_uuid->error)) {
                echo json_encode($cnpj_uuid);
                break;
            }
            $cnpj_uuid = $cnpj_uuid->result[0]->cnpj__get_uuid;
            $cache = serproConsultCnpjCache($cnpj_uuid);
            if (isset($cache)){
                echo json_encode($cache);
                break;
            }
            // Busca as chaves 
            // TESTES
            //$contract_uuid = "dcc06d9c-8434-49f4-a14d-d2a1da361670";
            // PRODUÇÃO
            $contract_uuid = "2f3aa007-c341-4342-b826-08a8873209bf";
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
            $ret = serproConsultCnpj($url, $bearer, $cnpj, $cnpj_uuid);
            echo json_encode($ret);
            break;
    }
