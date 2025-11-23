<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyCaptchaSolver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captcha:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify CAPTCHA solver configuration (2captcha/AntiCaptcha)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 CAPTCHA Solver Configuration Verification');
        $this->line('');

        // Check configuration
        $apiKey = config('form-monitor.captcha.api_key', '');
        $provider = config('form-monitor.captcha.provider', '2captcha');
        $timeout = config('form-monitor.captcha.timeout', 120);

        // Display configuration
        $this->info('Configuration:');
        $this->line("  Provider: {$provider}");
        
        if (empty($apiKey)) {
            $this->error('  API Key: NOT SET');
            $this->warn('  Status: ❌ CAPTCHA solving is DISABLED');
            $this->line('');
            $this->info('To enable CAPTCHA solving:');
            $this->line('  1. Get an API key from https://2captcha.com or https://anti-captcha.com');
            $this->line('  2. Add to your .env file:');
            $this->line('     CAPTCHA_SOLVER_API_KEY=your_api_key_here');
            $this->line('     CAPTCHA_SOLVER_PROVIDER=2captcha');
            return 1;
        }

        $apiKeyPreview = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
        $this->line("  API Key: {$apiKeyPreview}");
        $this->line("  Timeout: {$timeout} seconds");
        $this->line('');
        
        // Verify API key format
        $this->info('API Key Validation:');
        if ($provider === '2captcha') {
            // 2captcha API keys are typically 32 characters
            if (strlen($apiKey) >= 32) {
                $this->info('  ✅ API key length looks correct (32+ characters)');
            } else {
                $this->warn('  ⚠️  API key seems short (expected 32+ characters)');
            }
        } elseif ($provider === 'anticaptcha') {
            // AntiCaptcha API keys are typically 32 characters
            if (strlen($apiKey) >= 32) {
                $this->info('  ✅ API key length looks correct (32+ characters)');
            } else {
                $this->warn('  ⚠️  API key seems short (expected 32+ characters)');
            }
        }

        $this->line('');
        
        // Test API connectivity (optional)
        $this->info('Testing API Connectivity:');
        $this->line('  Note: This will make a test API call to verify connectivity');
        
        if (!$this->confirm('Do you want to test API connectivity?', false)) {
            $this->info('  Skipped');
            $this->line('');
            $this->info('✅ Configuration looks good!');
            $this->line('   CAPTCHA solving will be enabled when forms are checked.');
            return 0;
        }

        // Test API call
        try {
            if ($provider === '2captcha') {
                $this->test2Captcha($apiKey);
            } elseif ($provider === 'anticaptcha') {
                $this->testAntiCaptcha($apiKey);
            } else {
                $this->error("  Unknown provider: {$provider}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("  ❌ API test failed: " . $e->getMessage());
            $this->line('');
            $this->warn('⚠️  Configuration may be incorrect. Please verify:');
            $this->line('  1. API key is correct');
            $this->line('  2. You have credits in your account');
            $this->line('  3. Network connectivity is working');
            return 1;
        }

        $this->line('');
        $this->info('✅ Configuration verified successfully!');
        $this->line('   CAPTCHA solving is ENABLED and ready to use.');
        
        return 0;
    }

    private function test2Captcha(string $apiKey): void
    {
        $this->line('  Testing 2captcha API...');
        
        $client = new \GuzzleHttp\Client(['timeout' => 10]);
        $response = $client->get('https://2captcha.com/res.php', [
            'query' => [
                'key' => $apiKey,
                'action' => 'getbalance',
                'json' => 1,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        
        if (isset($data['status']) && $data['status'] === 1) {
            $balance = $data['request'] ?? 'unknown';
            $this->info("  ✅ API connection successful!");
            $this->line("  Balance: \${$balance}");
        } elseif (isset($data['request'])) {
            $this->error("  ❌ API error: {$data['request']}");
            throw new \Exception($data['request']);
        } else {
            throw new \Exception('Invalid API response');
        }
    }

    private function testAntiCaptcha(string $apiKey): void
    {
        $this->line('  Testing AntiCaptcha API...');
        
        $client = new \GuzzleHttp\Client(['timeout' => 10]);
        $response = $client->post('https://api.anti-captcha.com/getBalance', [
            'json' => [
                'clientKey' => $apiKey,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        
        if (isset($data['errorId']) && $data['errorId'] === 0) {
            $balance = $data['balance'] ?? 'unknown';
            $this->info("  ✅ API connection successful!");
            $this->line("  Balance: \${$balance}");
        } elseif (isset($data['errorDescription'])) {
            $this->error("  ❌ API error: {$data['errorDescription']}");
            throw new \Exception($data['errorDescription']);
        } else {
            throw new \Exception('Invalid API response');
        }
    }
}


