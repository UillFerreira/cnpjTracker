<?php
    include("../../auth/cgi/psql.php");
    sessionVerify();
    switch ($_GET["action"]) {
        case "cnd" :
// Exemplo de como solicitar o Token de Acesso (Bearer)
//curl -k -H "Authorization: Basic VklDNV9wWkFOMk9iVTJ6OXpZNU04SklhOWtVYTpxYXVXTjFyZ2Q4UDMxM1BZYV9EY2ZjZWhwS3dh" -d "grant_type=client_credentials" https://gateway.apiserpro.serpro.gov.br/token

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
            echo json_encode($response);
            break;
    }
