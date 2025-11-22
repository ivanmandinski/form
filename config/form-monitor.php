<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Form Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the form monitoring system.
    |
    */

    'user_agent' => env('FORM_MONITOR_USER_AGENT', 'Form Monitor Bot/1.0'),
    
    'timeouts' => [
        'http' => env('FORM_MONITOR_HTTP_TIMEOUT', 30),
        'dusk' => env('FORM_MONITOR_DUSK_TIMEOUT', 60),
        'puppeteer' => env('FORM_MONITOR_PUPPETEER_TIMEOUT', 120),
    ],
    
    'concurrency' => [
        'max_per_host' => env('FORM_MONITOR_MAX_CONCURRENT_PER_HOST', 2),
        'global_max' => env('FORM_MONITOR_GLOBAL_MAX_CONCURRENT', 10),
    ],
    
    'politeness' => [
        'delay_between_requests' => env('FORM_MONITOR_DELAY_BETWEEN_REQUESTS', 1),
        'delay_per_host' => env('FORM_MONITOR_DELAY_PER_HOST', 2),
    ],
    
    'artifacts' => [
        'retention_days' => env('FORM_MONITOR_ARTIFACT_RETENTION_DAYS', 30),
        'max_html_size' => env('FORM_MONITOR_MAX_HTML_SIZE', 1024 * 1024), // 1MB
        'screenshot_quality' => env('FORM_MONITOR_SCREENSHOT_QUALITY', 80),
    ],
    
    'logging' => [
        'log_all_requests' => env('FORM_MONITOR_LOG_ALL_REQUESTS', true),
        'log_artifacts' => env('FORM_MONITOR_LOG_ARTIFACTS', false),
        'redact_pii' => env('FORM_MONITOR_REDACT_PII', true),
    ],

    'notifications' => [
        'mail_to' => env('FORM_MONITOR_NOTIFICATIONS_MAIL_TO', null),
    ],

    'validation' => [
        'success_selectors' => [
            '.wpcf7-response-output.wpcf7-mail-sent-ok',
            '.cf7-success',
        ],
        'error_selectors' => [
            '.wpcf7-validation-errors',
            '.cf7-error',
            '.form-error',
        ],
        'success_phrases' => [
            'your message has been sent',
            'we will get back to you',
        ],
        'error_phrases' => [
            'please fix the errors',
            'invalid captcha',
            'verification failed',
        ],
        'url_change_keywords' => [
            '#thank',
            'thank-you',
            'success',
        ],
    ],
    
    'scheduling' => [
        'check_interval' => env('FORM_MONITOR_CHECK_INTERVAL', 60), // seconds
        'max_runtime' => env('FORM_MONITOR_MAX_RUNTIME', 300), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Puppeteer Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Puppeteer-based form checking with CAPTCHA support.
    |
    */

    'puppeteer' => [
        'headless' => env('PUPPETEER_HEADLESS', true),
        'debug' => env('PUPPETEER_DEBUG', false),
        'user_data_dir' => env('PUPPETEER_USER_DATA_DIR', null),
        'args' => [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu',
            '--disable-web-security',
            '--disable-features=VizDisplayCompositor',
            '--disable-background-timer-throttling',
            '--disable-backgrounding-occluded-windows',
            '--disable-renderer-backgrounding',
            '--disable-field-trial-config',
            '--disable-back-forward-cache',
            '--disable-ipc-flooding-protection',
            '--enable-features=NetworkService,NetworkServiceLogging',
            '--force-color-profile=srgb',
            '--metrics-recording-only',
            '--use-mock-keychain',
            '--disable-extensions',
            '--disable-plugins',
            '--disable-default-apps',
            '--disable-sync',
            '--disable-translate',
            '--hide-scrollbars',
            '--mute-audio',
            '--no-default-browser-check',
            '--no-pings',
            '--password-store=basic',
            '--disable-component-extensions-with-background-pages',
            '--disable-background-networking',
            '--disable-client-side-phishing-detection',
            '--disable-hang-monitor',
            '--disable-popup-blocking',
            '--disable-prompt-on-repost',
            '--disable-domain-reliability',
            '--disable-features=TranslateUI',
        ],
        'viewport' => [
            'width' => 1366,
            'height' => 768,
            'deviceScaleFactor' => 1,
            'hasTouch' => false,
            'isLandscape' => true,
            'isMobile' => false,
        ],
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'extra_http_headers' => [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate, br',
            'DNT' => '1',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Solver Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CAPTCHA solving services.
    |
    */

    'captcha' => [
        'api_key' => env('CAPTCHA_SOLVER_API_KEY', ''),
        'provider' => env('CAPTCHA_SOLVER_PROVIDER', '2captcha'), // 2captcha, anticaptcha, etc.
        'timeout' => env('CAPTCHA_SOLVER_TIMEOUT', 120), // seconds
        'retry_attempts' => env('CAPTCHA_SOLVER_RETRY_ATTEMPTS', 3),
        'solve_score_based' => env('CAPTCHA_SOLVE_SCORE_BASED', true),
        'solve_inactive_challenges' => env('CAPTCHA_SOLVE_INACTIVE_CHALLENGES', true),
        'solve_recaptcha_v3' => env('CAPTCHA_SOLVE_RECAPTCHA_V3', true),
        'solve_recaptcha_v2' => env('CAPTCHA_SOLVE_RECAPTCHA_V2', true),
        'solve_hcaptcha' => env('CAPTCHA_SOLVE_HCAPTCHA', true),
        'visual_feedback' => env('CAPTCHA_VISUAL_FEEDBACK', true),
    ],
];
