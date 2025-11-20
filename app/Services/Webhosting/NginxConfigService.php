<?php

namespace Pterodactyl\Services\Webhosting;

use Pterodactyl\Models\Website;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;

class NginxConfigService
{
    public function provision(Website $website, array $aliases = [], bool $enableSsl = false): string
    {
        $serverNames = trim(implode(' ', array_unique(array_merge([$website->domain], $aliases))));
        $root = rtrim($website->root_path, '/');
        $phpUpstream = config('webhosting.default_php_fpm');

        $sslBlock = $enableSsl ? $this->sslServerBlock($serverNames, $root) : '';

        $config = $this->httpServerBlock($serverNames, $root, $phpUpstream) . $sslBlock;

        $path = rtrim(config('webhosting.nginx_sites_path'), '/') . '/site-' . $website->domain . '.conf';
        File::put($path, $config);
        $website->nginx_config_path = $path;
        $website->save();

        return $path;
    }

    public function reload(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return;
        }

        $cmd = config('webhosting.nginx_reload_command');
        if (! $cmd) {
            return;
        }

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(30);
        $process->run();
    }

    private function httpServerBlock(string $serverNames, string $root, string $phpUpstream): string
    {
        return implode("\n", [
            'server {',
            '    listen 80;',
            '    server_name ' . $serverNames . ';',
            '    root ' . $root . ';',
            '    index index.php index.html index.htm;',
            '    location /.well-known/acme-challenge/ {',
            '        allow all;',
            '    }',
            '    location / {',
            '        try_files $uri $uri/ /index.php?$query_string;',
            '    }',
            '    location ~ \\.(php|phar)$ {',
            '        include fastcgi_params;',
            '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;',
            '        fastcgi_pass ' . $phpUpstream . ';',
            '    }',
            '    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg)$ {',
            '        expires 30d;',
            '    }',
            '}',
            '',
        ]);
    }

    private function sslServerBlock(string $serverNames, string $root): string
    {
        return implode("\n", [
            'server {',
            '    listen 443 ssl;',
            '    server_name ' . $serverNames . ';',
            '    root ' . $root . ';',
            '    index index.php index.html index.htm;',
            '    ssl_certificate /etc/letsencrypt/live/' . explode(' ', $serverNames)[0] . '/fullchain.pem;',
            '    ssl_certificate_key /etc/letsencrypt/live/' . explode(' ', $serverNames)[0] . '/privkey.pem;',
            '    location / {',
            '        try_files $uri $uri/ /index.php?$query_string;',
            '    }',
            '}',
            '',
        ]);
    }
}

