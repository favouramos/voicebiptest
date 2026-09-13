# Sophia The Promoter — Appointment Booking Agent

A lightweight PHP + MySQL appointment dashboard designed for Voicebip custom tool-calling.

## How the live flow works

Customer calls Voicebip → AI collects booking details → AI checks the requested slot → AI saves the confirmed appointment through `api/voicebip.php` → MySQL stores it → the admin dashboard shows it.

Voicebip's custom tools are dispatched to the agent's `webhook_url` as `tool.invocation` requests. The webhook is HMAC-SHA256 signed using the Voicebip workspace signing secret.

## Upload / setup

1. Unzip the folder into the document root for `calling.sophiathepromoter.com`.
2. Open `/install.php`.
3. Enter your MySQL host, database name, database username/password, admin login, Voicebip API key, Voicebip Agent ID, and Voicebip Workspace Signing Secret.
4. Complete installation.
5. Delete `install.php` immediately.
6. Open the home page and log in.

## Voicebip configuration

Use the contents of `system-prompt.txt` as the agent's System Prompt.

Use `voicebip-tool-definition.json` as the custom `tool_definitions` value. Voicebip expects this as a stringified JSON array in the agent configuration.

Set the agent's webhook URL to:

`https://calling.sophiathepromoter.com/api/voicebip.php`

The agent must use a hosted AI provider for tool calling.

## Existing agent update

Voicebip supports updating an existing agent with PATCH. Apply the webhook URL, system prompt, and tool definitions to your current Appointment Booking Agent.

Example request shape:

```bash
curl -X PATCH "https://api.voicebip.com/v1/agents/agt_YOUR_AGENT_ID" \
  -H "Authorization: Bearer pk_live_YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d @agent-update.json
```

An `agent-update.json` example is included below.

## Important configuration locations

All private credentials are stored in `config.php`, created by `install.php`:

- `db.name` = your database name
- `db.user` = your database user
- `db.pass` = your database password
- `voicebip.api_key` = Voicebip API key
- `voicebip.agent_id` = your Appointment Booking Agent ID
- `voicebip.signing_secret` = Voicebip workspace signing secret

## Production notes

- Use HTTPS.
- Delete `install.php` after setup.
- Keep `config.php` protected; Apache `.htaccess` protection is included.
- The webhook rejects requests with missing/invalid Voicebip signatures or timestamps older than 5 minutes.
- Appointment slots have a unique database constraint to reduce double-booking.
- Voicebip event IDs and tool-call IDs are stored to make retries safer.
