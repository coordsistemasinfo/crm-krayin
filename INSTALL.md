# Instalación de Krayin CRM (rama `unicomfa` — PostgreSQL)

Estas instrucciones son para instalar **esta adaptación** (Krayin CRM 2.2 con
soporte PostgreSQL) en un entorno de **pruebas** o de **producción**.

> Diferencia clave vs la guía oficial: el esquema vive en **PostgreSQL** dentro
> de un schema dedicado (`crmkrayin`, configurable con `DB_SCHEMA`), y el
> servidor de desarrollo corre en el **puerto 8100**.

---

## 1. Requisitos

| Requisito | Versión |
|---|---|
| PHP | **8.3+** con `pdo_pgsql`, `pgsql`, `mbstring`, `gd`, `intl`, `curl`, `zip`, `xml` |
| PostgreSQL | 12+ (probado con 16) |
| Composer | 2.x |
| Node + NPM | 18+ (solo para assets con Vite) |

Extensiones PHP obligatorias (verificables con `php -m`): `pdo_pgsql`, `pgsql`,
`mbstring`, `gd`, `intl`, `openssl`, `dom`, `curl`, `fileinfo`, `filter`.

---

## 2. Preparar PostgreSQL

Ejecutar como superusuario o con un usuario con permisos de creación:

```sql
-- Base de datos y usuario (ejemplo)
CREATE DATABASE siu_test OWNER siu_devel;

-- El schema del CRM (el instalador puede crearlo si el usuario tiene CREATE,
-- pero conviene crearlo a mano)
CREATE SCHEMA crmkrayin AUTHORIZATION siu_devel;

GRANT ALL ON SCHEMA crmkrayin TO siu_devel;
```

> **Nota**: todos los objetos del CRM se crean dentro del schema `crmkrayin`
> gracias a `search_path`. La base de datos puede tener otras cosas sin conflicto.

---

## 3. Obtener el código

> **Importante**: toda esta adaptación vive en la rama **`unicomfa`**. Asegúrate
> de estar en ella antes de instalar o actualizar (`git branch --show-current`
> debe mostrar `unicomfa`).

```bash
git clone -b unicomfa <URL-DE-ESTE-REPO> crm-krayin
cd crm-krayin

# Si el repositorio ya estaba clonado en otra rama:
git checkout unicomfa
git pull origin unicomfa

# 1) Primero el .env (los hooks post-autoload de composer arrancan la app
#    con `artisan package:discover`; con el .env presente el orden nunca falla)
cp .env.example .env

# 2) Luego las dependencias
composer install --no-interaction
```

> **El orden importa**: crear el `.env` **antes** de `composer install`. Si
> `composer install` corre sin `.env` en un entorno estricto puede fallar en el
> paso `package:discover` del post-autoload-dump; si eso ocurre, crea el `.env`
> (paso siguiente) y vuelve a correr `composer install`.

`composer install` es **obligatorio** (instala Laravel y todos los paquetes
Webkul). Los paquetes `Webkul/*` no vienen por composer externo — viven en
`packages/` y se cargan vía autoloader del repo.

`npm install` **no es necesario** para instalar ni para producción: solo si
planeas modificar CSS/JS del admin, en cuyo caso además compilarías con
`npm run build`. El instalador publica los assets base con `vendor:publish`.

---

## 4. Configurar el entorno

Editar el `.env` creado en el paso 3 — valores clave para PostgreSQL:

```ini
APP_ENV=local              # pruebas; en producción: production
APP_DEBUG=true             # en producción: false
APP_URL=http://localhost:8100   # o el dominio final
APP_LOCALE=es
APP_TIMEZONE=America/Bogota

DB_CONNECTION=pgsql
DB_HOST=10.10.70.223       # o 127.0.0.1 / el host del server de BD
DB_PORT=5432
DB_DATABASE=siu_test
DB_USERNAME=siu_devel
DB_PASSWORD=<contraseña>
DB_PREFIX=
DB_SCHEMA=crmkrayin        # search_path de PostgreSQL
```

Luego:

```bash
php artisan key:generate
```

---

## 5. Instalación (elige un método)

### Método A — Wizard gráfico (recomendado para pruebas)

