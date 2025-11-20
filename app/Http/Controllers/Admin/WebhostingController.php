<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Website;
use Pterodactyl\Services\Webhosting\NginxConfigService;
use Pterodactyl\Services\Webhosting\CertbotService;

class WebhostingController extends Controller
{
    public function __construct(
        private ViewFactory $view,
        private NginxConfigService $nginx,
        private CertbotService $certbot,
    ) {
    }

    public function index()
    {
        $websites = Website::with('user')->orderByDesc('id')->paginate(25);
        return $this->view->make('admin.webhosting.index', compact('websites'));
    }

    public function create()
    {
        $users = User::orderBy('username')->get(['id', 'username', 'email']);
        return $this->view->make('admin.webhosting.new', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'domain' => ['required', 'string', 'max:191', 'unique:websites,domain'],
            'root_path' => ['required', 'string'],
            'php_version' => ['nullable', 'string', 'max:50'],
            'enable_ssl' => ['nullable', 'boolean'],
        ]);

        $website = Website::create([
            'user_id' => $data['user_id'],
            'domain' => strtolower($data['domain']),
            'root_path' => $data['root_path'],
            'php_version' => $data['php_version'] ?? null,
            'ssl_enabled' => false,
            'ssl_status' => 'none',
            'enabled' => true,
        ]);

        $this->nginx->provision($website, [], (bool) ($data['enable_ssl'] ?? false));
        $this->nginx->reload();

        if (!empty($data['enable_ssl'])) {
            $this->certbot->issue($website);
            $this->nginx->provision($website, [], true);
            $this->nginx->reload();
        }

        return redirect()->route('admin.webhosting.view', $website->id);
    }

    public function view(Website $website)
    {
        return $this->view->make('admin.webhosting.view', compact('website'));
    }

    public function reprovision(Website $website): RedirectResponse
    {
        $this->nginx->provision($website, [], $website->ssl_enabled);
        $this->nginx->reload();
        return redirect()->route('admin.webhosting.view', $website->id);
    }

    public function issueSsl(Website $website): RedirectResponse
    {
        $this->certbot->issue($website);
        $this->nginx->provision($website, [], true);
        $this->nginx->reload();
        return redirect()->route('admin.webhosting.view', $website->id);
    }

    public function toggle(Website $website): RedirectResponse
    {
        $website->enabled = ! $website->enabled;
        $website->save();
        return redirect()->route('admin.webhosting.view', $website->id);
    }
}

