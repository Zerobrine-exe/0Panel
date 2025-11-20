<?php

return [
    'nginx_sites_path' => env('NGINX_SITES_PATH', '/etc/nginx/http.d'),
    'nginx_reload_command' => env('NGINX_RELOAD_CMD', 'nginx -s reload'),
    'certbot_command' => env('CERTBOT_CMD', 'certbot'),
    'certbot_email' => env('CERTBOT_EMAIL', ''),
    'default_php_fpm' => env('PHP_FPM_UPSTREAM', 'localhost:9000'),
];

