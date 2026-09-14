# Plugin WhatsApp — análisis (borrador)

> Estado: borrador. Pendiente definir proveedor y alcance antes del spec.
> **Importante**: evaluar si se consolida a través de Live Helper Chat
> (ver [livehelperchat.md](livehelperchat.md)).

## Opciones de proveedor (sin decidir)

| Proveedor | Pros | Contras |
|---|---|---|
| **WhatsApp Business Cloud API** (Meta) | oficial, plantillas de alta calidad, free tier de conversaciones | requiere Meta Business Manager, webhook configurado en Meta |
| Twilio WhatsApp | API simple, ya integrado en LHC | costo por mensaje |
| WATI / BSPs | dashboard listo | licencia por asiento |
| Baileys (open-wa, cuenta QR) | gratis, rápido | contra ToS de WhatsApp, riesgo de ban |

## Contexto Krayin

- Mismo estado de cobertura que el VoIP: docs de paquetes suficientes para la
  estructura; webhooks entrantes, settings y referencia de API externa
  disponibles en el código (ver [voip.md](voip.md) — contexto Krayin).
- Envío de mensajes: patrón de plantillas en `EmailTemplate`/`Marketing`;
  envío asíncrono vía colas.

## Alcance tentativo (a confirmar)

1. **Settings**: credenciales (token Cloud API / BSP) + verify token del webhook.
2. **Botón "WhatsApp"** en persona/lead (usa el número de `contact_numbers`).
3. **Tabla `whatsapp_messages`**: dirección, de/para, plantilla/texto, estado
   (sent/delivered/read/failed), wamid, vínculo a `persons`/`leads`.
4. **Webhook entrante** (Cloud API: `messages`) → registra mensaje y crea
   actividad vinculada; endpoint público con verify token.
5. **Plantillas** para mensajes salientes (aprobadas en Meta) y datagrid de
   mensajes.
6. **ACL** (`whatsapp.*`), menú, traducciones, jobs en cola.

## Decisión pendiente principal

**¿WhatsApp directo (Cloud API) o consolidado vía Live Helper Chat?** Si el
canal WhatsApp va a operarse dentro de LHC, este plugin independiente pierde
sentido — solo harían falta referencias (que el plugin LHC ya cubre). Decidir
con negocio antes de invertir en este spec.

## Pendientes para el spec

- Proveedor/eje de operación (Cloud API vs BSP vs vía LHC).
- ¿Mensajería 1:1 desde el CRM o también campañas con plantillas?
- ¿Número compartido o dedicado?
