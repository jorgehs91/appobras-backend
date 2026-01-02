# Introduction

API REST para gerenciamento de obras e projetos de construção civil.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

    Esta documentação fornece todas as informações necessárias para trabalhar com nossa API.

    <aside>Conforme você navega, verá exemplos de código para trabalhar com a API em diferentes linguagens de programação na área à direita (ou como parte do conteúdo no mobile).
    Você pode alternar a linguagem usada com as abas no canto superior direito (ou do menu de navegação no canto superior esquerdo no mobile).</aside>

    ## Autenticação

    A API utiliza Laravel Sanctum para autenticação via Bearer Token. A maioria dos endpoints requer autenticação.

    Para obter um token, faça login através do endpoint `/api/v1/auth/login`.

    ## Headers Obrigatórios

    - `Authorization: Bearer {token}` - Token de autenticação (obrigatório para endpoints protegidos)
    - `X-Company-Id: {company_id}` - ID da empresa no contexto (obrigatório para muitos endpoints)
    - `Content-Type: application/json`
    - `Accept: application/json`

