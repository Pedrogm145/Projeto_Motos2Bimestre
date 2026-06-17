# Publicar o Projeto em Hospedagem Gratis

## Recomendacao

Para este projeto, use uma hospedagem gratis com PHP e MySQL, como a InfinityFree.

## Passos

1. Crie uma conta na hospedagem.
2. Crie um site usando um subdominio gratis.
3. No painel da hospedagem, crie um banco MySQL.
4. Abra o phpMyAdmin da hospedagem e importe o arquivo `database.sql`.
5. Copie `conexao/config.example.php` para `conexao/config.local.php`.
6. Edite `conexao/config.local.php` com os dados reais do banco da hospedagem.
7. Envie todos os arquivos do projeto para a pasta publica da hospedagem, normalmente `htdocs`.
8. Acesse o dominio gerado pela hospedagem e teste cadastro, login e paginas administrativas.

## Tornar um usuario administrador

Depois de criar uma conta pelo site, abra o phpMyAdmin e execute:

```sql
UPDATE users SET is_admin = 1 WHERE email = 'seu-email@exemplo.com';
```

Troque o email pelo email usado no cadastro.

## Observacao

No XAMPP local, o projeto continua funcionando com banco `usuarios`, usuario `root` e senha vazia se o arquivo `conexao/config.local.php` nao existir.
