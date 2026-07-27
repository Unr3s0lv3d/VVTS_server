#!/bin/bash
# Wrapper script for iptables proxy firewall rules.
# Only allows opening/closing a single TCP port in the unprivileged range.
# Intended to be called via sudo by www-data.

set -e

if [ $# -ne 2 ]; then
    echo "Usage: $0 {open|close} <port>" >&2
    exit 1
fi

ACTION="$1"
PORT="$2"

if ! [[ "$PORT" =~ ^[0-9]+$ ]] || [ "$PORT" -lt 1024 ] || [ "$PORT" -gt 65535 ]; then
    echo "Error: port must be between 1024 and 65535" >&2
    exit 1
fi

case "$ACTION" in
    open)
        /sbin/iptables -I INPUT -p tcp --dport "$PORT" -j ACCEPT
        ;;
    close)
        /sbin/iptables -D INPUT -p tcp --dport "$PORT" -j ACCEPT
        ;;
    *)
        echo "Error: action must be 'open' or 'close'" >&2
        exit 1
        ;;
esac
