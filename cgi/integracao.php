<?php
// https://api.cnpja.com

$cnpj = "28885775000100";
$url = "https://gateway.apiserpro.serpro.gov.br/consulta-cnd-trial/v1/certidao";
$curl_h = curl_init($url);

curl_setopt($curl_h, CURLOPT_HTTPHEADER,
    array(
        'Authorization: Bearer 06aef429-a981-3ec5-a1f8-71d38d86481e',
        'accept: application/json',
        'Content-Type: application/json'
    )
);
$payload = json_encode(array( "TipoContribuinte"=> "1", "ContribuinteConsulta"=> "00000000000001", "CodigoIdentificacao"=> "9001", "GerarCertidaoPdf"=> true, "Chave"=> "dfdf" ));
// Set data
curl_setopt($curl_h, CURLOPT_POSTFIELDS, $payload );

# do not output, but store to variable
curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl_h);

echo $response;
