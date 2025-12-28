@component('mail::message')
# Convite para Projeto

Você foi convidado para participar do projeto **{{ $projectName }}** com a role de **{{ $role }}**.

**Para aceitar este convite e se tornar membro do projeto, clique no botão abaixo:**

@component('mail::button', ['url' => $acceptUrl])
Aceitar Convite
@endcomponent

@if($expiresAt)
**Este convite expira em:** {{ $expiresAt }}
@endif

**Nota:** Você só será adicionado ao projeto após aceitar este convite.

Obrigado,<br>
{{ config('app.name') }}
@endcomponent

