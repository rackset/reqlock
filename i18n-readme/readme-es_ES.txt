=== ReqLock ===
Contributors: rackset
Tags: firewall, external-requests, privacy, performance, offline
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bloquea las peticiones externas y lentas de terceros en WordPress: para resiliencia, rendimiento, privacidad y desarrollo sin conexión. Un solo interruptor.

== Description ==

**ReqLock** —también escrito **RequestLock** o **Request Lock**— es un cortafuegos de salida
(egress) para WordPress. Controla cada llamada que tu sitio hace *hacia* internet, en ambos lados de
la petición: el **servidor** (PHP / WP HTTP API) y el **navegador** (el HTML que renderizan tus
páginas). Un solo interruptor principal pone a tu sitio en pleno control de su propio tráfico saliente.

Los sitios WordPress modernos son ruidosos: comprobaciones de actualización, pings de licencia,
analíticas, gestores de etiquetas, fuentes externas, widgets incrustados, APIs de IA y diversas
llamadas «phone-home» contactan con servidores que no controlas. Cuando esos servidores van lentos,
están bloqueados o caídos, tus páginas y tu escritorio lo pagan, y cada uno de ellos es un punto por
el que se fugan los datos de tus visitantes. ReqLock te permite cortar ese tráfico a voluntad, al
instante y de forma reversible, sin editar archivos del tema ni rastrear plugins.

**Un interruptor, cuatro funciones:**

* **Resiliencia** — mantén el sitio funcionando cuando internet externo está **cortado o restringido**
  (cortes, apagones regionales, fallos en origen). Las páginas se sirven desde recursos locales y
  wp-admin deja de bloquearse en peticiones sin respuesta.
* **Rendimiento** — las llamadas de terceros lentas o caídas **fallan al instante** en lugar de
  bloquear la carga de las páginas del front-end y del back-end con largos tiempos de espera.
* **Privacidad** — elimina analíticas, rastreadores, fuentes externas y peticiones «phone-home» para
  que nada sobre tus visitantes salga del servidor.
* **Desarrollo** — convierte cualquier instalación en un entorno autónomo y **capaz de funcionar sin
  conexión**: sin llamadas externas, sin seguimiento desde una copia de pruebas, sin esperar a APIs
  remotas mientras trabajas.

= Casos de uso =

* **Resiliencia ante cortes / apagones.** Cuando la conectividad de origen está limitada o bloqueada,
  un sitio WordPress normal se atasca en cada llamada externa. Activa ReqLock y el sitio sigue
  sirviéndose desde recursos locales, incluido el administrador.
* **Acelerar un sitio lento.** Un solo host lento de analítica o de fuentes puede añadir segundos a
  cada carga. ReqLock hace que esas llamadas fallen rápido en lugar de bloquear el renderizado.
* **Despliegues centrados en la privacidad / sin seguimiento.** Ejecuta un sitio del que se pueda
  demostrar que no hace ninguna petición de terceros: útil para proyectos centrados en la privacidad,
  herramientas internas y configuraciones sensibles al cumplimiento.
* **Desarrollo local y de pruebas (staging).** Clona producción en un portátil o servidor de pruebas
  y ReqLock evita que «llame a casa»: ninguna analítica disparada desde una copia de prueba, ningún
  ping de licencia, ninguna comprobación de actualización de WordPress.org que ralentice `wp-admin`
  mientras construyes. El sitio se comporta igual con la red desconectada: ideal para programar sin
  conexión, demos y máquinas aisladas.
* **Auditar con qué habla un sitio.** El panel de hosts detectados registra cada host externo que el
  sitio contacta, para que veas exactamente con quién hablan tus temas y plugins, y luego decidas qué
  permitir y qué cortar.

= Qué bloquea =

**Lado del servidor (PHP / WP HTTP API)**

* Peticiones salientes `wp_remote_*` a hosts externos: comprobaciones de actualización/versión de
  WordPress.org, analíticas, APIs de IA (OpenAI, Gemini), fuentes remotas, pings de licencia/phone-home,
  etc. Fallan al instante en vez de agotar el tiempo de espera.

