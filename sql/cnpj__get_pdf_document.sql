CREATE OR REPLACE FUNCTION cnpj__get_pdf_document (p_cnpj varchar) RETURNS bytea AS $$
DECLARE
    v_data bytea;
    v_validity date;
BEGIN
    select max(validity), d.blob INTO v_validity, v_data  FROM cnpj__list AS c INNER JOIN cnpj__data_cnd AS d ON (d.cnpj_uuid = c.cnpj_uuid) WHERE c.cnpj = p_cnpj GROUP BY d.blob;
    RETURN v_data;
END;
$$ LANGUAGE plpgsql;
