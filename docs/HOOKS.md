# ReqLock developer hooks

ReqLock exposes a small, stable API so companion plugins and site code can extend its
behavior without forking. All hooks use the `reqlock_` prefix.

## Filters

### `reqlock_should_block_host`
The central block decision, applied everywhere ReqLock decides whether to block a host
(server-side HTTP, scripts, styles, resource hints, iframes, images).

```php
add_filter( 'reqlock_should_block_host', function ( $block, $host, $context, $reqlock ) {
    // $block   bool    — ReqLock's verdict from the active mode + allow/block lists
    // $host    string  — normalized host (no leading "www.")
    // $context string  — one of: http | script | style | resource-hint | iframe | image
    // $reqlock ReqLock — the plugin instance
    return $block;
}, 10, 4 );
```

### `reqlock_modes`
Register additional blocking modes (the core ships `all` and `blocklist`).

```php
add_filter( 'reqlock_modes', function ( $modes ) {
    $modes['auto'] = __( 'Auto-block slow hosts', 'your-textdomain' );
    return $modes;
} );
```

### `reqlock_blocklist_limit`
Maximum number of block-list hosts honored. Defaults to `2` in the free plugin;
return `0` (or a negative number) for unlimited.

```php
add_filter( 'reqlock_blocklist_limit', function () { return 0; } ); // unlimited
```

### `reqlock_blocklist_wildcards`
Whether `*.example.com` wildcard-subdomain entries are honored in the block-list.
Defaults to `false` (free strips them and matches exact hosts only).

```php
add_filter( 'reqlock_blocklist_wildcards', '__return_true' );
```

## Actions

### `reqlock_host_seen`
Fires the first time an external host is seen during a request (once per host/request).

```php
add_action( 'reqlock_host_seen', function ( $host ) { /* ... */ } );
```

### `reqlock_handle_save`
Fires on a verified ReqLock settings save (nonce already checked) before core options are
written — persist your own fields here.

```php
add_action( 'reqlock_handle_save', function ( $reqlock ) { /* save your options */ } );
```

### `reqlock_settings_after_cards`
Render your own settings cards on the ReqLock admin page, after the core cards.

```php
add_action( 'reqlock_settings_after_cards', function ( $reqlock ) { /* echo a card */ } );
```

### `reqlock_flushed_caches`
Fires after ReqLock flushes page/object caches following a settings change.

## Public methods

Access the instance with `ReqLock::instance()`:

| Method | Returns |
|---|---|
| `modes()` | array of `mode-key => label` |
| `mode()` | the active, validated mode key |
| `should_block_host( $host, $context = '' )` | bool — full decision incl. the filter |
| `should_block_url( $url, $context = '' )` | bool — parses host, then `should_block_host()` |
| `site_host()` | this site's host (no `www.`) |
| `get_option_value( $key )` | a stored setting value |
| `is_enabled()` | whether the master switch is ON |
