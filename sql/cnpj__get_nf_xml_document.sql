CREATE OR REPLACE FUNCTION cnpj__get_nf_xml_document (p_chave varchar) RETURNS jsonb AS $$
DECLARE
    v_data jsonb;
BEGIN
    SELECT json_data INTO v_data FROM cnpj__nfe_list WHERE chave = p_chave;
    RETURN v_data;
END;
$$ LANGUAGE plpgsql;

