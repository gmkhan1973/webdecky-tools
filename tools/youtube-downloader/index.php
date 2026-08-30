 <?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

$yt_dlp = '/usr/local/bin/yt-dlp';
$cookieFile = __DIR__ . "/cookies.txt";

/* ==============================
   AJAX: fetch video info
============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['youtube_url'])) {

    // Clean all previous output
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    $url = trim($_POST['youtube_url']);

    /* ==============================
       VALIDATE URL
    ============================== */

    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {

        echo json_encode([
            'error' => 'Invalid URL'
        ]);

        exit;
    }

    $escapedUrl = escapeshellarg($url);

    /* ==============================
       YT-DLP FLAGS
    ============================== */

    $baseFlags =
        "--no-playlist " .
        "--geo-bypass " .
        "--retries 3 " .
        "--extractor-retries 3 " .
        "--socket-timeout 15 " .
        "--force-ipv4 " .
        "--no-warnings " .
        "--no-check-certificates " .
        "--user-agent " . escapeshellarg(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124 Safari/537.36'
        );

    /* ==============================
       PRIMARY COMMAND (WITH COOKIES)
    ============================== */

    $cmd = escapeshellcmd($yt_dlp)
        . " -J "
        . $baseFlags
        . " --cookies "
        . escapeshellarg($cookieFile)
        . " "
        . $escapedUrl
        . " 2>&1";

    $output = [];
    $returnVar = 0;

    exec($cmd, $output, $returnVar);

    $rawOutput = implode("\n", $output);

    // Save debug log
    file_put_contents(
        __DIR__ . '/debug.txt',
        $rawOutput
    );

    /* ==============================
       EXTRACT JSON SAFELY
    ============================== */

    preg_match('/\{.*\}/s', $rawOutput, $matches);

    $json = $matches[0] ?? '';

    $data = json_decode($json, true);

    /* ==============================
       FALLBACK WITHOUT COOKIES
    ============================== */

    if (!$data || empty($data['formats'])) {

        $cmd = escapeshellcmd($yt_dlp)
            . " -J "
            . $baseFlags
            . " "
            . $escapedUrl
            . " 2>&1";

        $output = [];

        exec($cmd, $output, $returnVar);

        $rawOutput = implode("\n", $output);

        file_put_contents(
            __DIR__ . '/debug.txt',
            $rawOutput
        );

        preg_match('/\{.*\}/s', $rawOutput, $matches);

        $json = $matches[0] ?? '';

        $data = json_decode($json, true);
    }

    /* ==============================
       FINAL VALIDATION
    ============================== */

    if (!$data || empty($data['formats'])) {

        echo json_encode([
            'error' => 'yt-dlp failed or blocked',
            'debug' => $rawOutput,
            'code' => $returnVar
        ]);

        exit;
    }

    /* ==============================
       VIDEO INFO
    ============================== */

    $title = $data['title'] ?? 'YouTube Video';

    $thumbnail = $data['thumbnail'] ?? '';

   /* ==============================
   FILTER VIDEO + AUDIO FORMATS (FIXED)
============================== */

$video_formats = [];

/* ------------------------------
   1. VIDEO FORMATS
------------------------------ */
foreach ($data['formats'] as $f) {

    $isVideo = !empty($f['vcodec']) && $f['vcodec'] !== 'none';
    $height  = (int)($f['height'] ?? 0);

    if (!$isVideo || $height <= 0) {
        continue;
    }

    $size = $f['filesize']
        ?? $f['filesize_approx']
        ?? 0;

    // Keep best size per resolution
    if (
        !isset($video_formats[$height]) ||
        ($video_formats[$height]['filesize'] ?? 0) < $size
    ) {
        $video_formats[$height] = [
            'format_id' => $f['format_id'],
            'height' => $height,
            'filesize' => $size
        ];
    }
}

/* ------------------------------
   2. AUDIO FORMAT (MP3 FIX)
------------------------------ */

$audioFormat = null;

foreach ($data['formats'] as $f) {

    $isAudioOnly =
        (!empty($f['acodec']) && $f['acodec'] !== 'none') &&
        (!empty($f['vcodec']) && $f['vcodec'] === 'none');

    if ($isAudioOnly) {

        $audioSize = $f['filesize']
            ?? $f['filesize_approx']
            ?? 0;

        $audioFormat = [
            'format_id' => 'mp3',
            'height' => 0,
            'filesize' => $audioSize,
            'note' => 'audio'
        ];

        break; // take best available audio
    }
}

/* ------------------------------
   MERGE & SORT
------------------------------ */

$video_formats = array_values($video_formats);

usort($video_formats, function ($a, $b) {
    return ($b['height'] ?? 0) <=> ($a['height'] ?? 0);
});

/* Add MP3 at top or bottom (your choice) */
if ($audioFormat) {
    array_unshift($video_formats, $audioFormat);
} else {
    $video_formats[] = [
        'format_id' => 'mp3',
        'height' => 0,
        'filesize' => 0,
        'note' => 'audio'
    ];
}

/* ==============================
   OUTPUT JSON
============================== */

echo json_encode([
    'success' => true,
    'title' => $title,
    'thumbnail' => $thumbnail,
    'video_formats' => $video_formats
]);

exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>YouTube Video Downloader</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<style>
*{box-sizing:border-box}
.header{display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;margin-bottom:16px}
.logo{width:56px;height:56px;border-radius:10px;background:linear-gradient(135deg,#ff4b4b,#ff8b4b);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:20px}
.title{font-size:20px;margin:0}
.header div{font-size:14px;color:#64748b}

.form-row{margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
input[type="text"]{flex:1;min-width:180px;padding:12px;border-radius:10px;border:1px solid #e2e8f0;font-size:15px}
.btn{padding:12px 18px;border-radius:10px;border:none;background:#ff4b4b;color:#fff;font-weight:600;cursor:pointer}
#refresh-btn{background:#4caf50}

.progress-container{width:100%;background:#edf2f7;border-radius:12px;overflow:hidden;margin-top:18px}
.progress-bar{width:0;height:10px;background:linear-gradient(90deg,#ff8b4b,#ff4b4b);transition:width .35s}
#progress-text{text-align:center;margin-top:8px;font-weight:600;color:#2d3748}

.video-wrapper{display:flex;gap:18px;align-items:flex-start;margin-top:18px;padding:16px;border-radius:12px;border:1px solid #ffebe9;flex-wrap:wrap}
.video-thumb{flex:0 0 260px;min-width:120px}
.video-thumb img{width:100%;border-radius:10px}
.video-info{flex:1;min-width:180px}
.video-title{font-size:18px;margin:0 0 12px 0;}

.download-buttons{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px}
.download-buttons button{display:flex;justify-content:space-between;padding:10px;border-radius:9px;border:none;box-shadow:0 6px 18px rgba(15,23,42,0.06);cursor:pointer;font-weight:600}
.badge{background:#111;color:#fff;padding:4px 8px;border-radius:6px;font-size:12px}
.size{color:#475569;font-size:13px}
</style>
</head>

<body>

<div class="container">
  <div class="header">
    <div class="logo">YT</div>
    <div>
      <h2 class="title">YouTube Video Downloader</h2>
      <div style="color:#64748b;font-size:14px">Save YouTube videos to MP4 or MP3 — fast & secure</div>
    </div>
  </div>

  <div class="form-row">
    <input id="yt-url" type="text" placeholder="Paste YouTube URL">
    <button id="fetch-btn" class="btn">Fetch Video</button>
    <button id="refresh-btn" class="btn">Refresh</button>
  </div>

  <div class="progress-container">
    <div class="progress-bar" id="progress-bar"></div>
  </div>
  <div id="progress-text">Idle</div>

  <div id="result-area"></div>
</div>

<script>

const fetchBtn = document.getElementById('fetch-btn');

const ytUrlInput = document.getElementById('yt-url');

const resultArea = document.getElementById('result-area');

const progressBar = document.getElementById('progress-bar');

const progressText = document.getElementById('progress-text');

function setProgress(percent,text){

    progressBar.style.width = percent + '%';

    progressText.innerText = text;
}

function humanSize(bytes){

    if(!bytes){
        return 'Unknown size';
    }

    const units = ['B','KB','MB','GB'];

    let i = 0;

    while(bytes >= 1024 && i < units.length - 1){

        bytes /= 1024;

        i++;
    }

    return bytes.toFixed(2) + ' ' + units[i];
}

fetchBtn.addEventListener('click', async () => {

    const url = ytUrlInput.value.trim();

    if(!url){

        alert('Please enter YouTube URL');

        return;
    }

    resultArea.innerHTML = '';

    setProgress(15,'Fetching...');

    try {

        const response = await fetch(window.location.href,{

            method:'POST',

            headers:{
                'Content-Type':'application/x-www-form-urlencoded'
            },

            body:'youtube_url=' + encodeURIComponent(url)
        });

        let data;

        try {

            data = await response.json();

        } catch(err){

            const txt = await response.text();

            console.log(txt);

            resultArea.innerHTML =
                '<div class="error">Invalid JSON Response</div>';

            setProgress(0,'Failed');

            return;
        }

        if(data.error){

            resultArea.innerHTML =
                '<div class="error">' + data.error + '</div>';

            setProgress(0,'Failed');

            return;
        }

        setProgress(80,'Loading formats...');

        let html = '';

        data.video_formats.forEach(f => {

            let label = '';

            let badge = '';

            if(f.format_id === 'mp3'){

                label = 'MP3 Audio';

            } else {

                const height = parseInt(f.height) || 0;

                label = height + 'p';

                if(height >= 720){

                    badge =
                        '<span class="badge">HD</span>';

                } else {

                    badge =
                        '<span class="badge" style="background:#64748b">SD</span>';
                }
            }

            html += `
                <button onclick="downloadVideo(
                    '${encodeURIComponent(f.format_id)}',
                    '${encodeURIComponent(
                        f.format_id === 'mp3'
                        ? 'audio'
                        : 'mp4'
                    )}',
                    '${encodeURIComponent(url)}'
                )">

                    <span>
                        ${label} ${badge}
                    </span>

                    <span class="size">
                        ${humanSize(f.filesize)}
                    </span>

                </button>
            `;
        });

        resultArea.innerHTML = `
            <div class="video-wrapper">

                <div class="video-thumb">
                    <img src="${data.thumbnail}">
                </div>

                <div class="video-info">

                    <h3 class="video-title">
                        ${data.title}
                    </h3>

                    <div class="download-buttons">
                        ${html}
                    </div>

                </div>

            </div>
        `;

        setProgress(100,'Ready');

    } catch(error){

        console.log(error);

        resultArea.innerHTML =
            '<div class="error">Server Request Failed</div>';

        setProgress(0,'Failed');
    }

});

function downloadVideo(format_id, format, url){

  window.open(`/main-tools/youtube/download.php?file=${format_id}&format=${format}&url=${encodeURIComponent(url)}`, '_blank');
}
document
.getElementById('refresh-btn')
.addEventListener('click', () => {

    ytUrlInput.value = '';

    resultArea.innerHTML = '';

    setProgress(0,'Idle');
});

</script>

</body>
</html>
