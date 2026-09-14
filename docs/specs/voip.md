# Plugin VoIP — análisis (borrador)

> Estado: borrador. Pendiente definir proveedor y alcance antes del spec.

- Integraciones candidatas: **Asterisk local** (AMI/ARI), **Twilio Voice**,
  Zadarma u otra PBX con API. Sin definir.

## Contexto Krayin

- La documentación de paquetes (devdocs.krayincrm.com/2.2/packages) cubre la
  estructura: scaffolding, rutas, migraciones, modelos Concord, repositorios,
  DataGrids, ACL, menú, assets, traducciones.
- El repo ya tiene patrones de referencia reutilizables:
  - Webhooks entrantes sin auth: `admin.mail.inbound_parse`
    (`packages/Webkul/Admin/src/Routes/Admin/mail-routes.php:25`,
    `withoutMiddleware`).
  - Integración con API externa: paquete `GoogleContact`.
  - Tabla de actividades tipadas: `Activity` (types: call, meeting, lunch) —
    un registro de llamada puede reutilizarla o extenderla.
  - Settings con credenciales: módulo de settings existente en Admin.

## Alcance tentativo (a confirmar)

1. **Settings**: credenciales del proveedor VoIP (API/AMI) + webhook secret.
2. **Click-to-call**: botón en persona/lead (via `tel:` o softphone del
   proveedor).
3. **Tabla `calls`**: dirección, de/para, duración, estado, URL de grabación,
   vínculo a `persons`/`leads`.
4. **Webhook de CDR** (llamada terminada) → registra la llamada y crea
   actividad vinculada.
5. **DataGrid de llamadas** en el lead/persona y vista en Settings.
6. **ACL** (`voip.*`), menú, traducciones (solo Admin, sin views → strings en
   `admin::app.*`; si el paquete tiene vistas → `Resources/lang` propio).

## Constraints del proyecto (AGENTS.md)

- SQL portable vía `Webkul\Core\Database\SqlCompat` (PostgreSQL schema
  `crmkrayin`); migraciones con esquema builder; nunca `SET FOREIGN_KEY_CHECKS`
  ni UPDATE...JOIN en migraciones.
- `CHANGELOG.md` obligatorio por cada feature.

## Pendientes para el spec

- Proveedor VoIP (¿Asterisk?, ¿Twilio?, ¿Zadarma?).
- ¿Click-to-call desde navegador, solo logging, o ambos?
- ¿Graba llamadas el proveedor? ¿dónde se guardan las grabaciones?
