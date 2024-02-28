create table cnpj__list (cnpj_uuid uuid not null default gen_random_uuid() primary key, cnpj varchar (14) not null unique, criado timestamp not null default(now()));
drop table serpro__integracao ;
create table cnpj__api_serpro(contract_uuid uuid not null primary key, servico text, key text, secret text, url text);

create table cnpj__data_cnd (cnd_uuid uuid not null default(gen_random_uuid()) primary key, cnpj_uuid uuid not null, sha1 text not null, created date not null default(now()), blob bytea not null, validity date not null, FOREIGN KEY (cnpj_uuid) REFERENCES cnpj__list(cnpj_uuid));

create table cnpj__data_cnd (cnd_uuid uuid not null default(gen_random_uuid()) primary key, cnpj_uuid uuid not null, sha1 text not null, created date not null default(now()), blob bytea not null, FOREIGN KEY (cnpj_uuid) REFERENCES cnpj__list(cnpj_uuid));

create table cnpj__serpro_log(serpro_uuid uuid not null, cnpj_uuid uuid not null references cnpj__list(cnpj_uuid), status text not null, msg text not null, created date not null default now());

select * from auth__grant_execute('login');

