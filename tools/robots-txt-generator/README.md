Robots.txt Generator

A simple standalone PHP tool for generating a `robots.txt` file.

This utility is part of the [WebDecky Tools](https://github.com/gmkhan1973/webdecky-tools) open-source project.

Features

* Generate `robots.txt` content using PHP
* Add multiple user-agent rules
* Add `Disallow` paths
* Add `Allow` paths
* Add one or more sitemap URLs
* Simple and lightweight
* No database required
* No external PHP packages required

Requirements

* PHP 8.0 or newer
* A PHP-enabled web server

Installation

Clone or download this repository and place the utility on a PHP-enabled server.


git clone https://github.com/gmkhan1973/webdecky-tools.git

Then open:


tools/robots-txt-generator/index.php


Example Output

User-agent: *

Disallow: /admin/
Disallow: /private/

Allow: /public/

Sitemap: https://example.com/sitemap.xml

Web Version

You can also explore the online SEO and web tools available on [WebDecky](https://webdecky.com/tools).

Contributing

Contributions and improvements are welcome. Please read the repository's [CONTRIBUTING.md](../../CONTRIBUTING.md) before submitting changes.

License

This project is released under the MIT License. See the [LICENSE](../../LICENSE) file for details.

Author

Created by G.M. Khan.

* Website: https://webdecky.com/
* GitHub: https://github.com/gmkhan1973
