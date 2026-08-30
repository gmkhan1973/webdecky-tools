<?php
/*
 * @author G.M Khan 
 * @name WebDecky - PHP & Laravel Website Script
 * @copyright © 2025 webdecky.com
*/
declare(strict_types=1);
error_reporting(E_ERROR | E_PARSE);
session_start();
/* =====================================================
   ROBOTS.TXT GENERATOR
===================================================== */
function generateRobotsTxt(array $data): string {
    $lines = [];
    if (!empty($data['user_agents'])) {
        foreach ($data['user_agents'] as $ua) {
            $ua = trim($ua);
            if ($ua !== '') {
                $lines[] = "User-agent: $ua";
                if (!empty($data['disallow'])) {
                    foreach ($data['disallow'] as $d) {
                        $d = trim($d);
                        if ($d !== '') $lines[] = "Disallow: $d";
                    }
                }
                if (!empty($data['allow'])) {
                    foreach ($data['allow'] as $a) {
                        $a = trim($a);
                        if ($a !== '') $lines[] = "Allow: $a";
                    }
                }
                $lines[] = ""; // blank line between agents
            }
        }
    }
    if (!empty($data['sitemaps'])) {
        foreach ($data['sitemaps'] as $s) {
            $s = trim($s);
            if ($s !== '') $lines[] = "Sitemap: $s";
        }
    }
    return implode("\n", $lines);
}
/* =====================================================
   POST → REDIRECT → GET
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userAgents = explode("\n", $_POST['user_agents'] ?? '');
    $disallow   = explode("\n", $_POST['disallow'] ?? '');
    $allow      = explode("\n", $_POST['allow'] ?? '');
    $sitemaps   = explode("\n", $_POST['sitemaps'] ?? '');
    $data = [
        'user_agents' => $userAgents,
        'disallow' => $disallow,
        'allow' => $allow,
        'sitemaps' => $sitemaps
    ];
    $_SESSION['robots_txt'] = generateRobotsTxt($data);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
$robotsTxt = $_SESSION['robots_txt'] ?? '';
unset($_SESSION['robots_txt']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Robots.txt Generator Tool</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
.da-card{max-width:1200px;margin:40px auto;padding:35px;border-radius:14px;box-shadow:0 10px 25px rgba(0,0,0,.08);}
.da-title{text-align:center;font-size:26px;font-weight:700;margin-bottom:6px;}
.da-subtitle{text-align:center;opacity:.75;margin-bottom:25px;}
.da-input, .da-textarea{width:100%;padding:14px;font-size:16px;border-radius:10px;border:1px solid #ccc;margin-bottom:14px;font-family:monospace;}
.da-textarea{min-height:80px;resize:vertical;}
.da-buttons{display:flex;gap:10px;justify-content:center;margin-bottom:15px;}
.da-btn{padding:9px 14px;font-size:13px;font-weight:600;border-radius:8px;border:none;cursor:pointer;}
.da-btn.primary{background:#2563eb;color:#fff;}
.da-btn.secondary{background:#e5e7eb;color:#111;}
.da-alert{margin-top:15px;padding:10px;border-radius:8px;font-weight:600;}
.da-alert.success{background:#dcfce7;color:#166534;}
.da-results{margin-top:20px;}
.da-results textarea{width:100%;padding:15px;font-size:15px;border-radius:10px;border:1px solid #ccc;background:#f1f5f9;resize:vertical;}
.progress-wrap{display:none;margin-top:15px;}
.progress-bar{height:6px;width:100%;background:#e5e7eb;border-radius:10px;overflow:hidden;}
.progress-fill{height:100%;width:0;background:#2563eb;animation:loading 1.2s linear infinite;}
@keyframes loading{0%{width:0}50%{width:70%}100%{width:100%}}
</style>
<script>
function showProgress(){
    document.querySelector('.progress-wrap').style.display='block';
}
</script>
</head>
<body>
<div class="da-card">
<div class="da-title">Robots.txt Generator Tool</div>
<div class="da-subtitle">Create your robots.txt file easily</div>
<form method="post" onsubmit="showProgress()">
    <label>User-agent (one per line)</label>
    <textarea class="da-textarea" name="user_agents" placeholder="*"><?= isset($_POST['user_agents']) ? htmlspecialchars($_POST['user_agents']) : '*' ?></textarea>
    <label>Disallow (one path per line)</label>
    <textarea class="da-textarea" name="disallow" placeholder="/admin/"><?= isset($_POST['disallow']) ? htmlspecialchars($_POST['disallow']) : '' ?></textarea>
    <label>Allow (one path per line)</label>
    <textarea class="da-textarea" name="allow" placeholder="/public/"><?= isset($_POST['allow']) ? htmlspecialchars($_POST['allow']) : '' ?></textarea>
    <label>Sitemap URLs (one per line)</label>
    <textarea class="da-textarea" name="sitemaps" placeholder="https://example.com/sitemap.xml"><?= isset($_POST['sitemaps']) ? htmlspecialchars($_POST['sitemaps']) : '' ?></textarea>
    <div class="da-buttons">
        <button class="da-btn primary" type="submit">Generate Robots.txt</button>
        <button class="da-btn secondary" type="button" onclick="location.href=location.pathname">Refresh</button>
    </div>
    <div class="progress-wrap">
        <div class="progress-bar"><div class="progress-fill"></div></div>
    </div>
</form>
<?php if ($robotsTxt): ?>
<div class="da-results">
    <label>Generated robots.txt:</label>
    <textarea readonly rows="10"><?= htmlspecialchars($robotsTxt) ?></textarea>
</div>
<?php endif; ?>
</div>
</body>
</html>
