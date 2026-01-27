<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class InstallController extends Controller
{
    public function index()
    {
        return view('install.index');
    }

    public function requirements()
    {
        $requirements = [
            'PHP Version >= 8.2' => version_compare(phpversion(), '8.2.0', '>='),
            'BCMath' => extension_loaded('bcmath'),
            'Ctype' => extension_loaded('ctype'),
            'JSON' => extension_loaded('json'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),
            'PDO' => extension_loaded('pdo'),
            'Tokenizer' => extension_loaded('tokenizer'),
            'XML' => extension_loaded('xml'),
        ];

        $allMet = !in_array(false, $requirements);

        return view('install.requirements', compact('requirements', 'allMet'));
    }

    public function permissions()
    {
        $permissions = [
            'storage/framework/' => is_writable(storage_path('framework')),
            'storage/logs/' => is_writable(storage_path('logs')),
            'bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
            '.env' => is_writable(base_path('.env')),
        ];

        $allMet = !in_array(false, $permissions);

        return view('install.permissions', compact('permissions', 'allMet'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function storeDatabase(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'port' => 'required',
            'database' => 'required',
            'username' => 'required',
        ]);

        try {
            // Test connection logic here if needed, or just write to env
            // For simplicity, we'll write to env then try to migrate
            
            $this->updateEnv([
                'DB_HOST' => $request->host,
                'DB_PORT' => $request->port,
                'DB_DATABASE' => $request->database,
                'DB_USERNAME' => $request->username,
                'DB_PASSWORD' => $request->password ?? '',
            ]);

            // Clear config to ensure new env vars are picked up
            Artisan::call('config:clear');
            
            // Force reconnection with new config
            DB::purge('mysql');
            config([
                'database.connections.mysql.host' => $request->host,
                'database.connections.mysql.port' => $request->port,
                'database.connections.mysql.database' => $request->database,
                'database.connections.mysql.username' => $request->username,
                'database.connections.mysql.password' => $request->password ?? '',
            ]);
            DB::reconnect('mysql');
            
            // Run migrations
            Artisan::call('migrate:fresh', ['--force' => true]);
            
            return redirect()->route('install.admin');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Database connection failed: ' . $e->getMessage())->withInput();
        }
    }

    public function admin()
    {
        return view('install.admin');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            // Assign admin role
            $user->role = 'admin';
            $user->save();
            
            // Mark installed
            $this->updateEnv(['APP_INSTALLED' => 'true']);
            file_put_contents(storage_path('installed'), 'installed on ' . date('Y-m-d H:i:s'));
            
            return redirect()->route('login')->with('success', 'Installation successful! Please login.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create admin: ' . $e->getMessage())->withInput();
        }
    }

    protected function updateEnv($data)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            foreach ($data as $key => $value) {
                $value = '"' . trim($value) . '"'; // Quote values
                if (strpos($content, "{$key}=") !== false) {
                     $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                } else {
                    $content .= "\n{$key}={$value}";
                }
            }
            file_put_contents($path, $content);
        }
    }
}
