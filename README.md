# Link Manager

Sistema para gerenciar links de redes sociais, tipo Linktree.

## Stack

- PHP 8
- MySQL/MariaDB
- HTML5/CSS3
- JavaScript (vanilla)
- SortableJS para drag-and-drop

## Instalação

### 1. Banco de dados

Rode o script SQL no MySQL:

```bash
mysql -u root -p < database.sql
```

Ou importe pelo phpMyAdmin.

### 2. Configuração

As credenciais do banco estão em `config/db.php`. Já vem configurado para XAMPP padrão:

- Host: localhost
- Database: link_manager
- User: root
- Password: (vazio)

### 3. Apache

Certifique-se que o mod_rewrite está ativo no Apache para as URLs amigáveis funcionarem.

## Como usar

1. Acesse `http://localhost/linkme/register.php`
2. Crie sua conta
3. Faça login
4. Adicione seus links no dashboard
5. Sua página fica em `http://localhost/linkme/seu-username`

## Funcionalidades

- Cadastro e login de usuários
- Adicionar/editar/excluir links
- Reordenar links arrastando
- Personalizar cores da página
- Contador de cliques
- URLs amigáveis (case-insensitive)
- Design responsivo

## Estrutura

```
/linkme/
├── config/          # Conexão com banco
├── css/             # Estilos
├── js/              # Scripts
├── includes/        # Header e footer
├── dashboard.php    # Painel do usuário
├── index.php        # Roteador e página pública
├── login.php        # Login
├── register.php     # Cadastro
├── redirect.php     # Tracking de cliques
└── ajax_handler.php # Endpoints AJAX
```

## Segurança

- Senhas com hash (password_hash/verify)
- PDO com prepared statements
- Proteção XSS com htmlspecialchars
- Validação de inputs
- Sessões protegidas

## Observações

O sistema adiciona automaticamente `https://` nas URLs que não tiverem protocolo.

Usernames devem conter apenas letras, números, hífens e underscores.
