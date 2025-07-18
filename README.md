
# Mini Sistema de Gestão de Supermercado

## Descrição

Sistema web simples em PHP que simula a gestão de um pequeno supermercado, com foco em controle de vendas, estoque e permissões de acesso baseadas em perfis de usuário.

Este projeto foi desenvolvido para a prática de **PHP puro**, utilizando **sessões** para controle de acesso, interagindo com um **banco de dados MySQL (via PDO)** para persistência de dados, e implementando conceitos de **controle de permissões** entre diferentes perfis de usuário. Prioriza boas práticas de organização de código e segurança básica.

---

## Perfis de Usuário

O sistema define os seguintes perfis, cada um com suas responsabilidades e acessos:

* **Caixa**: Responsável por registrar vendas, visualizar a lista de produtos disponíveis e selecionar clientes para as vendas.
* **Estoque**: Focado na gestão de produtos, podendo visualizar, inserir, atualizar e realizar a exclusão lógica de produtos (requer permissão para algumas operações).
* **Admin**: Possui acesso a todos os painéis de visualização e é responsável por enviar solicitações de permissão para o perfil Financeiro.
* **Financeiro**: Visualiza e gerencia as solicitações de permissão enviadas pelos Admins, podendo aprová-las ou negá-las, liberando ou bloqueando funcionalidades específicas.

---

## Funcionalidades

O sistema oferece as seguintes funcionalidades principais:

* **Login e Autenticação**: Sistema de login com autenticação de usuário e controle de sessão para gerenciar o acesso às diferentes áreas do sistema.
* **Gestão de Produtos**:
    * Listagem completa de produtos.
    * Adição, edição e exclusão lógica de produtos.
    * Verificação de existência de produtos por nome e preço.
    * Reativação de produtos previamente desativados.
* **Controle de Estoque**: Integração entre a gestão de produtos e o estoque, com atualização automática da quantidade disponível após as vendas.
* **Gestão de Clientes**:
    * Listagem de clientes.
    * Seleção de cliente para associar a uma venda.
* **Processamento de Vendas (Caixa)**:
    * Interface de caixa para adição de múltiplos produtos ao carrinho.
    * Cálculo automático do subtotal por item e total da venda.
    * **Transações de Venda Seguras**: Utiliza transações de banco de dados (PDO) para garantir que todas as etapas de uma venda (registro da venda, inserção de itens vendidos, atualização de estoque) sejam concluídas com sucesso ou revertidas em caso de falha.
    * Mensagens de feedback detalhadas sobre o status da venda (sucesso, estoque insuficiente, etc.).
* **Controle de Permissões**: Sistema de solicitação e aprovação/negação de permissões entre os perfis Admin e Financeiro para certas ações.

---

--

## Como Usar

Para configurar e executar o sistema em seu ambiente local:

1.  **Clonar o Repositório**:
    ```bash
    git clone https://github.com/RonaldMamani/MiniSupermercado.git
    cd MiniSupermercado
    ```
2.  **Configurar o Servidor Local**:
    * Certifique-se de ter um ambiente LAMP/XAMPP/WAMP (Apache, MySQL, PHP) configurado.
    * Coloque a pasta `SuperMercado` no diretório de documentos do seu servidor web (ex: `htdocs` para XAMPP).
3.  **Configurar o Banco de Dados**:
    * Crie um banco de dados MySQL para o projeto.
    * Importe o esquema do banco de dados (as tabelas) para o seu novo banco de dados. **Você precisará criar suas tabelas manualmente com base nas consultas SQL que foram discutidas (produtos, clientes, vendas, venda_produtos, estoque, usuários, perfis, solicitações).**
    * Atualize as configurações de conexão com o banco de dados no arquivo `includes/config.php` (host, nome do DB, usuário, senha) e as constantes de nome de tabelas (`TB_PR`, `TB_CL`, `TB_VD`, `TB_VP`, etc.).
4.  **Acessar o Sistema**:
    * Abra seu navegador e acesse: `http://localhost/8080/login_form.php`
    * Faça login com um dos usuários pré-definidos (certifique-se de ter inserido usuários de teste no seu banco de dados, caso contrário, o login não funcionará).
5.  **Navegar pelas Funcionalidades**:
    * Explore as diferentes funcionalidades do sistema conforme o perfil de usuário logado.

---

**Observações**:

* Este projeto utiliza **Bootstrap 5.3.3** via CDN para estilização e ícones do **Bootstrap Icons**. Não há arquivos CSS ou JavaScript customizados no projeto.
* A gestão de usuários e senhas é simplificada para fins de demonstração e prática. Em um ambiente de produção, medidas de segurança mais robustas seriam necessárias.