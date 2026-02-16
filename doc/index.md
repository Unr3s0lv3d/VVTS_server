# Main

This is the documentation pertaining to the _VVTS (VPN Vulnerability Testing Suite)_ server component. If you're looking for the VVTS documentation itself, [click here](https://github.com/MidnightBlueLabs/VVTS/doc).

## Preparation

In order to get the VVTS server component up and running, the following components are expected to be available on your system:
* `php` (`sudo apt install php`)
* `php-sqlite3` (`sudo apt install php-sqlite3`)

The server component is simplistic in nature and should work with any webserver that supports php. In order to warrant sound functionality, make sure that:
* The domain name used for this component resolves to a single IP address.
* The IP address hosting this component is not shared with the VPN endpoint used for validating the VPN client through VVTS.
