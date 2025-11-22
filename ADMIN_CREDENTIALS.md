# Admin Login Credentials

## Default Admin Account

After running the `AdminUserSeeder`, you can log in with:

- **Email**: `admin@formmonitor.com`
- **Password**: `password`

## Creating the Admin User on Railway

If you haven't run the seeder yet, run it on Railway:

```bash
railway run php artisan db:seed --class=AdminUserSeeder
```

This will:
1. Create an admin role (if it doesn't exist)
2. Create an admin user with email `admin@formmonitor.com` and password `password`
3. Assign the admin role to the user

## Security Note

⚠️ **Important**: Change the default password immediately after first login!

The default password is `password` which is not secure for production. After logging in:

1. Go to your profile/settings
2. Change the password to something strong
3. Or update it directly in the database

## Changing the Admin Password

### Option 1: Via Laravel Tinker (Recommended)

```bash
railway run php artisan tinker
```

Then in tinker:
```php
$user = App\Models\User::where('email', 'admin@formmonitor.com')->first();
$user->password = bcrypt('your-new-password');
$user->save();
exit
```

### Option 2: Create a New Admin User

You can also create a new admin user with a custom password:

```bash
railway run php artisan tinker
```

Then:
```php
use App\Models\User;
use Spatie\Permission\Models\Role;

$adminRole = Role::firstOrCreate(['name' => 'admin']);
$user = User::create([
    'name' => 'Your Name',
    'email' => 'your-email@example.com',
    'password' => bcrypt('your-secure-password'),
]);
$user->assignRole($adminRole);
exit
```

## Verifying Admin Access

After logging in, you should be redirected to the admin dashboard at `/admin/dashboard`.

If you have the admin role, you'll have access to:
- Admin dashboard
- Target management
- Form configuration
- Check run monitoring
- All administrative features

