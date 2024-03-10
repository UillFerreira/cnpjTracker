-- CND
insert into cnpj__api_serpro (contract_uuid, servico, key, secret, url) values ('71deded7-218e-4d58-b745-770cbdd23aa9', 'Consulta CND - CONTRATO: TESTE', '06aef429-a981-3ec5-a1f8-71d38d86481e', '', 'https://gateway.apiserpro.serpro.gov.br/consulta-cnd-trial/v1/certidao') ON CONFLICT (contract_uuid) DO NOTHING;
insert into cnpj__api_serpro (contract_uuid, servico, key, secret, url) values ('90eb2c8c-7d9a-4b60-9871-607a02698536', 'Consulta CND - CONTRATO: 178445', 'VIC5_pZAN2ObU2z9zY5M8JIa9kUa', 'qauWN1rgd8P313PYa_DcfcehpKwa', 'https://gateway.apiserpro.serpro.gov.br/consulta-cnd/v1/certidao') ON CONFLICT (contract_uuid) DO NOTHING;
-- CNPJ
insert into cnpj__api_serpro (contract_uuid, servico, key, secret, url) values ('dcc06d9c-8434-49f4-a14d-d2a1da361670', 'Consulta CNPJ v2 - Mercado Privado - CONTRATO: TESTE', '06aef429-a981-3ec5-a1f8-71d38d86481e', '', 'https://gateway.apiserpro.serpro.gov.br/consulta-cnpj-df-trial/v2/empresa') ON CONFLICT (contract_uuid) DO NOTHING;
insert into cnpj__api_serpro (contract_uuid, servico, key, secret, url) values ('2f3aa007-c341-4342-b826-08a8873209bf', 'Consulta CNPJ v2 - Mercado Privado - CONTRATO: 178449', 'VIC5_pZAN2ObU2z9zY5M8JIa9kUa', 'qauWN1rgd8P313PYa_DcfcehpKwa', 'https://gateway.apiserpro.serpro.gov.br/consulta-cnpj-df/v2/empresa') ON CONFLICT (contract_uuid) DO NOTHING;
-- NFE
insert into cnpj__api_serpro (contract_uuid, servico, key, secret, url) values ('778b2c55-f73c-40ec-b6ee-21eb1f0af935', 'Consulta NFE v2 - CONTRATO: TESTE', '06aef429-a981-3ec5-a1f8-71d38d86481e', '', 'https://gateway.apiserpro.serpro.gov.br/consulta-nfe-df-trial/api/v1/nfe/') ON CONFLICT (contract_uuid) DO NOTHING;

