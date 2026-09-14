# Specs y análisis de integraciones

Este directorio guarda los análisis y especificaciones de plugins/integraciones
del CRM antes de implementarse como paquetes bajo `packages/Webkul/`.

Convención de trabajo:

1. Se escribe aquí el análisis + spec de la integración (`<tema>.md`).
2. Cuando el spec queda definido, se implementa como paquete en `packages/Webkul/`
   siguiendo el skill `crm-package-development` (estructura, SqlCompat, CHANGELOG).
3. Cambios de esquema solo por migraciones y SQL portable (PostgreSQL schema
   `crmkrayin` + MySQL).

## Versionado de los specs

Cada spec lleva encabezado `Versión:` y `Estado:` y una tabla de historial al
final del documento. La numeración:

| Versión | Significado |
|---|---|
| 0.1 | Borrador de análisis (ideas abiertas) |
| 0.2 | Análisis cerrado, decisiones de proveedor/alcance tomadas |
| 1.0 | Spec aprobado — se implementa como paquete |
| 1.x | Spec vivo: cambios tras la implementación (matching del código) |

Cada cambio de versión se confirma en un commit propio del spec.

## Estado actual

| Spec | Versión | Estado | Notas |
|---|---|---|---|
| [livehelperchat.md](livehelperchat.md) | 0.2 | Análisis listo — 3 opciones, recomendada la Opción B (plugin de referencias) | Requiere LHC instalado (URL + API key) |
| [voip.md](voip.md) | 0.1 | Borrador — pendiente definir proveedor y alcance | Asterisk / Twilio / otra PBX |
| [whatsapp.md](whatsapp.md) | 0.1 | Borrador — pendiente definir proveedor y alcance | Posible consolidación vía LHC |

Los specs detallados (v1.0) reemplazarán estas notas cuando se apruebe la
implementación.
