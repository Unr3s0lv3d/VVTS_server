<?php

require_once dirname(__FILE__) . "/autoload.php";

use \VVTS\Classes\SqliteTokenStorage;

$szBase = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST'] . '/';

/* RFC 8908 Captive Portal API — Android 12+, iOS 14+, Windows 11 */
$szAccept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (strpos($szAccept, 'application/captive+json') !== false) {
    header('Content-Type: application/captive+json');
    echo json_encode(['captive' => true, 'user-portal-url' => $szBase]);
    exit;
}

/* Captive portal HTML pagina — getoond als er geen API parameters aanwezig zijn */
if (!isset($_GET['create_token']) && !isset($_GET['query']) && !isset($_GET['flag'])) {
    date_default_timezone_set('Europe/Amsterdam');

    $headers = [
        'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR',
    ];
    $ipv4 = null;
    $ipv6 = null;
    foreach ($headers as $header) {
        if (empty($_SERVER[$header])) continue;
        foreach (explode(',', $_SERVER[$header]) as $candidate) {
            $ip = trim($candidate);
            if (!filter_var($ip, FILTER_VALIDATE_IP)) continue;
            if (!$ipv4 && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) $ipv4 = $ip;
            if (!$ipv6 && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) $ipv6 = $ip;
            if ($ipv4 && $ipv6) break 2;
        }
    }
    $time = date('H:i:s');
    $date = date('d-m-Y');

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn IP-adres</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f3;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 1.5rem;
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            border: 0.5px solid rgba(0,0,0,0.1);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .icon {
            width: 56px;
            height: 56px;
            background: #eeedfe;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .ip-block {
            background: #f5f5f3;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 10px;
            text-align: left;
        }
        .ip-block .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            background: #eeedfe;
            color: #3c3489;
            padding: 2px 8px;
            border-radius: 20px;
            margin-bottom: 6px;
        }
        .ip-block .badge.v6 { background: #e1f5ee; color: #085041; }
        .ip-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: #534ab7;
            word-break: break-all;
        }
        .ip-value.v6 { color: #0f6e56; }
        .ip-none { font-size: 0.95rem; color: #b4b2a9; }
        .divider { height: 0.5px; background: rgba(0,0,0,0.08); margin: 1.25rem 0; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .meta-item { background: #f5f5f3; border-radius: 12px; padding: 0.75rem; }
        .meta-label { font-size: 11px; color: #888780; margin-bottom: 3px; }
        .meta-value { font-size: 14px; font-weight: 500; color: #2c2c2a; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="9" stroke="#534ab7" stroke-width="1.5"/>
                <path d="M12 3C12 3 8 7 8 12C8 17 12 21 12 21" stroke="#534ab7" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M12 3C12 3 16 7 16 12C16 17 12 21 12 21" stroke="#534ab7" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M3 12H21" stroke="#534ab7" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="ip-block">
            <span class="badge">IPv4</span><br>
            <?php if ($ipv4): ?>
                <span class="ip-value"><?= htmlspecialchars($ipv4) ?></span>
            <?php else: ?>
                <span class="ip-none">Niet beschikbaar</span>
            <?php endif; ?>
        </div>

        <div class="ip-block">
            <span class="badge v6">IPv6</span><br>
            <?php if ($ipv6): ?>
                <span class="ip-value v6"><?= htmlspecialchars($ipv6) ?></span>
            <?php else: ?>
                <span class="ip-none">Niet beschikbaar</span>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <div class="meta">
            <div class="meta-item">
                <p class="meta-label">Datum</p>
                <p class="meta-value"><?= $date ?></p>
            </div>
            <div class="meta-item">
                <p class="meta-label">Tijd</p>
                <p class="meta-value"><?= $time ?></p>
            </div>
        </div>

        <div class="divider"></div>

        <button onclick="alert('JavaScript werkt!');" style="margin-top:.5rem;width:100%;padding:.75rem;background:#eeedfe;color:#3c3489;border:none;border-radius:12px;font-size:.95rem;font-weight:500;cursor:pointer;">
            Test JavaScript
        </button>
    </div>
</body>
</html>
<?php
    exit;
}

header("Content-Type: application/json");

$oStorage = new SqliteTokenStorage();

if (isset($_GET['create_token'])) {
    $szToken = base64_encode(random_bytes(24));
    $oStorage->StoreToken($szToken, 300000);
    $oObj = new stdClass;
    $oObj->status = "OK";
    $oObj->error = "";
    $oObj->token = $szToken;
    echo json_encode($oObj);
} else if (isset($_GET['query'])) {
    $szToken = $_GET['query'];
    $oFlag = $oStorage->RetrieveFlag($szToken);
    $oObj = new stdClass;
    if ($oFlag === false) {
        $oObj->status = "Error";
        $oObj->error = "Token does not exist";
    } else {
        $oObj->status = "OK";
        $oObj->error = "";
        switch ($oFlag->status) {
            case STATUS_NOT_FLAGGED: $oObj->flagged = "NOT_FLAGGED"; break;
            case STATUS_FLAGGED:
                $oObj->flagged = "FLAGGED";
                $oObj->ip = $oFlag->ip;
                break;
        }
    }
    echo json_encode($oObj);
} else if (isset($_GET['flag'])) {
    $szToken = $_GET['flag'];
    $oStorage->FlagToken($szToken, $_SERVER['REMOTE_ADDR']);
    $oObj = new stdClass;
    $oObj->status = "OK";
    $oObj->error = "";
    echo json_encode($oObj);
}

?>