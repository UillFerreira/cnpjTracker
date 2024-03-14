CREATE OR REPLACE FUNCTION cnpj__serpro_cnpj_select (p_cnpj_uuid uuid) RETURNS json AS $$
DECLARE
    v_cnpj_uuid uuid;
    v_json_ret json;
BEGIN
    SELECT json_data::json INTO v_json_ret FROM cnpj__data AS d WHERE cnpj_uuid = cnpj__data;
    INSERT INTO cnpj__serpro_log (contract_uuid, key_uuid, msg) VALUES ('2f3aa007-c341-4342-b826-08a8873209bf', v_chave_uuid, 'Nova consulta de CNPJ serpro');

    RETURN v_json_ret;
END;
$$ LANGUAGE plpgsql;
