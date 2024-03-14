CREATE OR REPLACE FUNCTION cnpj__serpro_cnpj_save (p_result json) RETURNS json AS $$
DECLARE
    v_updated timestamptz;
    v_cnpj varchar;
    v_cnpj_uuid uuid;
BEGIN
    v_cnpj = p_result->>'ni';
    IF (v_cnpj = '' OR v_cnpj IS NULL) THEN
        raise 'Não achou o CNPJ no json';
    END IF;
    SELECT cnpj_uuid INTO v_cnpj_uuid FROM cnpj__list WHERE cnpj = v_cnpj;
    IF (v_cnpj_uuid IS NULL) THEN
        INSERT INTO cnpj__list (cnpj) VALUES (v_cnpj) RETURNING cnpj_uuid INTO v_cnpj_uuid ;
    END IF;
    INSERT INTO cnpj__data(cnpj_uuid, json_data) VALUES (v_cnpj_uuid, p_result) ON CONFLICT (cnpj_uuid) DO UPDATE SET json_data = p_result;
    INSERT INTO cnpj__serpro_log (contract_uuid, key_uuid, msg) VALUES ('2f3aa007-c341-4342-b826-08a8873209bf', v_cnpj_uuid, 'Salvo uma consulta de CNPJ serpro');

    RETURN p_result;
END;
$$ LANGUAGE plpgsql;

