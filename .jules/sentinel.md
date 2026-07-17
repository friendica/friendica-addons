## 2024-05-15 - Unsanitized User Input in Heredoc Output (XSS)
**Vulnerability:** Found unescaped user input `$_GET['channels']` embedded directly into a string literal representing HTML via Heredoc (`<<< EOT`) in `irc/irc.php`.
**Learning:** Even simple variable concatenation in Heredoc strings bypasses template engine auto-escaping protections, leaving the application vulnerable to XSS if input comes from external sources like query parameters.
**Prevention:** Always use appropriate sanitization functions (like `htmlspecialchars` for HTML attributes and `urlencode` for URLs) when generating HTML natively without a template engine.