1. Arranca el servidor:
   ```bash
   composer run dev
   ```
   (sirve en `http://localhost:8100`; bajo **WSL2** escucha en `0.0.0.0`, así que
   también es accesible desde el navegador de Windows como
   `http://<IP-del-WSL>:8100` — obtener la IP con `hostname -I`).

2. Abre `http://localhost:8100/install` y sigue los pasos:
   - En **Database Connection** elige **PostgreSQL**.
   - Puerto: `5432`.
   - Completa host, base, usuario, contraseña.
   - **Database Schema**: escribe `crmkrayin`.
   - Continúa con migración → seeding → admin.

### Método B — Línea de comandos (recomendado para producción)

```bash
php artisan krayin-crm:install
```

Responde los prompts. Al elegir conexión `pgsql` el instalador ahora pregunta
el **schema** (default `public`; usa `crmkrayin`) y propone el puerto `5432`.

---

## 6. Verificación post-instalación

```bash
ls -la public/storage    # symlink -> storage/app/public (si no existe: php artisan storage:link)
php artisan migrate:status | tail -5   # 60+ tablas, sin pendientes
```

```sql
-- En PostgreSQL: 0 columnas tipo json (todas deben ser jsonb)
SELECT count(*) FROM information_schema.columns
WHERE table_schema = 'crmkrayin' AND data_type = 'json';
```

- Login admin en `http://localhost:8100/admin` con las credenciales creadas.
- Revisar que cargan: Dashboard, Leads, Contactos (persons), Actividades,
  Productos, Mail/inbox — estos ejercitan las consultas portables.

**Credenciales por defecto del instalador**: `admin@example.com` /
`admin123` (cámbialas al entrar, en *Settings → Users*).

---

## 7. Notas para producción

```bash
# .env de producción
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Optimizar
php artisan optimize

# Enlace público de storage (public/storage -> storage/app/public).
# El instalador lo ejecuta automáticamente, pero tras un deploy que
# borre public/storage hay que recrearlo:
php artisan storage:link

# Permisos
chmod -R ug+w storage bootstrap/cache
```

- Las imágenes subidas por el panel (logo, favicon, etc.) se guardan
  automáticamente en `storage/app/public/configuration/` y se sirven vía
  `/storage/configuration/...` — nunca copiarlas a mano a otra ruta.
- El schema **debe existir** (o el usuario con permiso `CREATE`) **antes** de
  correr el instalador, porque las migraciones crean todas las tablas dentro.
- Los jobs (email, data transfer) usan `QUEUE_CONNECTION=sync` por defecto;
  para producción considera `redis`/`database` + `php artisan queue:worker`.
- Respaldo de la BD: solo el schema `crmkrayin`
  (`pg_dump -n crmkrayin siu_test`).

---

## 8. Desarrollo local (WSL2)

```bash
composer run dev      # php artisan serve --host=0.0.0.0 --port=8100
```

- El puerto **8100** evita chocar con otras apps en el 8000.
- En WSL2 con modo NAT, si `localhost` no funciona desde Windows, usar la IP
  del WSL (`hostname -I`).

---

## 9. Verificación de calidad (antes de cualquier deploy)

```bash
php artisan test --compact   # suite Pest (42 tests) contra la conexión real
./vendor/bin/pint            # formato PSR-12
bash bin/validate-skills.sh  # validar skills de agents
```

---

## Solución de problemas comunes

| Síntoma | Causa probable | Solución |
|---|---|---|
| `falta una entrada para la tabla X en la cláusula FROM` | raw SQL MySQL en código nuevo | usar `Webkul\Core\Database\SqlCompat` (ver AGENTS.md) |
| `la columna «X.name» debe aparecer en la cláusula GROUP BY` | columna de tabla unida sin agrupar | agregar al GROUP BY o seleccionar con `MAX()` |
| `no se pudo identificar un operador de igualdad para el tipo json` | migración jsonb no aplicada | `php artisan migrate` |
| `llave duplicada viola restricción de unicidad (id)=(N)` | secuencia desfasada por inserts con id explícito | `SqlCompat::syncSequences()` |
| Wizard rechaza la conexión | validación JS antigua | esta rama ya acepta `pgsql`; verificar estar en `unicomfa` |
| Navegador de Windows no alcanza el servidor | WSL2, bind en 127.0.0.1 | usar `composer run dev` (incluye `--host=0.0.0.0`) o IP del WSL |
