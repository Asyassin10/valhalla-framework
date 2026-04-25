# MCP-Style Agents

Valhalla ships a local MVP for agent-like microservices using newline-delimited JSON over TCP.

## Install and Start

```bash
./bin/valhalla agent:install summarizer 9501
./bin/valhalla agent:start summarizer
./bin/valhalla agent:list
```

## Call an Agent

```bash
./bin/valhalla agent:call summarizer summarize
```

The response shape is:

```json
{
  "id": "request-id",
  "status": "ok",
  "result": {
    "task": "summarize",
    "payload": {
      "source": "cli"
    }
  },
  "error": null
}
```

## Extend a Handler

Replace `EchoAgentHandler` with a custom class implementing:

```php
interface AgentTaskHandler
{
    public function handle(string $task, array $payload = []): array;
}
```
