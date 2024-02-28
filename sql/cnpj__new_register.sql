CREATE OR REPLACE FUNCTION cnpj__new_register (p_cnpj varchar) RETURNS UUID AS $$
DECLARE
    v_cnpj_uuid uuid;
BEGIN
    INSERT INTO cnpj__list (cnpj) VALUES (p_cnpj) ON CONFLICT (cnpj) DO NOTHING RETURNING cnpj_uuid INTO v_cnpj_uuid;
    RETURN v_cnpj_uuid;
END;
$$ LANGUAGE plpgsql;
