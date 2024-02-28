<?php
    include("../../auth/cgi/psql.php");
    sessionVerify();
    function serproAuth($key, $secret, $contract_uuid) {
        // API DE TESTE
        if ($contract_uuid == "71deded7-218e-4d58-b745-770cbdd23aa9") {
            $result = new stdClass();
            $result->access_token = "06aef429-a981-3ec5-a1f8-71d38d86481e";
            return $result;
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gateway.apiserpro.serpro.gov.br/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $key . ':' . $secret);

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);
    }
    // Só está buscando com cnpj
    function serproConsultCnd ($url, $bearer, $cnpj, $codigo) {
        $ch = curl_init();
        $payload = 
            json_encode(array( 
                "TipoContribuinte"=> "1", 
                "ContribuinteConsulta"=> $cnpj, 
                "CodigoControle"=> $codigo,
                "CodigoIdentificacao"=> "9001", 
                "GerarCertidaoPdf"=> true 
                )
            );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$bearer;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);
    }
    function serproConsultCndCache($cnpj) {
        $cache = doSql("SELECT * FROM cnpj__serpro_cnd_select($1)", array($cnpj));
        if (!isset($cache->result[0]->cnpj__serpro_cnd_select)) {
            return;
        }
        $cache = json_decode($cache->result[0]->cnpj__serpro_cnd_select);
        $cache->cache   = true;
        $cache->cnpj    = $cnpj;
        // Default return
        $ret = new stdClass();
        $ret->result = array();
        $ret->result[0] = new stdClass();
        $ret->result[0] = $cache;
        return $ret;
    }
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
    }
