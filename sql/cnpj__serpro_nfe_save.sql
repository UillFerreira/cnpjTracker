CREATE OR REPLACE FUNCTION cnpj__serpro_nfe_save (p_result json) RETURNS json AS $$
DECLARE
    v_sha1 text;
    v_chave_uuid uuid;
    v_chave text;
    v_updated timestamptz;
BEGIN
    v_sha1  = encode(digest(p_result::text, 'sha1'), 'hex');
    v_chave = p_result->'nfeProc'->'protNFe'->'infProt'->>'chNFe';
    IF (v_chave = '' OR v_chave IS NULL) THEN
        raise 'Não tem a chave da Nfe';
    END IF;
    INSERT INTO cnpj__nfe_list(chave, json_data, sha1) VALUES (v_chave, p_result, v_sha1) ON CONFLICT (chave) DO UPDATE SET json_data = p_result, sha1 = v_sha1 RETURNING chave_uuid, updated INTO v_chave_uuid, v_updated ;
raise notice 'Atualizou!%', v_updated; 
    INSERT INTO cnpj__serpro_log (contract_uuid, key_uuid, msg) VALUES ('f0a6af8f-584f-4650-9702-3e327f2134f2', v_chave_uuid, 'Nova consulta NFe serpro');

    RETURN json_build_object (
        'chave', v_chave,
        'Atualizado', v_updated
    );
END;
$$ LANGUAGE plpgsql;