**Lado del navegador (HTML renderizado)**

* `<script src>` externo y `<link rel="stylesheet">` externo (p. ej. Google Fonts)
* Sugerencias de recursos: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* `<iframe>` externo (sustituido por un marcador local)
* Fragmentos de analítica en línea: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs,
  Meta Pixel, Hotjar, Yandex, TikTok, Pinterest, LinkedIn, X, Snap, Segment, Plausible
* Opcional: `<img>` externo (marcador transparente)

= Comportamiento clave =

* Tu propio dominio y todos sus subdominios siempre están **permitidos**.
* **Lista de permitidos** para cualquier otro host que deba seguir siendo accesible.
* **Panel de hosts detectados** que registra todos los hosts externos vistos, para construir tu lista
  de permitidos rápidamente.
* **Conmutadores por categoría** — activa o desactiva cada capa de bloqueo de forma independiente.
* **Control de conflictos en wp-config** — detecta una constante `WP_HTTP_BLOCK_EXTERNAL` fijada en el
  código y te permite desarmarla (comentarla) o rearmarla (restaurarla) con un clic, para que ReqLock
  sea el único interruptor del bloqueo externo. Los cambios son reversibles, con comprobación de
  integridad y atómicos.
* **Inerte cuando está APAGADO** — con el interruptor principal apagado el plugin no hace nada, por lo
  que puedes dejarlo instalado y activarlo solo cuando lo necesites.
* **Funciona sobre cachés de página completa** — el filtro de salida se ejecuta como el búfer más
  externo, así que también cubre las páginas cacheadas.

== Installation ==

1. Sube la carpeta `reqlock` a `/wp-content/plugins/`, o instala el ZIP desde **Plugins → Añadir nuevo → Subir plugin**.
2. Activa **ReqLock** en la pantalla de **Plugins**.
3. Ve a **Ajustes → ReqLock**.
4. Activa el **interruptor principal** cuando quieras bloquear las peticiones externas (durante un corte, para cortar llamadas lentas/de seguimiento, o para poner una copia de pruebas/local sin conexión). Está **APAGADO** por defecto, así que activarlo no cambia nada por sí solo.

== Frequently Asked Questions ==

= ¿Activarlo romperá mi sitio? =
No. Con el interruptor principal apagado el plugin es completamente inerte. Incluso encendido, tu propio dominio y sus subdominios siempre están permitidos.

= ¿Cuándo debo encender el interruptor principal? =
Siempre que quieras aislar el sitio de los servicios externos: durante un corte/restricción de internet, para evitar que llamadas lentas de terceros ralenticen la carga, para eliminar rastreadores por privacidad, o para poner una copia de pruebas/local totalmente sin conexión durante el desarrollo.

= ¿Puedo usarlo para desarrollar sin conexión? =
Sí, es un caso de uso central. Enciende el interruptor principal y la instalación deja de contactar con WordPress.org, analíticas, servidores de licencia, fuentes y otros hosts remotos. Tu sitio local o de pruebas carga y se comporta igual con la red desconectada, y nunca dispara llamadas de seguimiento ni phone-home desde una copia que no es de producción.

= ¿Funciona con plugins de caché? =
Sí. El filtro de salida se ejecuta como el búfer de salida más externo, así que también filtra las páginas cacheadas (probado con plugins de caché de página completa).

= ¿Afecta a wp-admin? =
El bloqueo de peticiones del lado del servidor se aplica en todas partes (lo que hace el administrador más rápido cuando no hay red). El saneamiento de HTML del lado del navegador se ejecuta por defecto solo en el front-end; opcionalmente puedes activarlo también para wp-admin.

= ¿Cómo mantengo un servicio externo funcionando mientras bloqueo el resto? =
Añade su host a la lista de permitidos. El panel de hosts detectados muestra todo lo que ReqLock ve, para que puedas copiarlos desde allí.

= ¿Y los scripts PHP personalizados que no pasan por WordPress? =
Los scripts independientes que usan `curl`/`file_get_contents` en bruto fuera de WordPress no son interceptables por un plugin y deben controlar sus propias llamadas externas.
