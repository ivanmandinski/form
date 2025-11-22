# Fix: Password Must Use Bcrypt Algorithm

Laravel requires passwords to be hashed using bcrypt. If you manually added a plain text password, you need to hash it.

## Solution: Hash the Password Using Laravel

### Option 1: Using Railway Tinker (Recommended)

Run this command to update the password:

```bash
railway run php artisan tinker
```

Then in tinker:
```php
$user = App\Models\User::where('email', 'admin@formmonitor.com')->first();
$user->password = bcrypt('your-password-here');
$user->save();
echo "Password updated successfully!\n";
exit
```

Replace `'your-password-here'` with your desired password.

### Option 2: Using Railway Tinker One-Liner

You can also do it in one command:

```bash
railway run php artisan tinker --execute="\$user = App\Models\User::where('email', 'admin@formmonitor.com')->first(); \$user->password = bcrypt('your-password-here'); \$user->save(); echo 'Password updated!';"
```

### Option 3: Direct SQL Update (If Tinker Doesn't Work)

If tinker doesn't work due to connection issues, you can update it directly in the database:

1. Go to Railway Dashboard → PostgreSQL service
2. Click "Connect" or "Query" 
3. Run this SQL (replace `'your-password-here'` with your desired password):

```sql
UPDATE users 
SET password = '$2y$12$' || encode(gen_random_bytes(32), 'base64')
WHERE email = 'admin@formmonitor.com';
```

Actually, that won't work. Better to use Laravel's Hash facade. Let me provide a better SQL solution:

**Better SQL approach:** Generate the hash first, then update:

1. First, generate a bcrypt hash. You can use this PHP one-liner:
```bash
php -r "echo password_hash('your-password-here', PASSWORD_BCRYPT);"
```

2. Then update the database with the generated hash:
```sql
UPDATE users 
SET password = 'PASTE_THE_GENERATED_HASH_HERE'
WHERE email = 'admin@formmonitor.com';
```

### Option 4: Create a Temporary Artisan Command

Create a simple command to update the password:

```bash
railway run php artisan make:command UpdateAdminPassword
```

But this might be overkill. Option 1 or 2 is easier.

## Verify the Password Works

After updating, try logging in:
- Email: `admin@formmonitor.com`
- Password: (the password you just set)

## Default Admin Password

If you want to use the default password from the seeder:
- Password: `password`

To set it:
```php
$user->password = bcrypt('password');
```

## Important Notes

1. **Never store plain text passwords** - Laravel always hashes passwords
2. **Bcrypt format** - Laravel uses bcrypt which starts with `$2y$`
3. **Password length** - Bcrypt hashes are always 60 characters long
4. **Security** - Use a strong password in production!

## Troubleshooting

### "User not found"
Make sure the email is correct:
```php
$user = App\Models\User::where('email', 'admin@formmonitor.com')->first();
if (!$user) {
    echo "User not found. Creating...\n";
    $user = App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@formmonitor.com',
        'password' => bcrypt('password'),
    ]);
    // Assign admin role if needed
    $user->assignRole('admin');
}
```

### "Still getting bcrypt error"
Make sure you're using `bcrypt()` function, not just setting a plain string:
- ✅ Correct: `$user->password = bcrypt('password');`
- ❌ Wrong: `$user->password = 'password';`


