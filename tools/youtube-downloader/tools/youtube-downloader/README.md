YouTube Downloader

A simple standalone PHP YouTube downloader interface for downloading content that you own or have permission to download.

This utility is part of the [WebDecky Tools](https://github.com/gmkhan1973/webdecky-tools) open-source project.

Features

* Simple YouTube URL input
* Video download support
* Audio download support
* MP3 audio conversion
* Single-video downloads
* Prevents playlist downloads
* Uses `yt-dlp`
* Lightweight standalone PHP implementation
* No database required
* No external PHP framework required

## Requirements

* PHP 8.0 or newer
* PHP-enabled web server
* `yt-dlp` installed and executable
* PHP `exec()` function enabled
* FFmpeg may be required by `yt-dlp` for audio extraction and conversion

Installation

Clone or download the WebDecky Tools repository:


git clone https://github.com/gmkhan1973/webdecky-tools.git


Open the YouTube Downloader directory:

tools/youtube-downloader/


Make sure `index.php` is placed on a PHP-enabled web server.

Configure the `yt-dlp` executable path in `index.php` according to your server environment.

Example:


$ytDlp = '/usr/local/bin/yt-dlp';


Usage

1. Open `index.php` through your PHP-enabled web server.
2. Enter a supported YouTube video URL.
3. Select the available download format.
4. Start the download.
5. The downloaded file is stored in the configured downloads directory.

Authorized Use

Use this tool only for content that you own, have permission to download, or are otherwise legally authorized to access and save.

Users are responsible for complying with YouTube's Terms of Service, copyright laws, and other applicable laws and platform policies.

This project does not provide or encourage methods for bypassing access controls, DRM, authentication, or other platform restrictions.

Web Version

You can explore the online tools provided by WebDecky:

**WebDecky:** https://webdecky.com/

**SEO & Web Tools:** https://webdecky.com/tools

## Contributing

Contributions, bug reports, suggestions, and improvements are welcome.

Please read the repository's [CONTRIBUTING.md](https://github.com/gmkhan1973/webdecky-tools/blob/main/CONTRIBUTING.md) before submitting changes.

License

This project is released under the MIT License.

See the [LICENSE](https://github.com/gmkhan1973/webdecky-tools/blob/main/LICENSE) file for details.

Author

Created by **G.M. Khan**

* Website: https://webdecky.com/
* GitHub: https://github.com/gmkhan1973
