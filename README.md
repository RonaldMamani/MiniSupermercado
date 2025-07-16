# Mini Sistema de Gestão de Supermercado

## Descrição

Sistema web simples em PHP que simula a gestão de um pequeno supermercado com controle de perfis e permissões.  
Este projeto é um desafio para prática de PHP puro, sessões, controle de acesso, manipulação de arquivos JSON e boas práticas de código.

## Perfis de Usuário

- **Caixa**: Registrar vendas, ver lista de produtos  
- **Estoque**: Visualizar produtos, inserir/atualizar/excluir produtos (com permissão)  
- **Admin**: Visualizar todos os painéis, enviar solicitações ao financeiro  
- **Financeiro**: Visualizar solicitações, aprovar ou negar permissões para cadastro

## Funcionalidades

- Login com autenticação simples (usuários mocados)  
- Controle de sessão para acesso às páginas  
- Simulação de produtos armazenados em arquivo JSON  
- Controle de permissões baseado em perfil e fluxo de liberação entre Admin e Financeiro  

## Estrutura do Projeto

Supermercado
lib
views
login.php
logout.php
index.php

## Como usar

1. Clonar o repositório  
2. Configurar servidor local (ex: XAMPP, WAMP, PHP Built-in server)  
3. Acessar localhost:[porta]/`login.php` e fazer login com um dos usuários pré-definidos  
4. Navegar pelas funcionalidades conforme perfil  
