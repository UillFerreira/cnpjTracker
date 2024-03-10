create table cnpj__data(cnpj_uuid uuid not null unique, updated timestamptz default now(), json_data jsonb not null, FOREIGN KEY (cnpj_uuid) REFERENCES cnpj__list(cnpj_uuid));
select * from auth__grant_execute('login');
