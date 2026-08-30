<?php

declare(strict_types=1);

error_reporting(E_ERROR | E_PARSE);
session_start();

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
| Set the correct path to yt-dlp on your server.
*/
$ytDlp = '/usr/local/bin/yt-dlp';

$message = '';
$error = '';
$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $url = trim($_POST['url'] ?? '');
    $format = $_POST['format'] ?? 'video';

    if ($url === '') {
        $error = 'Please enter a YouTube URL.';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid URL.';
    } elseif (!preg_match(
        '~^(https?://)?(www\.)?(youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)~i',
        $url
    )) {
        $error = 'Please enter a valid YouTube video URL.';
    } elseif (!is_file($ytDlp) || !is_executable($ytDlp)) {
        $error = 'yt-dlp is not installed or is not executable on this server.';
    } else {

        /*
        |----------------------------------------------------------------------
        | Output directory
        |----------------------------------------------------------------------
        */
        $downloadDir = __DIR__ . '/downloads';

        if (!is_dir($downloadDir)) {
            mkdir($downloadDir, 0755, true);
        }

        /*
        |----------------------------------------------------------------------
        | Build command
        |----------------------------------------------------------------------
        | --no-playlist prevents accidentally downloading an entire playlist.
        | --restrict-filenames creates safer filenames.
        */
        if ($format === 'audio') {

            $command = sprintf(
                '%s --no-playlist --restrict-filenames -x --audio-format mp3 -o %s %s 2>&1',
                escapeshellarg($ytDlp),
                escapeshellarg($downloadDir . '/%(title)s.%(ext)s'),
                escapeshellarg($url)
            );

        } else {

            $command = sprintf(
                '%s --no-playlist --restrict-filenames -f "best[ext=mp4]/best" -o %s %s 2>&1',
                escapeshellarg($ytDlp),
                escapeshellarg($downloadDir . '/%(title)s.%(ext)s'),
                escapeshellarg($url)
            );
        }

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $result = implode("\n", $output);
            $message = 'Download completed successfully.';
        } else {
            $error = 'The download could not be completed.';
            $result = implode("\n", $output);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<title>YouTube Downloader | WebDecky</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="description"
      content="A simple standalone PHP YouTube downloader utility from WebDecky.">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    color: #111827;
}

.wd-card {
    max-width: 850px;
    margin: 60px auto;
    padding: 35px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.wd-title {
    text-align: center;
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 8px;
}

.wd-subtitle {
    text-align: center;
    color: #6b7280;
    margin-bottom: 30px;
}

.wd-label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.wd-input,
.wd-select {
    width: 100%;
    padding: 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 16px;
    margin-bottom: 18px;
}

.wd-button {
    width: 100%;
    padding: 14px;
    border: 0;
    border-radius: 10px;
    background: #2563eb;
    color: #ffffff;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

.wd-button:hover {
    opacity: .9;
}

.wd-message,
.wd-error {
    margin-top: 20px;
    padding: 14px;
    border-radius: 10px;
    font-weight: 600;
}

.wd-message {
    background: #dcfce7;
    color: #166534;
}

.wd-error {
    background: #fee2e2;
    color: #991b1b;
}

.wd-output {
    margin-top: 20px;
}

.wd-output textarea {
    width: 100%;
    min-height: 150px;
    padding: 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-family: monospace;
    resize: vertical;
}

.wd-note {
    margin-top: 25px;
    padding: 15px;
    background: #f3f4f6;
    border-radius: 10px;
    color: #4b5563;
    font-size: 14px;
    line-height: 1.6;
}

.wd-footer {
    text-align: center;
    margin-top: 25px;
    color: #6b7280;
    font-size: 14px;
}

.wd-footer a {
    color: #2563eb;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="wd-card">

    <div class="wd-title">
        YouTube Downloader
    </div>

    <div class="wd-subtitle">
        Download videos or audio using a standalone PHP utility.
    </div>

    <form method="post">

        <label class="wd-label" for="url">
            YouTube URL
        </label>

        <input
            class="wd-input"
            type="url"
            id="url"
            name="url"
            placeholder="https://www.youtube.com/watch?v=..."
            value="<?= htmlspecialchars($_POST['url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label class="wd-label" for="format">
            Format
        </label>

        <select class="wd-select" id="format" name="format">

            <option value="video"
                <?= ($_POST['format'] ?? '') === 'video' ? 'selected' : '' ?>>
                Video (MP4)
            </option>

            <option value="audio"
                <?= ($_POST['format'] ?? '') === 'audio' ? 'selected' : '' ?>>
                Audio (MP3)
            </option>

        </select>

        <button class="wd-button" type="submit">
            Download
        </button>

    </form>

    <?php if ($message): ?>
        <div class="wd-message">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="wd-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($result): ?>
        <div class="wd-output">

            <label class="wd-label">
                Downloader Output
            </label>

            <textarea readonly><?= htmlspecialchars(
                $result,
                ENT_QUOTES,
                'UTF-8'
            ) ?></textarea>

        </div>
    <?php endif; ?>

    <div class="wd-note">
        <strong>Usage notice:</strong>
        Only download videos or audio that you own or have permission to
        download. Users are responsible for complying with YouTube's terms,
        copyright law, and applicable laws in their country.
    </div>

    <div class="wd-footer">
        Powered by
        <a href="https://webdecky.com/" target="_blank" rel="noopener">
            WebDecky
        </a>
    </div>

</div>

</body>
</html>
