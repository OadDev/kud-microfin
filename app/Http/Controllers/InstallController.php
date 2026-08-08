<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\NotificationTemplatesSeeder;
use Database\Seeders\PaymentSettingsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PDO;
use PDOException;
use Throwable;

class InstallController extends Controller
{
    public function show(): View
    {
        return view('install.wizard', [
            'checks' => $this->requirementChecks(),
        ]);
    }

    public function store(Request $request): RedirectResponse|View
    {
        $checks = $this->requirementChecks();
        if (collect($checks)->contains(fn ($c) => ! $c['ok'])) {
            return back()->withErrors(['db_host' => 'One or more server requirements are not met. Please fix them before continuing.']);
        }

        $data = $request->validate([
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_mobile' => ['required', 'digits:10'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'load_demo_data' => ['nullable', 'boolean'],
        ]);

        try {
            $pdo = new PDO(
                "mysql:host={$data['db_host']};port={$data['db_port']};dbname={$data['db_database']}",
                $data['db_username'],
                $data['db_password'] ?? '',
                [PDO::ATTR_TIMEOUT => 5]
            );
            $pdo = null;
        } catch (PDOException $e) {
            return back()->withInput()->withErrors(['db_host' => 'Could not connect to that database: '.$e->getMessage()]);
        }

        // Point this request's DB connection at the new database WITHOUT
        // touching .env yet — nothing is persisted until migrate/seed/admin
        // creation all actually succeed, so a failed attempt can just be
        // retried with the form still showing what was typed.
        config([
            'database.connections.mysql.host' => $data['db_host'],
            'database.connections.mysql.port' => $data['db_port'],
            'database.connections.mysql.database' => $data['db_database'],
            'database.connections.mysql.username' => $data['db_username'],
            'database.connections.mysql.password' => $data['db_password'] ?? '',
        ]);
        DB::purge('mysql');

        try {
            Artisan::call('migrate', ['--force' => true]);

            (new PaymentSettingsSeeder)->run();
            (new NotificationTemplatesSeeder)->run();

            User::create([
                'name' => $data['admin_name'],
                'mobile' => $data['admin_mobile'],
                'email' => $data['admin_email'] ?: null,
                'password' => Hash::make($data['admin_password']),
                'role' => 'admin',
                'status' => 'approved',
            ]);

            if ($request->boolean('load_demo_data')) {
                (new DemoDataSeeder)->run();
            }
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['db_host' => 'Setup failed while preparing the database: '.$e->getMessage()]);
        }

        $this->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
            'APP_URL' => $request->getSchemeAndHttpHost(),
        ]);
        Artisan::call('config:clear');

        file_put_contents(storage_path('app/installed.lock'), now()->toDateTimeString());

        return redirect()->route('admin.login')->with('success', 'Installation complete! Log in with the Admin account you just created.');
    }

    protected function requirementChecks(): array
    {
        $extensions = ['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'ctype', 'json', 'fileinfo', 'curl'];

        $checks = [
            ['label' => 'PHP version >= 8.2 (running '.PHP_VERSION.')', 'ok' => version_compare(PHP_VERSION, '8.2.0', '>=')],
        ];

        foreach ($extensions as $ext) {
            $checks[] = ['label' => "PHP extension: {$ext}", 'ok' => extension_loaded($ext)];
        }

        $checks[] = [
            'label' => '.env is writable',
            'ok' => file_exists(base_path('.env')) ? is_writable(base_path('.env')) : is_writable(base_path()),
        ];
        $checks[] = ['label' => 'storage/ is writable', 'ok' => is_writable(storage_path())];
        $checks[] = ['label' => 'bootstrap/cache/ is writable', 'ok' => is_writable(base_path('bootstrap/cache'))];

        return $checks;
    }

    protected function writeEnv(array $values): void
    {
        $path = base_path('.env');
        $content = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $escaped = preg_match('/\s|#|"/', (string) $value) ? '"'.str_replace('"', '\"', $value).'"' : $value;
            $line = "{$key}={$escaped}";

            if (preg_match("/^{$key}=.*$/m", $content)) {
                $content = preg_replace("/^{$key}=.*$/m", $line, $content);
            } else {
                $content .= "\n{$line}";
            }
        }

        file_put_contents($path, $content);
    }
}
