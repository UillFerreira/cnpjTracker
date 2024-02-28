CREATE OR REPLACE FUNCTION cnpj__serpro_param(p_contract_uuid uuid) RETURNS
    TABLE (
        servico text,
        key text,
        secret text,
        url text
    ) AS $$
    DECLARE
    BEGIN
        IF p_contract_uuid IS NULL THEN
            RAISE 'UUID do contrato não pode ser nulo';
        END IF;

        RETURN QUERY
            SELECT c.servico, c.key, c.secret, c.url FROM cnpj__api_serpro AS c WHERE contract_uuid = p_contract_uuid;
    END;
$$ LANGUAGE plpgsql;
