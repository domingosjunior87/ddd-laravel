# Sistema de Autenticação com Laravel e DDD

API Rest de autenticação desenvolvida em Laravel 8 utilizando os princípios do DDD. O Frontend é uma aplicação VueJS que está associada como um submódulo desse repositório. Você pode verificar o respositório da aplicação VueJS [aqui](https://github.com/domingosjunior87/vue-auth).

## Tecnologias
- PHP 8.0
- Laravel 8
- VueJS
- MySql

## Instalação
Após fazer o clone desse repositório, execute o docker-compose para iniciar o ambiente:

    docker-compose up

Quando o ambiente estiver funcionando, execute os comandos abaixo para instalar as dependências da aplicação Laravel e criar o banco de dados:

    docker exec -it laravel-php bash
    composer install
    php artisan migrate

Após executar todos os passos, é só acessar o seguinte endereço no seu navegador:

    http://locahost:8080