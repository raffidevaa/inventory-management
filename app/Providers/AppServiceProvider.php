<?php

namespace App\Providers;

use App\Models\User;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\UniformBucketLevelAccessVisibility;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Storage::extend('gcs', function ($app, $config) {
            $options = ['projectId' => $config['project_id']];

            if (! empty($config['key_file_path'])) {
                $options['keyFilePath'] = $config['key_file_path'];
            }

            $client = new StorageClient($options);
            $bucket = $client->bucket($config['bucket']);
            $adapter = new GoogleCloudStorageAdapter($bucket, $config['path_prefix'] ?? '', new UniformBucketLevelAccessVisibility);

            return new class(new Filesystem($adapter, $config), $adapter, $config) extends FilesystemAdapter
            {
                public function url($path)
                {
                    if (isset($this->config['url'])) {
                        return rtrim($this->config['url'], '/').'/'.ltrim($path, '/');
                    }

                    return parent::url($path);
                }
            };
        });

        // Admin-only gate: user management, role changes
        Gate::define('manage-users', fn (User $user) => $user->hasRole('admin'));

        // Admin + Staff gate: item and borrowing CRUD
        Gate::define('manage-inventory', fn (User $user) => $user->hasAnyRole(['admin', 'staff']));

        // Admin + Staff gate: processing returns
        Gate::define('process-return', fn (User $user) => $user->hasAnyRole(['admin', 'staff']));

        // Admin-only gate: category management
        Gate::define('manage-categories', fn (User $user) => $user->hasRole('admin'));

        // All authenticated roles can view reports/dashboard
        Gate::define('view-reports', fn (User $user) => true);
    }
}
