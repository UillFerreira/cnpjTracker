CREATE OR REPLACE FUNCTION cnpj__serpro_cnd_save (p_result json) RETURNS json AS $$
DECLARE
    v_cnpj_uuid uuid;
    v_sha1 text;
    v_cnd_uuid uuid;
BEGIN
    v_sha1 = encode(digest(p_result->'Certidao'->>'DocumentoPdf', 'sha1'), 'hex');
    INSERT INTO cnpj__data_cnd(cnpj_uuid, sha1, blob, validity) VALUES ((p_result->>'cnpj_uuid')::uuid, v_sha1, (p_result->'Certidao'->>'DocumentoPdf')::bytea, (p_result->'Certidao'->>'DataValidade')::date) RETURNING cnd_uuid INTO v_cnd_uuid;

    INSERT INTO cnpj__serpro_log (cnpj_uuid, status, msg, serpro_uuid) VALUES ((p_result->>'cnpj_uuid')::uuid, p_result->>'Status', p_result->>'Mensagem', v_cnd_uuid); 

    RETURN json_build_object (
        'Status', p_result->>'Status',
        'Mensagem', p_result->>'Mensagem', 
        'cnpj_uuid', p_result->>'cnpj_uuid',
        'DataValidade', p_result->'Certidao'->>'DataValidade',
        'DataEmissao', p_result->'Certidao'->>'DataEmissao'
    );
END;
$$ LANGUAGE plpgsql;
