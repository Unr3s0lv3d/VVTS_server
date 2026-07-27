# Main

This is the documentation pertaining to the _VVTS (VPN Vulnerability Testing Suite)_ server component. If you're looking for the VVTS documentation itself, [click here](https://github.com/MidnightBlueLabs/VVTS/doc).

## Preparation

In order to get the VVTS server component up and running, the following components are expected to be available on your system:
* `php` (`sudo apt install php`)
* `php-sqlite3` (`sudo apt install php-sqlite3`)
* `python3` (`sudo apt install python3`) — required for the remote proxy feature

The server component is simplistic in nature and should work with any webserver that supports php. In order to warrant sound functionality, make sure that:
* The domain name used for this component resolves to a single IP address.
* The IP address hosting this component is not shared with the VPN endpoint used for validating the VPN client through VVTS.

## Authentication

API endpoints that modify server state require a shared secret, sent via the `X-API-Secret` HTTP header. The secret is configured in `api_config.php`:
```php
define("API_SECRET", "your-random-secret-here");
```

## API endpoints

### Token management (no authentication required)
* `/?create_token` — creates a new validation token and returns it as JSON.
* `/?query=<token>` — queries the flag status of a token.
* `/?flag=<token>` — flags a token with the requester's IP address.

### Captive portal (authentication required)
* `/?configure_portal&portal_url=<url>` — sets the `user-portal-url` returned in the RFC 8908 captive portal JSON response.
* `/?unconfigure_portal` — removes the custom portal URL.

### WPAD proxy — remote mode (authentication required)
* `/?start_proxy` — starts a forward proxy on a random port and generates a PAC file. Requires `python3` and the firewall wrapper script.
* `/?stop_proxy` — stops the running proxy and removes the PAC file.

### WPAD proxy — local mode (authentication required)
* `/?configure_wpad&proxy_host=<host>&proxy_port=<port>&direct=<hosts>` — generates a PAC file pointing to the specified proxy address. The `direct` parameter is optional and accepts semicolon-separated hostnames.
* `/?unconfigure_wpad` — removes the PAC file.

### PAC file (no authentication required)
* `/?wpad` — serves the currently configured PAC file (`application/x-ns-proxy-autoconfig`). Returns 404 if no PAC file is configured.

### Captive portal detection (no authentication required)
* Requests with `Accept: application/captive+json` receive an RFC 8908 JSON response.
