🛍️ API de Produtos (PHP Puro)
📖 Descrição

Este projeto é uma API REST de gerenciamento de produtos, desenvolvida em PHP puro, com autenticação JWT, controle de acesso por permissões (usuário e administrador), e uma arquitetura organizada no padrão MVC (Controller → Service → Model).

Foi criada com o objetivo de praticar o desenvolvimento backend moderno, aplicando boas práticas como:

Separação de camadas,

Uso de middlewares para autenticação e autorização,

Rollback em transações,

Configuração via .env para segurança e portabilidade,

E carregamento automático de classes com Composer Autoload.

⚙️ Funcionalidades Principais

🔐 Autenticação JWT

Registro (register) e login (login) de usuários.

Geração e validação de token JWT em todas as rotas protegidas.

👑 Controle de Permissões

Middleware que valida o role (usuário ou administrador).

Rotas exclusivas para administradores.

💳 Compra de Produtos

O usuário só pode comprar produtos com a própria conta.

Verificação automática entre o id do comprador e o id do token JWT.

Rollback automático se alguma regra de negócio falhar.

🧠 Recomendação de Produtos

Endpoint que recomenda produtos com base nas compras anteriores do usuário.

🧩 Estrutura MVC

Controller: recebe as requisições HTTP.

Service: executa regras de negócio.

Model: interage com o banco de dados via PDO.

Middleware: autenticação, autorização e validações.

🧱 Estrutura de Pastas
📁 raiz_do_projeto/
├── app/
├── controller/
├── core/
│   ├── Database.php
│   └── ...
├── helpers/
├── http/
│   └── route.php
├── middleware/
├── models/
├── services/
├── utils/
├── public/
│   ├── .htaccess
│   └── index.php
├── routes/
│   └── api.php
├── vendor/
├── composer.json
├── composer.autoload.php
└── .env

🔒 Configuração Segura com .env

O projeto usa a biblioteca vlucas/phpdotenv
 para gerenciar variáveis de ambiente.
Assim, senhas e informações sensíveis não ficam no código-fonte.

📄 Exemplo de arquivo .env
DB_HOST=localhost
DB_NAME=api_produtos
DB_USER=root
DB_PASS=2400
DB_CHARSET=utf8mb4


⚠️ O arquivo .env deve estar na raiz do projeto e não deve ser versionado no Git.

📄 .gitignore
/vendor/
/cache/
/logs/
/tmp/
/storage/
/session/
/.vscode/
/.idea/
.env

⚙️ Instalação e Execução
🔧 Pré-requisitos

PHP 8.0+

Composer

MySQL ou MariaDB

Extensão pdo_mysql habilitada

🪜 Passos de instalação

Clonar o repositório

git clone https://github.com/IgorDorigan/nome-do-repositorio.git
cd nome-do-repositorio


Instalar as dependências

composer install


Criar e configurar o .env

cp .env.example .env


Edite com suas credenciais do banco de dados.

Executar o servidor local

php -S localhost:8000 -t public


Testar a API

As rotas estão em routes/api.php.

Use Postman ou Insomnia para testar.


🧰 Tecnologias e Conceitos

PHP 8+

Composer Autoload

PDO

JWT (JSON Web Token)

vlucas/phpdotenv – gerenciamento de variáveis de ambiente

Arquitetura MVC

Injeção de Dependência

Middlewares

Rollback em Transações


🔮 Próximos Passos

🧩 Implementar injeção de dependência via construtor;

🧹 Aplicar Clean Code e SOLID;

📘 Documentar todos os endpoints (Swagger/Postman);

🧪 Criar testes automatizados;

⚙️ Migrar futuramente para Laravel, mantendo a mesma lógica e arquitetura.


💬 Conclusões do Desenvolvedor

“Por ser feito em PHP puro, esse projeto exigiu planejamento, paciência e refatorações constantes.
Aprendi sobre autenticação JWT, middlewares, rollback, injeção de dependências e boas práticas de arquitetura backend.
Agora, com o uso do .env e phpdotenv, o código ficou mais seguro, limpo e profissional.
Ainda pretendo refatorar o projeto futuramente para aplicar injeção de dependências via construtor, princípios SOLID e Clean Code.”


👨‍💻 Autor

Igor Dorigan
Desenvolvedor Backend e estudante de Cibersegurança.
📅 Estudando PHP há 1 mês.
🔗 GitHub: github.com/IgorDorigan


