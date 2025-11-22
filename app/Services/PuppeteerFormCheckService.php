<?php

namespace App\Services;

use App\Models\FormTarget;
use App\Models\CheckRun;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PuppeteerFormCheckService
{
    private string $puppeteerScript;
    private int $timeout;

    public function __construct()
    {
        $this->puppeteerScript = resource_path('js/puppeteer-form-checker.js');
        $this->timeout = config('form-monitor.timeouts.puppeteer', 120); // 2 minutes
    }

    /**
     * Run Puppeteer form check and return result data (not a CheckRun).
     * The CheckRun should be created and managed by the calling service.
     */
    public function checkForm(FormTarget $formTarget): array
    {
        // Set PHP execution time limit for this process
        $originalTimeLimit = ini_get('max_execution_time');
        $timeout = config('form-monitor.timeouts.puppeteer', 300);
        set_time_limit($timeout);

        try {
            $result = $this->runPuppeteerCheck($formTarget);
            
            Log::info('Processing Puppeteer result', [
                'form_target_id' => $formTarget->id,
                'success' => $result['success'] ?? 'undefined',
                'status' => $result['status'] ?? 'undefined',
                'finalUrl' => $result['finalUrl'] ?? 'undefined',
                'has_html' => isset($result['html']),
                'result_keys' => array_keys($result),
            ]);

            $errorDetail = null;
            if (!($result['success'] ?? false)) {
                $errorDetail = [
                    'error' => $result['error'] ?? null,
                    'message' => $result['message'] ?? null,
                    'status' => $result['status'] ?? null,
                ];

                if (empty($errorDetail['error'])) {
                    $errorDetail['error'] = $errorDetail['message'] ?? 'Unknown error';
                }

                $classificationReason = data_get($result, 'debugInfo.classification.reason');
                $validationReason = data_get($result, 'debugInfo.validation.reason');

                if ($classificationReason) {
                    $errorDetail['reason'] = $classificationReason;
                } elseif ($validationReason) {
                    $errorDetail['reason'] = $validationReason;
                }

                if (array_key_exists('captchaDetected', $result)) {
                    $errorDetail['captcha_detected'] = (bool) $result['captchaDetected'];
                }

                if (data_get($result, 'debugInfo.captchaBlocking') !== null) {
                    $errorDetail['captcha_blocking'] = data_get($result, 'debugInfo.captchaBlocking');
                }
            }

            // Return result data in format expected by FormCheckService
            return [
                'status' => $this->mapStatus($result['status'] ?? 'unknown'),
                'final_url' => $result['finalUrl'] ?? null,
                'message_excerpt' => $result['message'] ?? null,
                'error_detail' => $errorDetail,
                'html' => $result['html'] ?? null,
                'debug_info' => $result['debugInfo'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Puppeteer form check failed', [
                'form_target_id' => $formTarget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => CheckRun::STATUS_ERROR,
                'error_detail' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ];
        } finally {
            // Restore original PHP execution time limit
            if (isset($originalTimeLimit)) {
                set_time_limit($originalTimeLimit);
            }
        }
    }

    private function runPuppeteerCheck(FormTarget $formTarget): array
    {
        // Prepare configuration for Puppeteer script
        $config = [
            'url' => $formTarget->target->url,
            'selectorType' => $formTarget->selector_type,
            'selectorValue' => $formTarget->selector_value,
            'fieldMappings' => $formTarget->fieldMappings->map(function ($mapping) {
                return [
                    'selector' => $mapping->selector,
                    'value' => $mapping->value,
                    'type' => $mapping->type ?? 'text',
                    'clearFirst' => $mapping->clear_first ?? true,
                    'delay' => $mapping->delay ?? 100,
                ];
            })->toArray(),
            'successSelector' => $formTarget->success_selector,
            'errorSelector' => $formTarget->error_selector,
            'timeout' => $this->timeout * 1000, // Convert to milliseconds
            'waitForJavaScript' => $formTarget->uses_js ?? true,
            'executeJavaScript' => $formTarget->execute_javascript ?? null,
            'waitForElements' => $formTarget->wait_for_elements ?? [],
            'customActions' => $formTarget->custom_actions ?? [],
            'captchaExpected' => true,
            'validationRules' => config('form-monitor.validation', []),
        ];

        $configJson = json_encode($config);

        // Create the process
        $process = new Process([
            'node',
            $this->puppeteerScript,
            $configJson
        ]);

        $process->setTimeout($this->timeout);
        $process->setWorkingDirectory(base_path());

        // Set environment variables
        $env = $_ENV;
        $env['PUPPETEER_HEADLESS'] = config('form-monitor.puppeteer.headless', 'true');
        $env['CAPTCHA_SOLVER_API_KEY'] = config('form-monitor.captcha.api_key', '');
        $env['PUPPETEER_EXECUTABLE_PATH'] = env('PUPPETEER_EXECUTABLE_PATH', $env['PUPPETEER_EXECUTABLE_PATH'] ?? '');
        $env['PUPPETEER_PRODUCT'] = env('PUPPETEER_PRODUCT', $env['PUPPETEER_PRODUCT'] ?? 'chrome');

        if (empty($env['PUPPETEER_EXECUTABLE_PATH'])) {
            $detectedBinary = $this->detectChromiumBinary();
            if ($detectedBinary) {
                $env['PUPPETEER_EXECUTABLE_PATH'] = $detectedBinary;
                Log::info('Detected Chromium binary for Puppeteer', [
                    'path' => $detectedBinary,
                ]);
            } else {
                Log::warning('Failed to detect Chromium binary for Puppeteer. Falling back to bundled browser.');
            }
        }
        
        // Ensure LD_LIBRARY_PATH is set for Chromium libraries
        if (empty($env['LD_LIBRARY_PATH'])) {
            // Try to find and set library paths if not already set
            $libPaths = [];
            $libs = [
                'libglib-2.0.so',
                'libnss3.so',
                'libatk-1.0.so',
                'libatspi.so',
                'libdrm.so',
                'libXcomposite.so',
                'libXdamage.so',
                'libXrandr.so',
                'libGL.so',
                'libXss.so',
                'libasound.so',
                'libatk-bridge-2.0.so',
            ];
            
            foreach ($libs as $lib) {
                $output = shell_exec("find /nix/store -name '{$lib}*' -type f 2>/dev/null | head -1 | xargs dirname 2>/dev/null");
                if ($output && trim($output)) {
                    $libPaths[] = trim($output);
                }
            }
            
            if (!empty($libPaths)) {
                $env['LD_LIBRARY_PATH'] = implode(':', array_unique($libPaths));
            }
        }
        
        $process->setEnv($env);

        Log::info('Running Puppeteer form check', [
            'form_target_id' => $formTarget->id,
            'url' => $formTarget->target->url,
        ]);

        try {
            $process->mustRun();
            $output = $process->getOutput();
            
            // Parse the JSON output
            $result = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON output from Puppeteer: ' . $output);
            }

            Log::info('Puppeteer form check completed', [
                'form_target_id' => $formTarget->id,
                'status' => $result['status'] ?? 'unknown',
                'captcha_detected' => $result['captchaDetected'] ?? false,
                'success' => $result['success'] ?? false,
                'final_url' => $result['finalUrl'] ?? null,
                'message' => $result['message'] ?? null,
            ]);

            return $result;

        } catch (ProcessFailedException $e) {
            $errorOutput = $process->getErrorOutput();
            $standardOutput = $process->getOutput();
            
            Log::error('Puppeteer process failed', [
                'form_target_id' => $formTarget->id,
                'error' => $e->getMessage(),
                'stderr' => $errorOutput,
                'stdout' => $standardOutput,
            ]);

            throw new \Exception('Puppeteer process failed: ' . $errorOutput ?: $e->getMessage());
        }
    }

    private function detectChromiumBinary(): ?string
    {
        $candidates = [
            'chromium',
            'chromium-browser',
            'google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
        ];

        foreach ($candidates as $candidate) {
            $command = "command -v {$candidate} 2>/dev/null";
            $path = shell_exec($command);
            if ($path && trim($path)) {
                return trim($path);
            }
        }

        $globCandidates = glob('/nix/store/*-chromium-*/bin/chromium');
        if (!empty($globCandidates)) {
            return $globCandidates[0];
        }

        return null;
    }

    private function mapStatus(string $puppeteerStatus): string
    {
        return match($puppeteerStatus) {
            'success' => CheckRun::STATUS_SUCCESS,
            'failure' => CheckRun::STATUS_FAILURE,
            'blocked' => CheckRun::STATUS_BLOCKED,
            'error' => CheckRun::STATUS_ERROR,
            'unknown' => CheckRun::STATUS_SUCCESS, // Treat unknown as success since form was submitted
            default => CheckRun::STATUS_SUCCESS, // Default to success for any other status
        };
    }

    public function isAvailable(): bool
    {
        try {
            // Check if Node.js is available
            $process = new Process(['node', '--version']);
            $process->run();
            
            if (!$process->isSuccessful()) {
                return false;
            }

            // Check if the Puppeteer script exists
            if (!file_exists($this->puppeteerScript)) {
                return false;
            }

            // Check if Puppeteer is installed
            $process = new Process(['node', '-e', 'require("puppeteer")']);
            $process->run();
            
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::warning('Puppeteer availability check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
