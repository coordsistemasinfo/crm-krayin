# Specs y análisis de integraciones

Este directorio guarda los análisis y especificaciones de plugins/integraciones
del CRM antes de implementarse como paquetes bajo `packages/Webkul/`.

Convención de trabajo:

1. Se escribe aquí el análisis + spec de la integración (`<tema>.md`).
2. Cuando el spec queda definido, se implementa como paquete en `packages/Webkul/`
   siguiendo el skill `crm-package-development` (estructura, SqlCompat, CHANGELOG).
3. Cambios de esquema solo por migraciones y SQL portable (PostgreSQL schema
   `crmkrayin` + MySQL).

## Estado actual

| Spec | Estado | Notas |
|---|---|---|
| [livehelperchat.md](livehelperchat.md) | Análisis listo — 3 opciones, recomendada la Opción B (plugin de referencias) | Requiere LHC instalado (URL + API key) |
| [voip.md](voip.md) | Borrador — pendiente definir proveedor y alcance | Asterisk / Twilio / otra PBX |
| [whatsapp.md](whatsapp.md) | Borrador — pendiente definir proveedor y alcance | Posible consolidación vía LHC |

Los specs detallados reemplazarán estas notas cuando se apruebe la implementación.
