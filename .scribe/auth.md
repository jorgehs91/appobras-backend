# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Para obter um token de autenticação, faça login através do endpoint <code>POST /api/v1/auth/login</code> fornecendo suas credenciais (email e senha). O token retornado deve ser incluído no header <code>Authorization</code> no formato <code>Bearer {token}</code>.
