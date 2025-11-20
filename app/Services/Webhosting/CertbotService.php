<?php

namespace Pterodactyl\Services\Webhosting;

use Pterodactyl\Models\Website;
use Symfony\Component\Process\Process;

class CertbotService
{
    public function issue(Website $website, array $aliases = []): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return false;
        }

        $email = config('webhosting.certbot_email');
        $cmd = config('webhosting.certbot_command');
        if (! $cmd || ! $email) {
            return false;
        }

        $domains = array_unique(array_merge([$website->domain], $aliases));
        $args = [];
        foreach ($domains as $d) {
            $args[] = '-d ' . $d;
        }

        $command = $cmd . ' --nginx -n --agree-tos -m ' . escapeshellarg($email) . ' ' . implode(' ', $args);

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(120);
        $website->ssl_status = 'pending';
        $website->save();
        $process->run();

        if ($process->isSuccessful()) {
            $website->ssl_enabled = true;
            $website->ssl_status = 'active';
            $website->save();
            return true;
        }

        $website->ssl_status = 'failed';
        $website->save();
        return false;
    }
}

