<?php

namespace App\Services;

use App\Models\FormTarget;
use App\Models\CheckRun;
use App\Services\PuppeteerFormCheckService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\FormCheckFailed;

class FormCheckService
{
    private Client $httpClient;
    private array $settings;
    private PuppeteerFormCheckService $puppeteerService;

    public function __construct(PuppeteerFormCheckService $puppeteerService = null)
    {
        $this->httpClient = new Client([
            'timeout' => config('form-monitor.timeouts.http', 30),
            'headers' => [
                'User-Agent' => config('form-monitor.user_agent', 'Form Monitor Bot/1.0'),
            ],
        ]);
        
        $this->settings = config('form-monitor', []);
        $this->puppeteerService = $puppeteerService ?? app(PuppeteerFormCheckService::class);
    }

    public function checkForm(FormTarget $formTarget): CheckRun
    {
        // Determine the driver to use
        $driver = CheckRun::DRIVER_HTTP;
        if ($formTarget->recaptcha_expected && $this->puppeteerService->isAvailable()) {
            $driver = CheckRun::DRIVER_PUPPETEER;
        } elseif ($formTarget->uses_js) {
            $driver = CheckRun::DRIVER_DUSK;
        }

        $checkRun = CheckRun::create([
            'form_target_id' => $formTarget->id,
            'driver' => $driver,
            'status' => 'pending',
            'started_at' => now(),
        ]);

        try {
            // Priority: Explicit driver type > Puppeteer for CAPTCHA > Dusk for JS > HTTP for standard
            if ($formTarget->driver_type === 'puppeteer' && $this->puppeteerService->isAvailable()) {
                Log::info('🎯 Using Puppeteer (explicitly configured)', [
                    'form_target_id' => $formTarget->id,
                    'driver' => 'puppeteer',
                ]);
                $result = $this->checkWithPuppeteer($formTarget);
            } elseif ($formTarget->driver_type === 'dusk' && $this->isChromeDriverAvailable()) {
                Log::info('🎯 Using Dusk (explicitly configured)', [
                    'form_target_id' => $formTarget->id,
                    'driver' => 'dusk',
                ]);
                $result = $this->checkWithDusk($formTarget);
            } elseif ($formTarget->recaptcha_expected && $this->puppeteerService->isAvailable()) {
                Log::info('🎯 Using Puppeteer for CAPTCHA form', [
                    'form_target_id' => $formTarget->id,
                    'driver' => 'puppeteer',
                ]);
                $result = $this->checkWithPuppeteer($formTarget);
            } elseif ($formTarget->uses_js) {
                Log::info('🎯 Using Dusk for JavaScript form', [
                    'form_target_id' => $formTarget->id,
                    'driver' => 'dusk',
                ]);
                $result = $this->checkWithDusk($formTarget);
            } else {
                Log::info('🎯 Using HTTP for standard form', [
                    'form_target_id' => $formTarget->id,
                    'driver' => 'http',
                ]);
                $result = $this->checkWithHttp($formTarget);
            }

            $checkRun->update([
                'status' => $result['status'],
                'http_status' => $result['http_status'] ?? null,
                'final_url' => $result['final_url'] ?? null,
                'message_excerpt' => $result['message_excerpt'] ?? null,
                'error_detail' => $result['error_detail'] ?? null,
                'debug_info' => $result['debug_info'] ?? null,
                'finished_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Form check failed', [
                'form_target_id' => $formTarget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $checkRun->update([
                'status' => CheckRun::STATUS_ERROR,
                'error_detail' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                'finished_at' => now(),
            ]);
        }

        if ($checkRun->status === 'failure' || $checkRun->status === 'error') {
            try {
                $recipient = config('form-monitor.notifications.mail_to') ?? config('mail.from.address');
                if ($recipient) {
                    Mail::to($recipient)->send(new FormCheckFailed($checkRun));
                    Log::info('Failure notification sent', ['run_id' => $checkRun->id, 'to' => $recipient]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send notification email', ['error' => $e->getMessage()]);
            }
        }

        return $checkRun;
    }

    private function checkWithPuppeteer(FormTarget $formTarget): array
    {
        Log::info('🚀 Starting Puppeteer form check', [
            'form_target_id' => $formTarget->id,
            'url' => $formTarget->target->url,
        ]);

        try {
            $result = $this->puppeteerService->checkForm($formTarget);
            
            Log::info('✅ Puppeteer form check completed successfully', [
                'form_target_id' => $formTarget->id,
                'status' => $result['status'] ?? 'unknown',
                'final_url' => $result['final_url'] ?? null,
            ]);
            
            // Return result data - CheckRun will be updated by the calling method
            return [
                'status' => $result['status'] ?? CheckRun::STATUS_ERROR,
                'http_status' => null,
                'final_url' => $result['final_url'] ?? null,
                'message_excerpt' => $result['message_excerpt'] ?? null,
                'error_detail' => $result['error_detail'] ?? null,
                'html' => $result['html'] ?? null,
                'debug_info' => $result['debug_info'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('❌ Puppeteer form check failed', [
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
        }
    }

    private function checkWithHttp(FormTarget $formTarget): array
    {
        $url = $formTarget->target->url;
        
        // For HTTP method, we don't handle CAPTCHAs - they should use Puppeteer
        if ($this->hasRecaptcha($url)) {
            Log::warning('⚠️ CAPTCHA detected in HTTP form check - should use Puppeteer instead', [
                'form_target_id' => $formTarget->id,
                'url' => $url,
            ]);
            
            return [
                'status' => CheckRun::STATUS_BLOCKED,
                'error_detail' => ['reason' => 'captcha_detected_but_http_method_used'],
            ];
        }

        // Get the page
        $response = $this->httpClient->get($url);
        $initialSnapshot = $this->snapshotHttpResponse($response);
        $html = $response->getBody()->getContents();
        $finalUrl = $url; // Use original URL for now
        
        // Parse the form
        $crawler = new Crawler($html);
        $form = $this->findForm($crawler, $formTarget);
        
        if (!$form) {
            return [
                'status' => CheckRun::STATUS_ERROR,
                'error_detail' => ['reason' => 'form_not_found'],
            ];
        }

        // Collect form data
        $formData = $this->collectFormData($form, $formTarget);
        
        // Submit the form
        $submitResponse = $this->submitForm($form, $formData, $formTarget);
        $submitSnapshot = $this->snapshotHttpResponse($submitResponse);
        $submitHtml = $submitResponse->getBody()->getContents();
        $submitFinalUrl = $url; // Use original URL for now
        
        // Classify the result
        $status = $this->classifyResponse($submitResponse, $submitHtml, $formTarget);
        
        return [
            'status' => $status,
            'http_status' => $submitResponse->getStatusCode(),
            'final_url' => $submitFinalUrl,
            'message_excerpt' => $this->extractMessage($submitHtml, $formTarget),
            'html' => $submitHtml,
            'debug_info' => [
                'driver' => 'http',
                'initial_response' => $initialSnapshot,
                'submit_response' => $submitSnapshot,
                'status' => $status,
                'form_selector' => $formTarget->selector_value,
            ],
        ];
    }

    private function checkWithDusk(FormTarget $formTarget): array
    {
        // For now, always fallback to HTTP method to avoid ChromeDriver issues
        Log::info('Using HTTP fallback instead of Dusk for form target', [
            'form_target_id' => $formTarget->id,
        ]);
        
        return $this->checkWithHttp($formTarget);
    }

    private function isChromeDriverAvailable(): bool
    {
        // For now, always return false to avoid ChromeDriver issues
        // This will force all forms to use HTTP method
        return false;
    }

    private function hasRecaptcha(string $url): bool
    {
        try {
            $response = $this->httpClient->get($url);
            $html = $response->getBody()->getContents();
            
            return str_contains($html, 'g-recaptcha') || 
                   str_contains($html, 'recaptcha') ||
                   str_contains($html, 'data-sitekey');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function hasRecaptchaInDusk(Browser $browser): bool
    {
        try {
            return $browser->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.g-recaptcha')) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function findForm(Crawler $crawler, FormTarget $formTarget): ?Crawler
    {
        $selector = $formTarget->selector_value;
        
        switch ($formTarget->selector_type) {
            case 'id':
                $form = $crawler->filter("#{$selector}")->closest('form');
                break;
            case 'class':
                $form = $crawler->filter(".{$selector}")->closest('form');
                break;
            case 'css':
                $form = $crawler->filter($selector)->closest('form');
                break;
            default:
                return null;
        }
        
        return $form->count() > 0 ? $form : null;
    }

    private function collectFormData(Crawler $form, FormTarget $formTarget): array
    {
        $data = [];
        
        // Collect all form inputs
        $form->filter('input, select, textarea')->each(function (Crawler $input) use (&$data) {
            $name = $input->attr('name');
            $value = $input->attr('value') ?: '';
            
            if ($name) {
                $data[$name] = $value;
            }
        });
        
        // Override with field mappings
        foreach ($formTarget->fieldMappings as $mapping) {
            $data[$mapping->name] = $mapping->value;
        }
        
        return $data;
    }

    private function submitForm(Crawler $form, array $data, FormTarget $formTarget): \Psr\Http\Message\ResponseInterface
    {
        $method = $formTarget->method_override ?: $form->attr('method') ?: 'POST';
        $action = $formTarget->action_override ?: $form->attr('action') ?: $formTarget->target->url;
        
        $options = [
            'form_params' => $data,
            'allow_redirects' => true,
        ];
        
        if (strtoupper($method) === 'GET') {
            $options = ['query' => $data];
        }
        
        return $this->httpClient->request($method, $action, $options);
    }

    private function classifyResponse(\Psr\Http\Message\ResponseInterface $response, string $html, FormTarget $formTarget): string
    {
        $statusCode = $response->getStatusCode();
        
        // Check for HTTP errors
        if ($statusCode >= 400) {
            if (in_array($statusCode, [403, 429])) {
                return CheckRun::STATUS_BLOCKED;
            }
            return CheckRun::STATUS_ERROR;
        }
        
        // Check for success/error indicators
        if ($formTarget->success_selector && $this->elementExists($html, $formTarget->success_selector)) {
            return CheckRun::STATUS_SUCCESS;
        }
        
        if ($formTarget->error_selector && $this->elementExists($html, $formTarget->error_selector)) {
            return CheckRun::STATUS_FAILURE;
        }
        
        // Default classification based on status code
        return $statusCode >= 200 && $statusCode < 300 ? CheckRun::STATUS_SUCCESS : CheckRun::STATUS_FAILURE;
    }

    private function classifyDuskResponse(Browser $browser, string $html, FormTarget $formTarget): string
    {
        // Check for success/error indicators
        if ($formTarget->success_selector) {
            try {
                $browser->waitFor($formTarget->success_selector, 5);
                return CheckRun::STATUS_SUCCESS;
            } catch (\Exception $e) {
                // Success selector not found
            }
        }
        
        if ($formTarget->error_selector) {
            try {
                $browser->waitFor($formTarget->error_selector, 5);
                return CheckRun::STATUS_FAILURE;
            } catch (\Exception $e) {
                // Error selector not found
            }
        }
        
        // Default to success if no indicators found
        return CheckRun::STATUS_SUCCESS;
    }

    private function elementExists(string $html, string $selector): bool
    {
        try {
            $crawler = new Crawler($html);
            return $crawler->filter($selector)->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function extractMessage(string $html, FormTarget $formTarget): ?string
    {
        if ($formTarget->success_selector) {
            try {
                $crawler = new Crawler($html);
                $element = $crawler->filter($formTarget->success_selector)->first();
                return $element->text();
            } catch (\Exception $e) {
                // Ignore errors
            }
        }
        
        // Fallback: extract common success/error messages
        $crawler = new Crawler($html);
        $messages = $crawler->filter('.message, .alert, .notification, .success, .error')->each(function (Crawler $node) {
            return trim($node->text());
        });
        
        return !empty($messages) ? implode('; ', $messages) : null;
    }

    private function fillFormFields(Browser $browser, FormTarget $formTarget): void
    {
        foreach ($formTarget->fieldMappings as $mapping) {
            try {
                $browser->type($mapping->name, $mapping->value);
            } catch (\Exception $e) {
                // Field not found, continue
            }
        }
    }

    private function takeScreenshot(Browser $browser, FormTarget $formTarget): string
    {
        $filename = 'screenshots/' . uniqid() . '_' . $formTarget->id . '.png';
        $browser->screenshot($filename);
        return $filename;
    }


    private function snapshotHttpResponse(\Psr\Http\Message\ResponseInterface $response): array
    {
        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', array_slice($values, 0, 3));
            if (count($headers) >= 25) {
                break;
            }
        }

        return [
            'status' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
            'headers' => $headers,
        ];
    }
}
