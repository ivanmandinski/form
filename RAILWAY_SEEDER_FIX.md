# Fix: Database Connection Error When Running Seeder

The error "could not translate host name 'postgres.railway.internal'" usually means the command isn't running in Railway's environment properly.

## Solution 1: Verify Railway Link

Make sure you're linked to the correct project:

```bash
railway link
```

Select your project (e.g., `fearless-light`).

## Solution 2: Check Service Status

1. Go to Railway Dashboard
2. Check that your PostgreSQL service is **Active** and running
3. If it's stopped, start it

## Solution 3: Run from Railway Dashboard Terminal

Instead of using `railway run`, try using Railway's web terminal:

1. Go to Railway Dashboard → Your Web Service
2. Click on "Deployments" → Latest deployment
3. Look for a "Terminal" or "Console" option
4. Run the seeder there:
   ```bash
   php artisan db:seed --class=AdminUserSeeder
   ```

## Solution 4: Use Railway Shell

Try using Railway's shell command:

```bash
railway shell
```

Then inside the shell:
```bash
php artisan db:seed --class=AdminUserSeeder
```

## Solution 5: Check Environment

Make sure you're in the correct environment:

```bash
railway status
```

This will show which project and environment you're linked to.

## Solution 6: Manual Database Connection Test

Test if the database connection works:

```bash
railway run php artisan tinker
```

Then in tinker:
```php
DB::connection()->getPdo();
```

If this works, the connection is fine and the issue might be with the seeder itself.

## Solution 7: Alternative - Create Admin User Manually

If the seeder keeps failing, create the admin user manually:

```bash
railway run php artisan tinker
```

Then:
```php
use App\Models\User;
use Spatie\Permission\Models\Role;

// Create admin role
$adminRole = Role::firstOrCreate(['name' => 'admin']);

// Create admin user
$adminUser = User::firstOrCreate(
    ['email' => 'admin@formmonitor.com'],
    [
        'name' => 'Admin User',
        'password' => bcrypt('password'),
    ]
);

// Assign admin role
$adminUser->assignRole($adminRole);

echo "Admin user created successfully!\n";
echo "Email: admin@formmonitor.com\n";
echo "Password: password\n";
exit
```

## Why This Happens

The `postgres.railway.internal` hostname only works within Railway's internal network. When you run `railway run`, it should execute the command in Railway's environment where this hostname is resolvable. If it's not working, it might be:

1. Network connectivity issue
2. Database service not properly connected
3. Command not executing in Railway's environment

## Verify It Worked

After creating the admin user, verify it exists:

```bash
railway run php artisan tinker
```

Then:
```php
$user = App\Models\User::where('email', 'admin@formmonitor.com')->first();
if ($user) {
    echo "User found: " . $user->name . "\n";
    echo "Has admin role: " . ($user->hasRole('admin') ? 'Yes' : 'No') . "\n";
} else {
    echo "User not found\n";
}
exit
```


