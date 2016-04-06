Wikimedia Grants Review
=======================

Review grant applications.

System Requirements
-------------------
* PHP >= 5.3.7

Setup
-----

### Sample Apache .htaccess file

    <IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule .* index.php/$0 [L,PT]
    </IfModule>


Configuration
-------------

The application follows the [Twelve-Factor App](http://12factor.net/)
configuration principle of configuration via environment variables. The
following variables are expected to be provided:

* DB_DSN = PDO DSN
* DB_USER = PDO username
* DB_PASS = PDO password

The following variables can be optionally provided:

* LOG_FILE = fopen()-compatible filename or stream URI (default: `php://stderr`)
* LOG_LEVEL = PSR-3 logging level (default: `notice`)
* SMTP_HOST = SMTP mail server (default: `localhost`)
* CACHE_DIR = Directory to cache twig templates (default: `data/cache`)

### Apache

    SetEnv DB_DSN mysql:host=localhost;dbname=scholarships;charset=utf8
    SetEnv DB_USER my_database_user
    SetEnv DB_PASS "super secret password"

### .env file

For environments where container based configuration isn't possible or
desired, a `.env` file can be placed in the root of the project. This file
will be parsed using PHP's `parse_ini_file()` function and the resulting
settings will be injected into the application environment.

    DB_DSN="mysql:host=localhost;dbname=scholarships;charset=utf8"
    DB_USER=my_database_user
    DB_PASS="super secret password"
    APPLICATION_OPEN=2013-01-01T00:00:00Z
    APPLICATION_CLOSE=2013-02-01T00:00:00Z
    MOCK=1


Hacking
-------

We manage PHP dependencies using Composer. This git repository includes the
Composer managed resources that are needed for deployment on the Wikimedia
Foundation production servers.

For local testing you will need to install several additional development-only
libraries:

  composer install

Once the testing libraries are installed you can run tests with this command:

  composer test

When submitting a patch for review you must ensure that your locally installed
testing libraries have been removed:

  composer install --no-dev
  composer dump-autoload --no-dev

A typical git commit should not include any changes to `composer.lock` or
files in the `vendor` directory. These files should only be updated when a new
runtime dependency is added or when the exact versions of the testing
libraries are updated.

Authors
-------
* Bryan Davis, Wikimedia Foundation
* Niharika Kohli, Wikimedia Foundation

Based on code developed for the Wikimania Scaholarships application.

License
-------
[GNU GPL 3.0](www.gnu.org/copyleft/gpl.html "GNU GPL 3.0")
