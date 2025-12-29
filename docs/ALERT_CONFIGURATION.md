# Configuração de Alertas

Este documento descreve a configuração do sistema de alertas automáticos para tarefas vencendo e licenças expirando.

## Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Alert Configuration
# Número de dias antes da data de vencimento da tarefa para enviar alertas (padrão: 3)
ALERT_TASK_DAYS=3

# Número de dias antes da expiração da licença para enviar alertas (padrão: 30)
ALERT_LICENSE_DAYS=30
```

## Comando de Geração de Alertas

O sistema utiliza o comando Artisan `alerts:generate` para gerar alertas automaticamente.

### Execução Manual

Para executar o comando manualmente:

```bash
php artisan alerts:generate
```

### Agendamento Automático

O comando está configurado para executar automaticamente **diariamente às 09:00** através do Laravel Scheduler.

#### Configuração do Cron (Produção)

Para garantir que o scheduler funcione em produção, adicione a seguinte entrada ao crontab do servidor:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Substitua `/path-to-your-project` pelo caminho absoluto do seu projeto.

## Laravel Horizon

O sistema utiliza o Laravel Horizon para monitoramento de filas. Os jobs de alerta são processados através da fila.

### Acesso ao Dashboard

Após configurar o Horizon, acesse o dashboard em:

```
http://your-domain/horizon
```

### Configuração de Autenticação

Por padrão, o Horizon está acessível apenas em ambiente local. Para produção, configure a autenticação no arquivo `config/horizon.php`:

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'maxProcesses' => 10,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 60,
            'nice' => 0,
        ],
    ],
],
```

### Monitoramento

O Horizon permite monitorar:
- Jobs processados com sucesso
- Jobs falhados
- Tempo de processamento
- Throughput de jobs
- Métricas de performance

## Tipos de Alertas

### Tarefas Vencidas (`task.overdue`)

Alertas são gerados para tarefas que já passaram da data de vencimento (`due_at < now()`).

### Tarefas Próximas do Vencimento (`task.near_due`)

Alertas são gerados para tarefas que estão próximas do vencimento (`due_at <= now() + ALERT_TASK_DAYS`).

### Licenças Expirando (`license.expiring`)

*Funcionalidade futura - será implementada quando o model License for criado.*

## Estrutura de Notificações

As notificações são armazenadas na tabela `notifications` com a seguinte estrutura:

- **user_id**: ID do usuário que receberá a notificação
- **notifiable_id**: ID da tarefa/licença relacionada
- **notifiable_type**: Tipo do modelo relacionado (Task, License, etc.)
- **type**: Tipo de alerta (`task.overdue`, `task.near_due`, `license.expiring`)
- **data**: JSON com informações adicionais (task_id, task_title, due_at, project_id, project_name)
- **channels**: Array de canais de notificação (atualmente: `['database']`)
- **read_at**: Timestamp de quando a notificação foi lida (null se não lida)

## Jobs

O sistema utiliza o job `SendAlertJob` para processar alertas de forma assíncrona. Este job:

1. Recebe os dados de alertas agrupados por usuário
2. Cria notificações no banco de dados para cada tarefa/licença
3. Pode ser expandido no futuro para enviar emails ou push notifications

## Troubleshooting

### Alertas não estão sendo gerados

1. Verifique se o scheduler está rodando: `php artisan schedule:list`
2. Verifique os logs: `storage/logs/laravel.log`
3. Execute o comando manualmente para debug: `php artisan alerts:generate`

### Jobs não estão sendo processados

1. Verifique se o Horizon está rodando: `php artisan horizon`
2. Verifique a configuração da fila em `config/queue.php`
3. Verifique se o Redis está configurado e acessível
4. Acesse o dashboard do Horizon para ver jobs falhados

### Notificações duplicadas

O sistema utiliza `withoutOverlapping()` para evitar execuções simultâneas do comando. Se ainda assim houver duplicatas, verifique:

1. Se há múltiplas instâncias do scheduler rodando
2. Se o cache está funcionando corretamente
3. Se há jobs duplicados na fila

