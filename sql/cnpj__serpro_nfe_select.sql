CREATE OR REPLACE FUNCTION cnpj__serpro_nfe_select (p_chave varchar) RETURNS json AS $$
DECLARE
    v_cnpj_uuid uuid;
    v_json_ret json;
BEGIN

    select json_build_object(
        'DataValidade', max(validity),
        'Status', l.status,
        'Mensagem', l.msg,
        'DataEmissao', l.created,
        'cnpj_uuid', c.cnpj_uuid
    ) INTO v_json_ret
    FROM cnpj__list AS c
    INNER JOIN cnpj__data_cnd AS d ON (d.cnpj_uuid = c.cnpj_uuid)
    INNER JOIN cnpj__serpro_log as l ON (l.serpro_uuid = d.cnd_uuid)  WHERE c.cnpj = p_cnpj GROUP BY l.status, l.msg, l.created, c.cnpj_uuid;

    RETURN v_json_ret;
END;
$$ LANGUAGE plpgsql;
