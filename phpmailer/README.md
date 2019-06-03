PHP Mailer SMTP
============

by Marcus Mueller

This addon replaces the default `mail()` function by the `PHPMailer` library, allowing the use of an outbound SMTP server.

Configuration
-------------

You can override the default value of the following config keys in your base Friendica install `config/addon.config.php` file:

	'phpmailer' => [
        // smtp (Boolean)
        // Enables SMTP relaying for outbound emails
        'smtp' => false,

        // smtp_server (String)
        // SMTP server host name
        'smtp_server' => 'smtp.example.com',

        // smtp_port (Integer)
        // SMTP server port number
        'smtp_port' => 25,

        // smtp_secure (String)
        // What kind of encryption to use on the SMTP connection.
        // Options: '', 'ssl' or 'tls'.
        'smtp_secure' => '',

        // smtp_port_s (Integer)
        // Secure SMTP server port number
        'smtp_port_s' => 465,

        // smtp_username (String)
        // SMTP server authentication user name
        // Empty string disables authentication
        'smtp_username' => '',

        // smtp_password (String)
        // SMTP server authentication password
        // Empty string disables authentication
        'smtp_password' => '',

        // smtp_from (String)
        // From address used when using the SMTP server
        // Example: no-reply@example.com
        'smtp_from' => '',
    ],

License
=======

The _PHPMailer addon_ is licensed under the [GNU Affero General Public License v3](https://www.gnu.org/licenses/agpl-3.0.html).

The _PHP Mailer_ library is licensed under the [GNU Lesser General Public License](https://www.gnu.org/licenses/lgpl-3.0.html).
