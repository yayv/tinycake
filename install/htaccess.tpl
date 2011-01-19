RewriteEngine On
RewriteBase /{basedir}

RewriteCond %{REQUEST_URI} !^/{basedir}/(|v|js|upload)/.*
RewriteRule ^(.*)$ index.php
