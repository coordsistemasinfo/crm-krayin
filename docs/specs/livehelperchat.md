# Live Helper Chat — análisis de integración

> Estado: análisis listo. El paso siguiente es el spec detallado de la Opción B
> y luego el paquete `packages/Webkul/HelperChat` (nombre por decidir).

- Fuente: https://livehelperchat.com/ — chat de soporte open source autoalojado.
- Documentación API: https://doc.livehelperchat.com (REST API con Swagger en
  https://api.livehelperchat.com, API keys, webhooks entrantes/salientes, bots).

## Capacidades relevantes de LHC

- **REST API**: clave de API creada en *System configuration → REST API*; se
  puede limitar a chats (`lhchat, use`) y a departamentos. Exporta chats en JSON.
- **Webhooks salientes**: eventos como `chat.close`, `chat.web_add_msg_admin`,
  con condiciones y placeholders (`{{args.chat.id}}`), ejecutados en background.
- **Incoming webhooks** (entrantes): estructura propia para integrar terceros;
  ya trae integraciones nativas de **WhatsApp (Twilio y open-wa)**, Telegram,
  Facebook, Discord, etc.
- **Bots** con triggers que pueden llamar REST APIs externas sin código.

## Opciones evaluadas

| Opción | Descripción | Esfuerzo | Contras |
|---|---|---|---|
| A — Solo embeber | widget JS/iframe de LHC dentro del admin | ~1 día | sin vínculo con registros del CRM |
| **B — Plugin de referencias (recomendada)** | paquete Krayin que consulta la REST API de LHC | media | requiere LHC ya instalado |
| C — Bidireccional completa | transcripciones como actividades, envío de datos del lead a LHC vía bot | alta | alcance grande para una primera fase |

## Opción B — alcance propuesto (referencia para el spec)

1. **Settings**: URL del LHC + API key (con permiso de solo lectura).
2. **Vinculación automática**: match por email/teléfono entre chats de LHC y
   `persons` / `leads` del CRM.
3. **Panel "Chats"** en la vista de persona/lead: conversaciones recientes de
   esa persona (API `chats`), con enlace directo al chat en LHC.
4. **Webhook LHC → Krayin** (`chat.close` / `chat.web_add_msg_admin`): endpoint
   público (patrón `admin.mail.inbound_parse` en `mail-routes.php:25` con
   `withoutMiddleware`) que registra el chat como actividad vinculada.

## Observaciones clave

- **LHC puede absorber WhatsApp**: trae WhatsApp (Twilio/open-wa) nativo. Si
  WhatsApp opera *a través de* LHC, el plugin independiente de WhatsApp podría
  no hacer falta — el plugin LHC referenciaría chats web, WhatsApp y Telegram
  desde un solo lugar.
- LHC también soporta voz/video (Agora/Jitsi), pero la telefonía real (PBX)
  sigue siendo el plugin VoIP aparte.

## Pendientes para el spec

- ¿LHC ya instalado? URL, versión, si comparte servidor con el CRM.
- Alcance exacto de la Opción B (¿qué pasa si un chat no matchea a ninguna
  persona? ¿se crea la persona automáticamente?).
- Permisos/ACL del panel de chats en el admin.
