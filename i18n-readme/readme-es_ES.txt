=== ReqLock ===
Contributors: rackset
Tags: firewall, external-requests, privacy, performance, offline
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cortafuegos de salida para WordPress: bloquea las peticiones externas y lentas de terceros para lograr resiliencia, rendimiento y privacidad, con un solo interruptor.

== Description ==

**ReqLock** —también escrito **RequestLock** o **Request Lock** (en persa: رک لاک / ریکوئست لاک)—
es un cortafuegos de salida (egress) para WordPress. Controla cada llamada que tu sitio hace *hacia*
internet, tanto del lado del servidor como del navegador. Un solo interruptor principal, tres usos:

* **Resiliencia** — mantén el sitio funcionando cuando internet externo está **cortado o restringido** (cortes, apagones). Las páginas se sirven desde recursos locales y wp-admin deja de bloquearse en peticiones sin respuesta.
* **Rendimiento** — las llamadas de terceros lentas o caídas **fallan al instante** en lugar de bloquear la carga de las páginas del front-end y del administrador (back-end).
* **Privacidad** — elimina analíticas, rastreadores, fuentes externas y peticiones «phone-home».

= Qué bloquea =

**Lado del servidor (PHP / WP HTTP API)**

* Peticiones salientes `wp_remote_*` a hosts externos: comprobaciones de actualización/versión de WordPress.org, analíticas, APIs de IA (OpenAI, Gemini), fuentes remotas, etc. Fallan al instante en vez de agotar el tiempo de espera.

**Lado del navegador (HTML renderizado)**

* `<script src>` externo y `<link rel="stylesheet">` externo (p. ej. Google Fonts)
* Sugerencias de recursos: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* `<iframe>` externo (sustituido por un marcador local)
* Fragmentos de analítica en línea: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex
* Opcional: `<img>` externo (marcador transparente)

= Comportamiento clave =

* Tu propio dominio y todos sus subdominios siempre están **permitidos**.
* **Lista de permitidos** para cualquier otro host que deba seguir siendo accesible.
* **Panel de hosts detectados** que registra todos los hosts externos vistos, para construir tu lista de permitidos rápidamente.
* **Conmutadores por categoría** — activa o desactiva cada capa de bloqueo de forma independiente.
* **Inerte cuando está APAGADO** — con el interruptor principal apagado el plugin no hace nada, por lo que puedes dejarlo instalado y activarlo solo cuando lo necesites.
* **Funciona sobre cachés de página completa** — el filtro de salida se ejecuta como el búfer más externo, así que también cubre las páginas cacheadas.
* **Control de conflictos en wp-config** — detecta un `WP_HTTP_BLOCK_EXTERNAL` fijado en el código y te permite desarmarlo (comentarlo) o rearmarlo (restaurarlo) con un clic, para que ReqLock sea el único interruptor del bloqueo externo.

== Installation ==

1. Sube la carpeta `reqlock` a `/wp-content/plugins/`, o instala el ZIP desde **Plugins → Añadir nuevo → Subir plugin**.
2. Activa **ReqLock** en la pantalla de **Plugins**.
3. Ve a **Ajustes → ReqLock**.
4. Activa el **interruptor principal** cuando quieras bloquear las peticiones externas (durante un corte, o para cortar llamadas lentas/de seguimiento). Está **APAGADO** por defecto, así que activarlo no cambia nada por sí solo.

== Frequently Asked Questions ==

= ¿Activarlo romperá mi sitio? =
No. Con el interruptor principal apagado el plugin es completamente inerte. Incluso encendido, tu propio dominio y sus subdominios siempre están permitidos.

= ¿Cuándo debo encender el interruptor principal? =
Siempre que quieras aislar el sitio de los servicios externos: durante un corte/restricción de internet, para evitar que llamadas lentas de terceros ralenticen la carga, o para eliminar rastreadores por privacidad.

= ¿Funciona con plugins de caché? =
Sí. El filtro de salida se ejecuta como el búfer de salida más externo, así que también filtra las páginas cacheadas (probado con plugins de caché de página completa).

= ¿Afecta a wp-admin? =
El bloqueo de peticiones del lado del servidor se aplica en todas partes (lo que hace el administrador más rápido cuando no hay red). El saneamiento de HTML del lado del navegador se ejecuta por defecto solo en el front-end; opcionalmente puedes activarlo también para wp-admin.

= ¿Cómo mantengo un servicio externo funcionando mientras bloqueo el resto? =
Añade su host a la lista de permitidos. El panel de hosts detectados muestra todo lo que ReqLock ve, para que puedas copiarlos desde allí.

= ¿Y los scripts PHP personalizados que no pasan por WordPress? =
Los scripts independientes que usan `curl`/`file_get_contents` en bruto fuera de WordPress no son interceptables por un plugin y deben controlar sus propias llamadas externas.
