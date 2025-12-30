# Feature: Expo Push Notifications

Este documento descreve a funcionalidade de **Expo Push Notifications** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Modelo de Dados](#modelo-de-dados)
4. [Casos de Uso](#casos-de-uso)
5. [API Endpoints](#api-endpoints)
6. [Regras de Negócio](#regras-de-negócio)
7. [Integração Frontend](#integração-frontend)
8. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

**Expo Push Notifications** permite o envio de notificações push para dispositivos móveis através da API do Expo. O sistema integra com o sistema de notificações existente para enviar alertas em tempo real aos usuários.

### Objetivos

- Enviar notificações push para dispositivos móveis quando eventos importantes ocorrem
- Integrar com o sistema de notificações existente (database notifications)
- Permitir que usuários registrem seus tokens Expo para receber notificações
- Fornecer feedback em tempo real sobre tarefas, alertas e eventos do sistema

### Características Principais

- ✅ Armazenamento de tokens Expo Push no modelo User
- ✅ Serviço dedicado para envio de push notifications via Expo API
- ✅ Integração automática com SendAlertJob para alertas de tarefas
- ✅ Validação de formato de token Expo
- ✅ Suporte a múltiplas notificações em batch
- ✅ Tratamento de erros e logging adequado
- ✅ Suporte a opções avançadas (sound, badge, priority)

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
User
  └── expo_push_token (campo direto)
      └── ExpoPushService (envia notificações)
          └── Expo API (https://exp.host/--/api/v2/push/send)

Notification (sistema existente)
  └── channels: ['database', 'expo']
      └── SendAlertJob (dispara push quando há token)
```

### Relacionamentos

#### User → ExpoPushToken (Opcional)
- **Tipo**: Campo direto no modelo User
- **Cardinalidade**: 1:1 (um usuário pode ter um token)
- **Campo**: `expo_push_token` (nullable)
- **Descrição**: Token Expo Push do dispositivo móvel do usuário

#### ExpoPushService → Expo API (Externo)
- **Tipo**: Integração HTTP
- **Endpoint**: `POST https://exp.host/--/api/v2/push/send`
- **Descrição**: Serviço responsável por enviar notificações via API do Expo

### Fluxo Conceitual

```
1. Registro do Token
   └── Usuário obtém token do Expo no app mobile
       └── POST /api/v1/user/expo-token
           └── Token armazenado em User.expo_push_token

2. Evento no Sistema
   └── SendAlertJob é disparado (ex: tarefa atrasada)
       └── Cria Notification no banco
           └── Se User tem expo_push_token:
               └── ExpoPushService.sendPush()
                   └── Notificação enviada via Expo API
                       └── Usuário recebe push no dispositivo
```

---

## 📊 Modelo de Dados

### Tabela: `users` (campo adicionado)

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `expo_push_token` | string(255) | Token Expo Push do dispositivo | Não | Nullable, formato: ExponentPushToken[...] ou ExpoPushToken[...] |

### Índices

- Não há índices específicos para `expo_push_token` (campo opcional, baixa cardinalidade)

### Constraints

- Formato do token deve ser validado via `ExpoPushService::isValidToken()`
- Token deve seguir padrão: `ExponentPushToken[...]` ou `ExpoPushToken[...]`

---

## 💼 Casos de Uso

### Caso 1: Registrar Token Expo Push

**Cenário**: Usuário abre o app mobile pela primeira vez e precisa registrar seu token para receber notificações.

```json
POST /api/v1/user/expo-token
{
  "expo_push_token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
}
```

**Resultado**: Token armazenado no perfil do usuário. Usuário passará a receber push notifications quando eventos ocorrerem.

---

### Caso 2: Atualizar Token Expo Push

**Cenário**: Usuário reinstalou o app ou mudou de dispositivo, gerando um novo token.

```json
POST /api/v1/user/expo-token
{
  "expo_push_token": "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]"
}
```

**Resultado**: Token antigo substituído pelo novo. Notificações futuras serão enviadas para o novo dispositivo.

---

### Caso 3: Receber Notificação de Tarefa Atrasada

**Cenário**: Sistema detecta tarefa atrasada e envia push notification automaticamente.

```
1. SendAlertJob é executado
2. Notification criada no banco (type: 'task.overdue')
3. Sistema verifica se User tem expo_push_token
4. ExpoPushService.sendPush() é chamado
5. Push notification enviada via Expo API
6. Usuário recebe notificação no dispositivo
```

**Conteúdo da Notificação:**
- **Título**: "Tarefa Atrasada" (ou "N Tarefas Atrasadas" se múltiplas)
- **Corpo**: Nome da tarefa e projeto
- **Data**: ID da notificação, tipo e dados relacionados

---

### Caso 4: Receber Notificação de Tarefa Próxima do Vencimento

**Cenário**: Sistema detecta tarefa próxima do vencimento (2 dias).

```
1. SendAlertJob é executado
2. Notification criada (type: 'task.near_due')
3. Push notification enviada automaticamente
```

**Conteúdo da Notificação:**
- **Título**: "Tarefa Próxima do Vencimento"
- **Corpo**: Nome da tarefa e data de vencimento

---

### Caso 5: Múltiplas Notificações em Batch

**Cenário**: Usuário tem várias tarefas atrasadas. Sistema envia uma notificação consolidada.

```
1. SendAlertJob detecta 5 tarefas atrasadas
2. Cria 5 Notifications no banco
3. Envia 1 push notification com título: "5 Tarefas Atrasadas"
4. Corpo: "Você tem 5 novas notificações"
```

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/user/expo-token
```

### Endpoints Disponíveis

#### 1. Registrar/Atualizar Token Expo Push

```http
POST /api/v1/user/expo-token
```

**Headers:**
- `Authorization: Bearer {token}` (obrigatório)
- `Content-Type: application/json`

**Body:**
```json
{
  "expo_push_token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
}
```

**Validações:**
- `expo_push_token` é obrigatório
- Token deve seguir formato válido (ExponentPushToken[...] ou ExpoPushToken[...])
- Token máximo 255 caracteres

**Resposta (200 OK):**
```json
{
  "message": "Expo push token updated successfully",
  "data": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "expo_push_token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
    ...
  }
}
```

**Códigos HTTP:**
- `200` - Token atualizado com sucesso
- `401` - Não autenticado
- `422` - Erro de validação (token inválido ou formato incorreto)

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso ao endpoint requer:**
- Autenticação via Sanctum (`auth:sanctum`)
- Usuário pode atualizar apenas seu próprio token

**Outras roles:** Não há restrições de role específicas. Qualquer usuário autenticado pode registrar seu token.

### Validações

#### Validação de Formato de Token

1. **Token válido (ExponentPushToken)**: ✅ Aceito
2. **Token válido (ExpoPushToken)**: ✅ Aceito
3. **Token inválido**: ❌ Retorna 422 com mensagem de erro
4. **Token vazio**: ❌ Retorna 422 (campo obrigatório)

#### Validação de Integração

- **Usuário sem token**: Notificações são criadas no banco, mas push não é enviado
- **Usuário com token**: Push é enviado automaticamente quando notificações são criadas
- **Erro no envio**: Erro é logado, mas não interrompe o processo (notificação ainda é criada no banco)

### Integração com Sistema de Notificações

#### Canais de Notificação

As notificações podem usar múltiplos canais:
- `database`: Sempre presente (notificação salva no banco)
- `expo`: Adicionado quando usuário tem `expo_push_token`

#### Envio Automático

Push notifications são enviadas automaticamente quando:
1. `SendAlertJob` é executado
2. Usuário tem `expo_push_token` preenchido
3. Há notificações para criar (tarefas atrasadas, próximas do vencimento, etc.)

#### Opções de Notificação

Cada push notification inclui:
- `sound`: 'default' (som padrão do dispositivo)
- `badge`: Contador de notificações não lidas do usuário
- `data`: Dados adicionais (notification_id, type, dados relacionados)

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/user.ts

export interface User {
  id: number;
  name: string;
  email: string;
  expo_push_token: string | null;
  // ... outros campos
}

export interface UpdateExpoTokenInput {
  expo_push_token: string;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/userService.ts

import { User, UpdateExpoTokenInput } from '@/types/user';

export const userService = {
  /**
   * Registra ou atualiza o token Expo Push do usuário
   */
  async updateExpoToken(data: UpdateExpoTokenInput): Promise<User> {
    const response = await api.post('/user/expo-token', data);
    return response.data.data;
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useExpoToken.ts

import { useMutation, useQueryClient } from '@tanstack/react-query';
import { userService } from '@/services/userService';
import { UpdateExpoTokenInput } from '@/types/user';

export function useUpdateExpoToken() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateExpoTokenInput) => userService.updateExpoToken(data),
    onSuccess: () => {
      // Invalidar cache do usuário para refletir mudança
      queryClient.invalidateQueries({ queryKey: ['user', 'me'] });
    },
  });
}
```

### Exemplo de Integração no App Mobile (React Native/Expo)

```typescript
// hooks/useExpoPushToken.ts

import { useEffect } from 'react';
import * as Notifications from 'expo-notifications';
import { useUpdateExpoToken } from '@/hooks/useExpoToken';
import { Platform } from 'react-native';

export function useExpoPushToken() {
  const updateExpoToken = useUpdateExpoToken();

  useEffect(() => {
    async function registerForPushNotifications() {
      // Verificar se é dispositivo físico (não funciona em emulador)
      if (Platform.OS === 'android' || Platform.OS === 'ios') {
        const { status: existingStatus } = await Notifications.getPermissionsAsync();
        let finalStatus = existingStatus;

        // Solicitar permissão se não tiver
        if (existingStatus !== 'granted') {
          const { status } = await Notifications.requestPermissionsAsync();
          finalStatus = status;
        }

        // Se permissão negada, não continuar
        if (finalStatus !== 'granted') {
          console.warn('Permissão de notificações negada');
          return;
        }

        // Obter token do Expo
        const tokenData = await Notifications.getExpoPushTokenAsync({
          projectId: 'your-expo-project-id', // Substituir pelo ID do projeto Expo
        });

        const token = tokenData.data;

        // Registrar token no backend
        try {
          await updateExpoToken.mutateAsync({
            expo_push_token: token,
          });
          console.log('Token Expo registrado com sucesso');
        } catch (error) {
          console.error('Erro ao registrar token:', error);
        }
      }
    }

    registerForPushNotifications();
  }, []);
}
```

### Exemplo de Componente (React Native)

```typescript
// components/PushNotificationSetup.tsx

import { useEffect, useState } from 'react';
import { View, Text, Button, Alert } from 'react-native';
import * as Notifications from 'expo-notifications';
import { useExpoPushToken } from '@/hooks/useExpoPushToken';

export function PushNotificationSetup() {
  const [permissionStatus, setPermissionStatus] = useState<string | null>(null);
  const { updateExpoToken } = useUpdateExpoToken();

  useEffect(() => {
    checkPermissionStatus();
  }, []);

  async function checkPermissionStatus() {
    const { status } = await Notifications.getPermissionsAsync();
    setPermissionStatus(status);
  }

  async function requestPermission() {
    const { status } = await Notifications.requestPermissionsAsync();
    setPermissionStatus(status);

    if (status === 'granted') {
      const tokenData = await Notifications.getExpoPushTokenAsync({
        projectId: 'your-expo-project-id',
      });

      try {
        await updateExpoToken.mutateAsync({
          expo_push_token: tokenData.data,
        });
        Alert.alert('Sucesso', 'Notificações push ativadas!');
      } catch (error) {
        Alert.alert('Erro', 'Não foi possível registrar o token');
      }
    } else {
      Alert.alert('Permissão Negada', 'Você precisa permitir notificações para receber alertas');
    }
  }

  if (permissionStatus === 'granted') {
    return (
      <View>
        <Text>✅ Notificações push ativadas</Text>
      </View>
    );
  }

  return (
    <View>
      <Text>Notificações push não estão ativadas</Text>
      <Button title="Ativar Notificações" onPress={requestPermission} />
    </View>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Registrar Token ao Fazer Login

```typescript
// No componente de login ou após autenticação bem-sucedida

import { useExpoPushToken } from '@/hooks/useExpoPushToken';

function LoginScreen() {
  const { login } = useAuth();
  useExpoPushToken(); // Registra token automaticamente quando componente monta

  // ... resto do componente
}
```

### Exemplo 2: Verificar se Token Está Registrado

```typescript
import { useQuery } from '@tanstack/react-query';
import { userService } from '@/services/userService';

function useUserProfile() {
  return useQuery({
    queryKey: ['user', 'me'],
    queryFn: () => userService.getCurrentUser(),
  });
}

function NotificationSettings() {
  const { data: user } = useUserProfile();
  const hasToken = !!user?.expo_push_token;

  return (
    <View>
      {hasToken ? (
        <Text>✅ Notificações push ativadas</Text>
      ) : (
        <Text>⚠️ Notificações push não configuradas</Text>
      )}
    </View>
  );
}
```

### Exemplo 3: Atualizar Token Manualmente

```typescript
import { useUpdateExpoToken } from '@/hooks/useExpoToken';
import * as Notifications from 'expo-notifications';

function RefreshTokenButton() {
  const updateToken = useUpdateExpoToken();

  async function handleRefresh() {
    try {
      const tokenData = await Notifications.getExpoPushTokenAsync({
        projectId: 'your-expo-project-id',
      });

      await updateToken.mutateAsync({
        expo_push_token: tokenData.data,
      });

      Alert.alert('Sucesso', 'Token atualizado');
    } catch (error) {
      Alert.alert('Erro', 'Não foi possível atualizar o token');
    }
  }

  return <Button title="Atualizar Token" onPress={handleRefresh} />;
}
```

---

## 🔍 Queries Úteis para Frontend

### Verificar Status de Permissão

```typescript
import * as Notifications from 'expo-notifications';

async function checkNotificationPermission() {
  const { status } = await Notifications.getPermissionsAsync();
  return status === 'granted';
}
```

### Obter Token Expo Atual

```typescript
import * as Notifications from 'expo-notifications';

async function getCurrentExpoToken() {
  const tokenData = await Notifications.getExpoPushTokenAsync({
    projectId: 'your-expo-project-id',
  });
  return tokenData.data;
}
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Escopo**: Usuário pode atualizar apenas seu próprio token
- **Validação**: Token deve seguir formato válido do Expo

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Formato do token**: Validar antes de enviar (regex: `/^(ExponentPushToken|ExpoPushToken)\[.+\]$/`)
2. **Permissão de notificações**: Verificar se usuário concedeu permissão antes de solicitar token
3. **Erro de registro**: Tratar erros de rede ou validação adequadamente

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Receipts de Notificação**: Implementar verificação de receipts do Expo para confirmar entrega
2. **Configurações de Notificação**: Permitir usuário escolher tipos de notificação que deseja receber
3. **Notificações Agendadas**: Suporte a notificações agendadas (ex: lembrete de tarefa)
4. **Notificações em Lote**: Otimizar envio de múltiplas notificações usando batch API
5. **Métricas**: Tracking de taxa de entrega e abertura de notificações

### Considerações para Implementação

- **Receipts**: Expo fornece endpoint para verificar status de entrega
- **Configurações**: Criar tabela `user_notification_preferences` para armazenar preferências
- **Agendamento**: Usar Laravel Scheduler para notificações futuras

---

## 📚 Referências

- [Expo Push Notifications Documentation](https://docs.expo.dev/push-notifications/overview/)
- [Expo Push API Reference](https://docs.expo.dev/push-notifications/sending-notifications/)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Service: `app/Services/ExpoPushService.php`
- Controller: `app/Http/Controllers/MeController.php` (método `updateExpoToken`)
- Tests: `tests/Unit/ExpoPushServiceTest.php`, `tests/Feature/ExpoTokenTest.php`

---

## ❓ FAQ

### P: O token Expo expira?

**R:** Não, os tokens Expo são permanentes. No entanto, se o usuário desinstalar e reinstalar o app, um novo token será gerado e o antigo ficará inválido.

### P: Posso ter múltiplos tokens para o mesmo usuário?

**R:** Atualmente, o sistema suporta apenas um token por usuário. Se o usuário registrar um novo token, o anterior será substituído.

### P: O que acontece se o envio de push falhar?

**R:** O erro é logado, mas o processo continua. A notificação ainda é criada no banco de dados (canal 'database'). O sistema não tenta reenviar automaticamente.

### P: Push notifications funcionam em emuladores?

**R:** Não. Push notifications do Expo só funcionam em dispositivos físicos. Em emuladores, o token pode ser gerado, mas as notificações não serão recebidas.

### P: Como testar push notifications localmente?

**R:** Use um dispositivo físico com o app instalado. O Expo fornece tokens de teste que podem ser usados para desenvolvimento.

### P: Posso desabilitar push notifications?

**R:** Sim. O usuário pode simplesmente não registrar um token ou remover o token existente (atualizando com `null` - requer implementação futura).

---

**Última atualização:** 2025-12-30  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

