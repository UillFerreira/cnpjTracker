CREATE OR REPLACE FUNCTION cnpj__get_uuid (p_cnpj varchar) RETURNS UUID AS $$
DECLARE
    v_cnpj_uuid uuid;
BEGIN
    SELECT cnpj_uuid INTO v_cnpj_uuid FROM cnpj__list WHERE cnpj = p_cnpj;
    IF (v_cnpj_uuid IS NULL) THEN
        v_cnpj_uuid = cnpj__new_register(p_cnpj);
    END IF;
    RETURN v_cnpj_uuid;
END;
$$ LANGUAGE plpgsql;
