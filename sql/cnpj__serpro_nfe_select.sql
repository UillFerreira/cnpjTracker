CREATE OR REPLACE FUNCTION cnpj__serpro_nfe_select (p_chave varchar) RETURNS json AS $$
DECLARE
    v_json_ret json;
BEGIN

    select json_build_object(
        'chave', chave,
        'atualizado', updated
    ) INTO v_json_ret
    FROM cnpj__nfe_list AS c;

    RETURN v_json_ret;
END;
$$ LANGUAGE plpgsql;
