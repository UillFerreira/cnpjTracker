CREATE OR REPLACE FUNCTION cnpj__serpro_cnpj_select (p_cnpj_uuid uuid) RETURNS json AS $$
DECLARE
    v_cnpj_uuid uuid;
    v_json_ret json;
BEGIN
    SELECT json_data::json INTO v_json_ret FROM cnpj__data AS d WHERE cnpj_uuid = cnpj__data;
    RETURN v_json_ret;
END;
$$ LANGUAGE plpgsql;
