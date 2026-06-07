# ReqLock

[English](README.md) · [日本語](README.ja.md) · [简体中文](README.zh_CN.md) · **Español** · [Deutsch](README.de_DE.md) · [Français](README.fr_FR.md) · [فارسی](README.fa_IR.md)

*También escrito **RequestLock** o **Request Lock**. Persa / فارسی: **رک لاک (ریکوئست لاک)**.*

> Un plugin de WordPress que actúa como interruptor de corte maestro y **desarma las llamadas externas (a internet)** del núcleo de WordPress, tu tema y tus plugins, para que tu sitio siga funcionando cuando internet externo se corta o se restringe.

Diseñado para ofrecer resiliencia durante las restricciones de conectividad: acciona un único interruptor y el sitio deja de salir a internet, sirviéndose solo desde recursos locales. Como ventaja añadida, **wp-admin deja de bloquearse** en peticiones externas sin respuesta, porque las llamadas bloqueadas fallan al instante en lugar de esperar a que se agote el tiempo de espera.

> **No solo para cortes** — también funciona como una solución general para las **peticiones de red lentas**: bloquea las llamadas a terceros lentas o poco fiables que lastran el rendimiento del **front-end y del administrador (back-end)** de WordPress, incluso cuando hay internet. Las llamadas bloqueadas fallan al instante, así que no pueden atascar la carga de las páginas esperando a que expire el tiempo de espera.

## Qué bloquea

**Lado del servidor (PHP / WP HTTP API)**
- Peticiones salientes `wp_remote_*` a hosts externos: comprobaciones de actualización y de versión de WordPress.org, analíticas, APIs de OpenAI / Gemini, fuentes remotas, etc.
- Las llamadas externas fallan *al instante* (en lugar de agotar el tiempo de espera) → un administrador más rápido cuando se está sin conexión.

**Lado del navegador (HTML renderizado)**
- `<script src="…">` externo
- `<link rel="stylesheet">` externo (p. ej. Google Fonts)
- Sugerencias de recursos: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
- `<iframe>` externo → sustituido por un marcador local limpio
- Fragmentos de analítica en línea: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex Metrica
- *(opcional)* `<img>` externo → marcador transparente

## Comportamiento clave

- **Tu propio dominio y todos sus subdominios siempre están permitidos** (p. ej. `my.yoursite.com`).
- **Lista de permitidos**: añade cualquier otro host que deba seguir siendo accesible.
- **Panel de hosts detectados**: cada host externo que el plugin ve queda registrado, para que puedas construir la lista de permitidos rápidamente.
- **Conmutadores por categoría**: activa o desactiva cada capa de bloqueo de forma independiente.
- **Inerte cuando está apagado**: con el interruptor maestro apagado, el plugin no hace nada; es seguro dejarlo instalado de forma permanente y activarlo solo cuando haga falta.
- **El indicador de la barra de administración** muestra cuándo ReqLock está activo.

## Instalación

1. Copia la carpeta `reqlock` en `wp-content/plugins/`.
2. Activa **ReqLock** desde la pantalla de Plugins.
3. Ve a **Ajustes → ReqLock**.
4. Cuando internet externo no esté disponible, activa el **interruptor maestro**.

## Resumen de ajustes

| Grupo | Opción | Por defecto |
|---|---|---|
| Maestro | ReqLock | **No** |
| Lado del servidor | Bloquear WP HTTP API saliente | Sí |
| Lado del navegador | Scripts externos | Sí |
| Lado del navegador | Hojas de estilo externas | Sí |
| Lado del navegador | Sugerencias de recursos (preconnect/dns-prefetch/…) | Sí |
| Lado del navegador | Iframes externos | Sí |
| Lado del navegador | Fragmentos de analítica en línea | Sí |
| Lado del navegador | Imágenes externas | No |
| Alcance | Sanear también wp-admin | No |
| Alcance | Registrar hosts detectados | Sí |
| — | Lista de permitidos de hosts | vacío |

## Requisitos

- WordPress 5.0+
- PHP 7.2+ (probado en 7.4 / 8.1 / 8.2)

## Cómo funciona

- **HTTP API**: engancha `pre_http_request` y devuelve un `WP_Error` para cualquier host externo (respetando la lista de permitidos), cortocircuitando la petición antes de que salga del servidor.
- **Recursos encolados**: desencola/desregistra cualquier script/estilo registrado cuyo origen sea externo.
- **HTML renderizado**: almacena en búfer la salida de la página y elimina las etiquetas de recursos externos y los fragmentos conocidos de analítica en línea con patrones conservadores y bien anclados. Cada elemento eliminado deja un comentario rastreable `<!-- ReqLock blocked … -->` (no se realiza ninguna petición).

Deja deliberadamente intactos los `<link>` externos `rel="canonical"` / `alternate` / de icono (seguro para el SEO) y nunca toca las URL relativas/internas.

> Nota: los puntos de entrada PHP independientes que omiten WordPress por completo (p. ej. scripts personalizados que usan `curl` directamente) **no** pueden ser interceptados por un plugin de WordPress y deben proteger sus propias llamadas externas.

## Licencia

GPL-2.0-or-later. Consulta [LICENSE](LICENSE).

## Créditos

Desarrollado y mantenido por el **Rackset DevOps Team**.

Sitio web: **[https://rackset.com](https://rackset.com)**
