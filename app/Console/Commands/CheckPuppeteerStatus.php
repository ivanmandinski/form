<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PuppeteerFormCheckService;
use Symfony\Component\Process\Process;

class CheckPuppeteerStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'puppeteer:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Puppeteer availability and status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Puppeteer Status...');
        $this->newLine();

        // Check Node.js
        $this->info('📦 Checking Node.js...');
        try {
            $process = new Process(['node', '--version']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $version = trim($process->getOutput());
                $this->info("✅ Node.js: {$version}");
            } else {
                $this->error('❌ Node.js not found or not working');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Node.js check failed: ' . $e->getMessage());
            return 1;
        }

        // Check Puppeteer script
        $this->newLine();
        $this->info('📜 Checking Puppeteer script...');
        $scriptPath = resource_path('js/puppeteer-form-checker.js');
        
        if (file_exists($scriptPath)) {
            $this->info("✅ Script found: {$scriptPath}");
            
            if (is_executable($scriptPath)) {
                $this->info('✅ Script is executable');
            } else {
                $this->warn('⚠️ Script is not executable (run: chmod +x ' . $scriptPath . ')');
            }
        } else {
            $this->error('❌ Script not found: ' . $scriptPath);
            return 1;
        }

        // Check Puppeteer package
        $this->newLine();
        $this->info('📚 Checking Puppeteer package...');
        try {
            $process = new Process(['node', '-e', 'require("puppeteer")']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $this->info('✅ Puppeteer package is available');
                
                // Get version
                $process = new Process(['node', '-e', 'console.log(require("puppeteer").version)']);
                $process->run();
                if ($process->isSuccessful()) {
                    $version = trim($process->getOutput());
                    $this->info("✅ Puppeteer version: {$version}");
                }
            } else {
                $this->error('❌ Puppeteer package not found');
                $this->warn('Run: npm install puppeteer puppeteer-extra puppeteer-extra-plugin-stealth puppeteer-extra-plugin-recaptcha');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Puppeteer package check failed: ' . $e->getMessage());
            return 1;
        }

        // Check CAPTCHA solver configuration
        $this->newLine();
        $this->info('🔐 Checking CAPTCHA solver configuration...');
        $apiKey = config('form-monitor.captcha.api_key');
        $provider = config('form-monitor.captcha.provider');
        
        if ($apiKey && $apiKey !== '') {
            $this->info("✅ CAPTCHA API key configured for {$provider}");
            $this->info("✅ API key: " . substr($apiKey, 0, 8) . '...');
        } else {
            $this->warn('⚠️ CAPTCHA API key not configured');
            $this->warn('Add to .env: CAPTCHA_SOLVER_API_KEY=your_api_key_here');
        }

        // Check Puppeteer service
        $this->newLine();
        $this->info('🔧 Checking Puppeteer service...');
        try {
            $puppeteerService = app(PuppeteerFormCheckService::class);
            $isAvailable = $puppeteerService->isAvailable();
            
            if ($isAvailable) {
                $this->info('✅ Puppeteer service is available and working');
            } else {
                $this->error('❌ Puppeteer service is not available');
            }
        } catch (\Exception $e) {
            $this->error('❌ Puppeteer service check failed: ' . $e->getMessage());
        }

        // Test script execution
        $this->newLine();
        $this->info('🧪 Testing script execution...');
        try {
            $testConfig = json_encode([
                'url' => 'https://httpbin.org/get',
                'selectorType' => 'css',
                'selectorValue' => 'body',
                'fieldMappings' => [],
                'timeout' => 5000
            ]);
            
            $process = new Process(['node', $scriptPath, $testConfig]);
            $process->setTimeout(10);
            $process->run();
            
            if ($process->isSuccessful()) {
                $this->info('✅ Script execution test passed');
            } else {
                $this->warn('⚠️ Script execution test failed');
                $this->warn('Error: ' . $process->getOutput());
            }
        } catch (\Exception $e) {
            $this->warn('⚠️ Script execution test failed: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎯 Puppeteer Status Check Complete!');
        
        return 0;
    }
}
