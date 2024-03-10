<?php
function serproAuth($key, $secret, $contract_uuid) {
    // API DE TESTE
    if ($contract_uuid == "71deded7-218e-4d58-b745-770cbdd23aa9" || $contract_uuid == "dcc06d9c-8434-49f4-a14d-d2a1da361670" || $contract_uuid == "778b2c55-f73c-40ec-b6ee-21eb1f0af935") {
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
// Só está buscando com cnpj
function serproConsultCnpj ($url, $bearer, $cnpj, $codigo) {


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '/' . $cnpj);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'x-request-tag: ' . $codigo;
    $headers[] = 'Authorization: Bearer '.$bearer;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $result = curl_error($ch);
    }
    curl_close($ch);
    return json_decode($result);
}
function serproConsultCnpjCache($cnpj) {
    $cache = doSql("SELECT * FROM cnpj__serpro_cnpj_select($1)", array($cnpj));
    if (!isset($cache->result[0]->cnpj__serpro_cnpj_select)) {
        return;
    }
    $cache = json_decode($cache->result[0]->cnpj__serpro_cnpj_select);
    $cache->cache   = true;
    $cache->cnpj    = $cnpj;
    // Default return
    $ret = new stdClass();
    $ret->result = array();
    $ret->result[0] = new stdClass();
    $ret->result[0] = $cache;
    return $ret;
}

// busca informações da nota
function serproConsultNfe ($url, $bearer, $chave, $codigo) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . $chave);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'x-request-tag: ' . $codigo;
    $headers[] = 'Authorization: Bearer '.$bearer;
    $headers[] = 'x-signature: 1';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    curl_close($ch);

    return json_decode($response, true);
}

