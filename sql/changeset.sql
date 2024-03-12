create table cnpj__data(cnpj_uuid uuid not null unique, updated timestamptz default now(), json_data jsonb not null, FOREIGN KEY (cnpj_uuid) REFERENCES cnpj__list(cnpj_uuid));
create table cnpj__nfe_list(chave_uuid uuid not null default gen_random_uuid() primary key, chave varchar(44) unique not null, updated timestamptz default now(), json_data jsonb not null, sha1 text not null unique);
drop table cnpj__serpro_log;
create table cnpj__serpro_log (contract_uuid uuid not null references cnpj__api_serpro (contract_uuid), key_uuid uuid not null, msg text, created timestamptz not null default now());
drop function cnpj__serpro_cnd_select ( character varying);
select * from auth__grant_execute('login');
