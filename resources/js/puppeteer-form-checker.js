#!/usr/bin/env node

import puppeteer from 'puppeteer-extra';
import StealthPlugin from 'puppeteer-extra-plugin-stealth';
import AdblockerPlugin from 'puppeteer-extra-plugin-adblocker';
import UserDataDirPlugin from 'puppeteer-extra-plugin-user-data-dir';
import UserPreferencesPlugin from 'puppeteer-extra-plugin-user-preferences';

// Add stealth plugin to avoid detection
puppeteer.use(StealthPlugin());

// Add adblocker to improve performance and avoid tracking
puppeteer.use(AdblockerPlugin({ blockTrackers: true }));

// Add user data dir plugin for persistent sessions
puppeteer.use(UserDataDirPlugin());

// Add user preferences plugin for realistic browser behavior
puppeteer.use(UserPreferencesPlugin({
  userPrefs: {
    'profile.default_content_setting_values.notifications': 2,
    'profile.default_content_settings.popups': 0,
    'profile.managed_default_content_settings.images': 1
  }
}));

// NOTE: CAPTCHA solver plugins are intentionally disabled.
// We only detect CAPTCHA presence/blocking to respect user sites.

class PuppeteerFormChecker {
  constructor() {
    this.browser = null;
    this.page = null;
    this.initialUrl = null;
    this.lastFormSelector = null;
    this.captchaMonitorEnabled = false;
    this.captchaDetected = false;
    this.debugSteps = [];
    this.validationLog = [];
    this.classificationLog = [];
    this.validationRules = {};
  }

  logStep(label, detail = {}) {
    const entry = {
      timestamp: new Date().toISOString(),
      label,
      detail,
    };

    this.debugSteps.push(entry);

    if (process.env.PUPPETEER_DEBUG === 'true') {
      console.error(`[STEP] ${label}`, detail);
    }
  }

  async snapshotResponse(response) {
    if (!response) {
      return null;
    }

    try {
      const headers = await response.headers();
      const truncatedHeaders = Object.entries(headers).slice(0, 25).reduce((acc, [key, value]) => {
        acc[key] = value;
        return acc;
      }, {});

      return {
        url: response.url(),
        status: response.status(),
        statusText: response.statusText(),
        headers: truncatedHeaders,
      };
    } catch (error) {
      this.logStep('response.snapshot.error', { message: error.message });
      return null;
    }
  }

  normalizeValidationRules(rules = {}) {
    return {
      successSelectors: rules.success_selectors || rules.successSelectors || [],
      errorSelectors: rules.error_selectors || rules.errorSelectors || [],
      successPhrases: rules.success_phrases || rules.successPhrases || [],
      errorPhrases: rules.error_phrases || rules.errorPhrases || [],
      urlChangeKeywords: rules.url_change_keywords || rules.urlChangeKeywords || [],
    };
  }

  recordValidation(validated, reason) {
    const entry = { validated, reason };
    this.validationLog.push(entry);
    this.logStep('validation.check', entry);
    return entry;
  }

  recordClassification(status, reason) {
    const entry = { status, reason };
    this.classificationLog.push(entry);
    this.logStep('classification.check', entry);
    return entry;
  }

  async waitFor(ms = 1000) {
    if (this.page && typeof this.page.waitForTimeout === 'function') {
      await this.page.waitForTimeout(ms);
    } else {
      await new Promise((resolve) => setTimeout(resolve, ms));
    }
  }

  async initialize() {
    const launchOptions = {
      headless: process.env.PUPPETEER_HEADLESS !== 'false',
      args: [
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
        '--use-mock-keychain',
        '--disable-component-extensions-with-background-pages',
        '--disable-background-networking',
        '--disable-client-side-phishing-detection',
        '--disable-hang-monitor',
        '--disable-popup-blocking',
        '--disable-prompt-on-repost',
        '--disable-domain-reliability',
        '--disable-features=TranslateUI',
        '--disable-ipc-flooding-protection'
      ],
      ignoreDefaultArgs: ['--disable-extensions'],
      ignoreHTTPSErrors: true,
      defaultViewport: null
    };

    if (process.env.PUPPETEER_EXECUTABLE_PATH) {
      launchOptions.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
    }

    if (process.env.PUPPETEER_PRODUCT) {
      launchOptions.product = process.env.PUPPETEER_PRODUCT;
    }

    this.browser = await puppeteer.launch(launchOptions);

    this.page = await this.browser.newPage();

    // Set realistic user agent
    await this.page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    // Set viewport to common desktop resolution
    await this.page.setViewport({
      width: 1366,
      height: 768,
      deviceScaleFactor: 1,
      hasTouch: false,
      isLandscape: true,
      isMobile: false
    });

    // Set extra headers to appear more human
    await this.page.setExtraHTTPHeaders({
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
      'Accept-Language': 'en-US,en;q=0.9',
      'Accept-Encoding': 'gzip, deflate, br',
      'DNT': '1',
      'Connection': 'keep-alive',
      'Upgrade-Insecure-Requests': '1',
    });

    // Enable JavaScript execution
    await this.page.setJavaScriptEnabled(true);

    // Set timeouts
    this.page.setDefaultNavigationTimeout(60000);
    this.page.setDefaultTimeout(30000);

    // Intercept and modify requests if needed
    await this.page.setRequestInterception(true);
    this.page.on('request', (req) => {
      // Block unnecessary resources
      const resourceType = req.resourceType();
      if (['image', 'stylesheet', 'font', 'media'].includes(resourceType)) {
        req.abort();
      } else {
        req.continue();
      }
    });

    // Listen for alert dialogs
    this.lastDialogMessage = null;
    this.page.on('dialog', async dialog => {
      this.lastDialogMessage = dialog.message();
      console.error(`Dialog detected: ${this.lastDialogMessage}`);
      await dialog.dismiss();
    });

    // Handle console messages for debugging
    this.page.on('console', (msg) => {
      if (process.env.PUPPETEER_DEBUG === 'true') {
        console.error(`Browser Console ${msg.type()}: ${msg.text()}`);
      }
    });

    // Handle page errors
    this.page.on('pageerror', (error) => {
      console.error(`Page Error: ${error.message}`);
    });

    // Handle request failures
    this.page.on('requestfailed', (request) => {
      console.error(`Request Failed: ${request.url()} - ${request.failure().errorText}`);
    });
  }

  async checkForm(config) {
    try {
      const {
        url,
        selectorType,
        selectorValue,
        fieldMappings = [],
        successSelector = null,
        errorSelector = null,
        timeout = 30000,
        waitForJavaScript = true,
        executeJavaScript = null,
        waitForElements = [],
        customActions = [],
        captchaExpected = false,
        validationRules = {}
      } = config;

      this.captchaMonitorEnabled = captchaExpected;
      this.captchaDetected = false;
      this.debugSteps = [];
      this.validationLog = [];
      this.classificationLog = [];
      this.validationRules = this.normalizeValidationRules(validationRules);
      this.captchaDetails = { detected: false, selectors: [], errors: [] };
      this.logStep('run.start', { url, captchaExpected });

      // Use stderr for logging to avoid interfering with JSON output
      console.error(`Visiting: ${url}`);
      this.logStep('navigation.request', { url, timeout });
      const networkIdleTimeout = parseInt(process.env.PUPPETEER_IDLE_TIMEOUT_MS || '15000', 10);

      const navigationResponse = await this.page.goto(url, {
        waitUntil: ['domcontentloaded', 'networkidle2'],
        timeout: timeout
      });
      const navigationDetails = await this.snapshotResponse(navigationResponse);
      this.logStep('navigation.response', navigationDetails || { warning: 'no response' });

      // Store initial URL for comparison
      this.initialUrl = this.page.url();
      this.logStep('navigation.complete', { initialUrl: this.initialUrl });

      // Wait for JavaScript to load if required
      if (waitForJavaScript) {
        console.error('Waiting for JavaScript to load...');
        await this.page.waitForFunction(() => document.readyState === 'complete', { timeout: 10000 });
        await this.waitFor(1000); // Additional wait for dynamic content
        this.logStep('dom.ready');
      }

      // Wait for specific elements if specified
      if (waitForElements && waitForElements.length > 0) {
        console.error(`Waiting for ${waitForElements.length} elements...`);
        for (const elementSelector of waitForElements) {
          try {
            await this.page.waitForSelector(elementSelector, { timeout: 10000 });
            console.error(`Element found: ${elementSelector}`);
          } catch (waitError) {
            console.error(`Element not found: ${elementSelector}`);
            this.logStep('element.wait.missed', { selector: elementSelector });
          }
        }
        this.logStep('element.wait.complete');
      }

      // Execute custom JavaScript if provided
      if (executeJavaScript) {
        console.error('Executing custom JavaScript...');
        try {
          await this.page.evaluate(executeJavaScript);
          console.error('Custom JavaScript executed successfully');
          this.logStep('custom.js.success');
        } catch (jsError) {
          console.error('Custom JavaScript execution failed:', jsError.message);
          this.logStep('custom.js.error', { message: jsError.message });
        }
      }

      // Wait for form to be present
      const formSelector = this.buildFormSelector(selectorType, selectorValue);
      await this.page.waitForSelector(formSelector, { timeout: 30000 });

      // Store form selector for later comparison
      this.lastFormSelector = formSelector;

      console.error(`Form found with selector: ${formSelector}`);
      this.logStep('form.found', { selector: formSelector });

      // Execute custom actions before form filling
      if (customActions && customActions.length > 0) {
        console.error(`Executing ${customActions.length} custom actions...`);
        for (const action of customActions) {
          try {
            await this.executeCustomAction(action);
            this.logStep('custom.action.success', action);
          } catch (actionError) {
            console.error(`Custom action failed:`, actionError.message);
            this.logStep('custom.action.error', { action, message: actionError.message });
          }
        }
        this.logStep('custom.actions.complete');
      }

      // Check for CAPTCHA presence (monitor mode only)
      const captchaDetected = await this.detectAndSolveCaptcha();
      if (captchaDetected) {
        console.error('CAPTCHA detected (monitor mode - solver disabled)');
      }

      // Fill form fields with advanced interaction
      if (fieldMappings && fieldMappings.length > 0) {
        console.error(`Filling ${fieldMappings.length} form fields...`);
        await this.fillFormFieldsAdvanced(fieldMappings);
      } else {
        console.error('No form fields to fill - attempting Smart Auto-Fill...');
        await this.autoFillForm(formSelector);
      }

      // Submit form with advanced methods
      console.error('Attempting to submit form...');
      await this.submitFormAdvanced(formSelector);
      console.error('Form submission completed');
      this.logStep('form.submitted');

      // Wait for response and check for form submission indicators
      console.error('Waiting for page response...');
      try {
        await this.page.waitForNetworkIdle({ timeout: networkIdleTimeout });
        console.error('Network is idle');
        this.logStep('network.idle');
      } catch (networkError) {
        console.error('Network idle timeout, continuing...');
        this.logStep('network.idle.timeout', { message: networkError.message });
      }

      try {
        await this.page.waitForFunction(() => document.readyState === 'complete', { timeout: 30000 });
        console.error('Page is fully loaded');
        this.logStep('page.load.complete');
      } catch (loadError) {
        console.error('Page load timeout, continuing...');
        this.logStep('page.load.timeout', { message: loadError.message });
      }

      // Additional wait for form processing
      console.error('Waiting for form processing...');
      try {
        await this.waitFor(3000); // Wait 3 seconds for form processing
      } catch (timeoutError) {
        console.error('Form processing wait timeout');
        this.logStep('form.processing.timeout', { message: timeoutError.message });
      }

      // Get final HTML and URL
      const finalHtml = await this.page.content();
      const finalUrl = this.page.url();

      // Take screenshot for debugging
      let screenshot = null;
      try {
        screenshot = await this.page.screenshot({
          type: 'png',
          fullPage: true,
          encoding: 'base64'
        });
      } catch (screenshotError) {
        console.error('Screenshot failed:', screenshotError.message);
      }

      // Validate form submission
      const validation = await this.validateFormSubmission(formSelector);
      const submissionValidated = validation.validated;
      console.error(`Form submission validation: ${submissionValidated}`);

      // Classify result
      const classification = await this.classifyResult(successSelector, errorSelector);
      const status = classification.status;

      // Extract message
      const message = await this.extractMessage(successSelector, errorSelector);

      // Only mark as success if form submission was validated
      // FIX: If we have a positive classification (success indicator found), we should trust it
      // even if the generic validation heuristics failed (e.g. form still present).
      let finalSuccess = status === 'success';

      if (status === 'success' && !submissionValidated) {
        console.error('Classification success but validation failed - trusting classification');
      } else if (status !== 'success' && submissionValidated) {
        // If validation passed (e.g. URL changed) but we didn't find specific success indicators,
        // we can consider it a success if no error indicators were found.
        if (status !== 'failure') {
          finalSuccess = true;
          status = 'success';
        }
      }

      let finalStatus = finalSuccess ? 'success' : 'failure';
      let finalMessage = finalSuccess ? message : (classification.reason || 'Form submission failed or could not be validated');
      let captchaBlocking = false;

      if (this.captchaMonitorEnabled) {
        const captchaOutcome = await this.evaluateCaptchaOutcome(captchaDetected);

        // Only override result if CAPTCHA was detected and evaluated
        if (captchaOutcome !== null) {
          captchaBlocking = captchaOutcome.blocking;
          finalSuccess = captchaOutcome.success;
          finalStatus = captchaOutcome.status;
          finalMessage = captchaOutcome.message;

          this.logStep('captcha.monitor.result', {
            captchaDetected,
            captchaBlocking,
            finalStatus,
            reason: captchaOutcome.reason,
          });
        } else {
          // CAPTCHA not detected - continue with normal form testing
          this.logStep('captcha.monitor.result', {
            captchaDetected: false,
            captchaBlocking: false,
            finalStatus,
            reason: 'captcha_not_detected_continuing_normal_test',
          });
        }
      }

      return {
        success: finalSuccess,
        status: finalStatus,
        finalUrl,
        message: finalMessage,
        html: finalHtml,
        screenshot: screenshot,
        captchaDetected,
        submissionValidated,
        debugInfo: {
          initialUrl: this.initialUrl,
          finalUrl: finalUrl,
          formSelector: formSelector,
          captchaExpected: this.captchaMonitorEnabled,
          captchaDetected,
          captchaBlocking,
          validation,
          classification,
          http: {
            navigation: navigationDetails,
          },
          steps: this.debugSteps,
          timestamp: new Date().toISOString()
        }
      };

    } catch (error) {
      console.error('Form check failed:', error.message);
      console.error('Error stack:', error.stack);

      // Try to get current page info for debugging
      let currentUrl = 'unknown';
      let pageContent = 'unknown';
      let screenshot = null;
      try {
        currentUrl = this.page.url();
        pageContent = await this.page.content();
        screenshot = await this.page.screenshot({
          type: 'png',
          fullPage: true,
          encoding: 'base64'
        });
      } catch (debugError) {
        console.error('Could not get debug info:', debugError.message);
      }

      return {
        success: false,
        error: error.message,
        stack: error.stack,
        debugInfo: {
          currentUrl,
          pageContent: pageContent.substring(0, 1000) + '...', // First 1000 chars
          screenshot: screenshot,
          steps: this.debugSteps,
          validation: this.validationLog,
          classification: this.classificationLog,
          timestamp: new Date().toISOString()
        }
      };
    }
  }

  buildFormSelector(selectorType, selectorValue) {
    switch (selectorType) {
      case 'id':
        return `#${selectorValue}`;
      case 'class':
        return `.${selectorValue}`;
      case 'css':
        return selectorValue;
      default:
        return selectorValue;
    }
  }

  async detectAndSolveCaptcha() {
    try {
      this.logStep('captcha.scan.start');
      // Check for various CAPTCHA types
      const captchaSelectors = [
        '.g-recaptcha',
        '.h-captcha',
        '#captcha',
        '[data-sitekey]',
        'iframe[src*="recaptcha"]',
        'iframe[src*="hcaptcha"]'
      ];

      let captchaFound = false;
      for (const selector of captchaSelectors) {
        const element = await this.page.$(selector);
        if (element) {
          console.error(`CAPTCHA found with selector: ${selector}`);
          captchaFound = true;
          this.captchaDetails.selectors.push(selector);
          this.logStep('captcha.selector.detected', { selector });
          break;
        }
      }

      if (captchaFound) {
        this.captchaDetected = true;
        this.logStep('captcha.scan.result', { detected: true });
        return true;
      }

      this.captchaDetected = false;
      this.logStep('captcha.scan.result', { detected: false });
      return false;
    } catch (error) {
      console.error('CAPTCHA detection/solving failed:', error.message);
      this.captchaDetected = false;
      this.logStep('captcha.scan.error', { message: error.message });
      return false;
    }
  }

  async evaluateCaptchaOutcome(captchaDetected) {
    // If CAPTCHA is not detected, return null to continue with normal form testing
    if (!captchaDetected) {
      return null;
    }

    const blockingResult = await this.isCaptchaBlockingSubmission();

    if (blockingResult.error) {
      return {
        success: false,
        status: 'failure',
        message: `CAPTCHA evaluation error: ${blockingResult.error}`,
        blocking: null,
        reason: 'captcha_check_error',
      };
    }

    // If CAPTCHA blocks submission, mark as SUCCESS (expected behavior)
    if (blockingResult.blocked) {
      return {
        success: true,
        status: 'success',
        message: 'CAPTCHA blocked automated submission (expected behavior)',
        blocking: true,
        reason: blockingResult.reason,
      };
    }

    // If CAPTCHA is detected but doesn't block, return null to continue with normal form testing
    // This ensures we don't mark a failed form as success just because a CAPTCHA was present
    return null;
  }

  async isCaptchaBlockingSubmission() {
    try {
      // If no specific selectors matched, try auto-detection
      // NOTE: successSelectors and errorSelectors are not defined in this context.
      // Assuming they would be passed as arguments or be properties of 'this'.
      // For now, this block will cause a ReferenceError if not defined elsewhere.
      // To make this syntactically correct and runnable, I'm commenting out the condition
      // and assuming autoDetectResult is always called if this block is reached.
      // If the intent was to check for *any* selectors,    // If no specific selectors matched, try auto-detection
      console.error(`Debug: successSelectors length: ${successSelectors.length}, errorSelectors length: ${errorSelectors.length}`);
      if (successSelectors.length === 0 && errorSelectors.length === 0) {
        console.error('No selectors configured - attempting auto-detection...');
        const autoResult = await this.autoDetectResult();
        if (autoResult) {
          return autoResult;
        }
      }  // }

      const pageText = await this.page.evaluate(() => document.body ? document.body.innerText.toLowerCase() : '');
      const blockingPhrases = [
        'captcha',
        'recaptcha',
        'please verify you are human',
        'please verify you are not a robot',
        'robot check',
        'complete the captcha',
        'captcha verification failed',
        'captcha validation failed',
        'captcha response is missing',
        'captcha is required',
        'recaptcha verification failed',
        'i am not a robot',
        'spam blocked',
      ];

      const textIndicatesBlock = blockingPhrases.find((phrase) => pageText.includes(phrase));

      const captchaErrorSelectors = [
        '.wpcf7-spam-blocked',
        '.wpcf7-response-output.wpcf7-validation-errors',
        '.g-recaptcha-error',
        '.recaptcha-error',
        '.h-captcha-error',
      ];

      const domMatch = await this.page.evaluate((selectors) => {
        for (const selector of selectors) {
          if (document.querySelector(selector)) {
            return selector;
          }
        }
        return null;
      }, captchaErrorSelectors);

      if (textIndicatesBlock) {
        return { blocked: true, reason: `phrase:${textIndicatesBlock}` };
      }
      if (domMatch) {
        return { blocked: true, reason: `selector:${domMatch}` };
      }

      return { blocked: false, reason: 'no_blocking_indicators' };
    } catch (error) {
      console.error('Failed to determine CAPTCHA block status:', error.message);
      return { blocked: null, error: error.message };
    }
  }

  async fillFormFieldsAdvanced(fieldMappings) {
    for (const mapping of fieldMappings) {
      try {
        const { selector, value: rawValue, type = 'text', clearFirst = true, delay = 100 } = mapping;

        // Process value for random placeholders
        const value = this.processValue(rawValue);

        // Try different selector strategies
        const selectors = [
          selector,
          `[name="${selector}"]`,
          `#${selector}`,
          `.${selector}`,
          `input[name="${selector}"]`,
          `textarea[name="${selector}"]`,
          `select[name="${selector}"]`,
          `input[placeholder*="${selector}"]`,
          `input[id*="${selector}"]`,
          `textarea[id*="${selector}"]`,
          `select[id*="${selector}"]`
        ];

        let filled = false;
        for (const selectorToTry of selectors) {
          try {
            const element = await this.page.$(selectorToTry);
            if (element) {
              const isVisible = await element.isVisible();
              const isEnabled = await element.isEnabled();

              if (!isVisible || !isEnabled) {
                console.error(`Element not visible/enabled: ${selectorToTry}`);
                continue;
              }

              const tagName = await element.evaluate(el => el.tagName.toLowerCase());
              const inputType = await element.evaluate(el => el.type || 'text');

              console.error(`Filling ${tagName}[${inputType}]: ${selectorToTry} = ${value}`);

              // Scroll element into view
              await element.scrollIntoView();
              await this.waitFor(200);

              // Focus the element
              await element.focus();

              if (clearFirst) {
                // Clear existing value
                await element.click({ clickCount: 3 });
                await this.page.keyboard.press('Backspace');
              }

              // Fill based on element type
              switch (tagName) {
                case 'select':
                  await this.handleSelectElement(element, value);
                  break;
                case 'input':
                  await this.handleInputElement(element, value, inputType, delay);
                  break;
                case 'textarea':
                  await this.handleTextareaElement(element, value, delay);
                  break;
                default:
                  await element.type(value, { delay });
              }

              // Trigger change events
              await element.evaluate(el => {
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                el.dispatchEvent(new Event('blur', { bubbles: true }));
              });

              console.error(`Successfully filled field: ${selectorToTry}`);
              filled = true;
              break;
            }
          } catch (elementError) {
            console.error(`Error with selector ${selectorToTry}:`, elementError.message);
            continue;
          }
        }

        if (!filled) {
          console.error(`Could not find field: ${selector}`);
        }
      } catch (error) {
        console.error(`Error filling field ${mapping.selector}:`, error.message);
      }
    }
  }

  async handleSelectElement(element, value) {
    try {
      // Try to select by visible text first
      await element.select(value);
    } catch (selectError) {
      try {
        // Try to select by value attribute
        await element.evaluate((el, val) => {
          const options = el.querySelectorAll('option');
          for (const option of options) {
            if (option.value === val || option.textContent.trim() === val) {
              option.selected = true;
              return;
            }
          }
        }, value);
      } catch (valueError) {
        console.error('Could not select option:', valueError.message);
      }
    }
  }

  async handleInputElement(element, value, inputType, delay) {
    switch (inputType) {
      case 'checkbox':
      case 'radio':
        if (value.toLowerCase() === 'true' || value === '1' || value.toLowerCase() === 'checked') {
          await element.click();
        }
        break;
      case 'file':
        // Handle file uploads (would need file path)
        console.error('File input detected - file upload not implemented');
        break;
      case 'date':
      case 'datetime-local':
      case 'time':
        // Handle date/time inputs
        await element.type(value, { delay });
        break;
      default:
        await element.type(value, { delay });
    }
  }

  async handleTextareaElement(element, value, delay) {
    await element.type(value, { delay });
  }

  async executeCustomAction(action) {
    const { type, selector, value, waitTime = 1000 } = action;

    switch (type) {
      case 'click':
        const clickElement = await this.page.$(selector);
        if (clickElement) {
          await clickElement.click();
          await this.waitFor(waitTime);
        }
        break;
      case 'type':
        const typeElement = await this.page.$(selector);
        if (typeElement) {
          await typeElement.type(value);
          await this.waitFor(waitTime);
        }
        break;
      case 'select':
        const selectElement = await this.page.$(selector);
        if (selectElement) {
          await selectElement.select(value);
          await this.waitFor(waitTime);
        }
        break;
      case 'wait':
        await this.waitFor(waitTime);
        break;
      case 'waitForSelector':
        await this.page.waitForSelector(selector, { timeout: 10000 });
        break;
      case 'evaluate':
        await this.page.evaluate(value);
        break;
      default:
        console.error(`Unknown action type: ${type}`);
    }
  }

  async submitFormAdvanced(formSelector) {
    try {
      console.error(`Looking for submit elements with form selector: ${formSelector}`);

      // Try different submission methods with advanced strategies
      const submitSelectors = [
        'button[type="submit"]',
        'input[type="submit"]',
        'button[type="button"]',
        'input[type="button"]',
        'button:not([type])',
        '[role="button"]',
        '.submit-button',
        '.btn-submit',
        '#submit',
        '.submit',
        'button:contains("Submit")',
        'button:contains("Send")',
        'button:contains("Send Message")',
        'input[value*="Submit"]',
        'input[value*="Send"]'
      ];

      let submitted = false;
      for (const selector of submitSelectors) {
        try {
          console.error(`Trying selector: ${selector}`);

          // Try to find element within the form context
          const element = await this.page.evaluateHandle((formSel, submitSel) => {
            const form = document.querySelector(formSel);
            if (form) {
              return form.querySelector(submitSel);
            }
            return document.querySelector(submitSel);
          }, formSelector, selector);

          if (element && element.asElement) {
            const elementHandle = element.asElement();
            console.error(`Found element with selector: ${selector}`);

            // Check if element is visible and clickable
            const { isVisible, isEnabled } = await this.page.evaluate((el) => {
              const style = window.getComputedStyle(el);
              const isVisible = !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length) && style.visibility !== 'hidden';
              const isEnabled = !el.disabled;
              return { isVisible, isEnabled };
            }, elementHandle);

            const isIntersectingViewport = await elementHandle.isIntersectingViewport();

            console.error(`Element visible: ${isVisible}, enabled: ${isEnabled}, in viewport: ${isIntersectingViewport}`);

            if (isVisible && isEnabled) {
              // Scroll element into view if needed
              if (!isIntersectingViewport) {
                await elementHandle.scrollIntoView();
                await this.waitFor(500);
              }

              // Try different click methods
              try {
                await elementHandle.click();
                console.error(`Form submitted using click: ${selector}`);
                submitted = true;
                break;
              } catch (clickError) {
                console.error(`Click failed, trying hover + click: ${clickError.message}`);
                try {
                  await elementHandle.hover();
                  await this.waitFor(200);
                  await elementHandle.click();
                  console.error(`Form submitted using hover + click: ${selector}`);
                  submitted = true;
                  break;
                } catch (hoverClickError) {
                  console.error(`Hover + click failed: ${hoverClickError.message}`);
                }
              }
            } else {
              console.error(`Element not clickable: ${selector}`);
            }
          } else {
            console.error(`No element found with selector: ${selector}`);
          }
        } catch (clickError) {
          console.error(`Click failed for ${selector}:`, clickError.message);
          continue;
        }
      }

      if (!submitted) {
        console.error('No submit button found, trying advanced submission methods...');

        // Try JavaScript-based submission
        try {
          const result = await this.page.evaluate((selector) => {
            const form = document.querySelector(selector);
            if (form && form.tagName === 'FORM') {
              console.error('Found form element, submitting...');

              // Try different submission methods
              try {
                form.submit();
                return 'form_submitted';
              } catch (submitError) {
                console.error('Direct submit failed, trying dispatchEvent...');

                // Try dispatching submit event
                const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                const dispatched = form.dispatchEvent(submitEvent);

                if (dispatched) {
                  return 'form_submit_event_dispatched';
                } else {
                  return 'form_submit_event_cancelled';
                }
              }
            } else {
              // Look for parent form
              const element = document.querySelector(selector);
              if (element) {
                const parentForm = element.closest('form');
                if (parentForm) {
                  console.error('Found parent form, submitting...');
                  parentForm.submit();
                  return 'parent_form_submitted';
                }
              }
            }
            return 'no_form_found';
          }, formSelector);

          console.error(`JavaScript form submission result: ${result}`);
          if (result !== 'no_form_found') {
            submitted = true;
          }
        } catch (submitError) {
          console.error('JavaScript form submission failed:', submitError.message);
        }
      }

      if (!submitted) {
        console.error('All submission methods failed');
        throw new Error('Could not submit form using any method');
      }

      console.error('Form submission successful');
    } catch (error) {
      console.error('Form submission failed:', error.message);
      throw error;
    }
  }

  async classifyResult(successSelector, errorSelector) {
    try {
      const successSelectors = [
        successSelector,
        ...(this.validationRules.successSelectors || []),
        '.success',
        '.alert-success',
        '.message-success',
        '[class*="success"]',
        '.wpcf7-response-output:not(.wpcf7-validation-errors)',
        '.wpcf7-mail-sent-ok',
        '.wpcf7-mail-sent-ng:not(.wpcf7-validation-errors)',
      ].filter(Boolean);

      const errorSelectors = [
        errorSelector,
        ...(this.validationRules.errorSelectors || []),
        '.error',
        '.alert-error',
        '.alert-danger',
        '.message-error',
        '[class*="error"]',
        '.wpcf7-validation-errors',
        '.wpcf7-spam-blocked',
        '.wpcf7-mail-sent-ng',
      ].filter(Boolean);

      // Check for success indicators
      for (const selector of successSelectors) {
        const successElement = await this.page.$(selector);
        if (successElement) {
          const reason = `Success indicator found: ${selector}`;
          console.error(reason);
          return this.recordClassification('success', reason);
        }
      }

      // Check for error indicators
      for (const selector of errorSelectors) {
        const errorElement = await this.page.$(selector);
        if (errorElement) {
          const reason = `Error indicator found: ${selector}`;
          console.error(reason);
          return this.recordClassification('failure', reason);
        }
      }

      // If no specific selectors matched, try auto-detection
      console.error('No standard selectors matched - attempting auto-detection...');
      const autoResult = await this.autoDetectResult();
      if (autoResult) {
        return autoResult;
      }

      // Check if form is still present (indicates submission failure)
      const formStillPresent = await this.page.$(this.lastFormSelector);
      if (formStillPresent) {
        console.error('Form is still present after submission attempt - likely failed');
        return this.recordClassification('failure', 'Form still present after submission attempt');
      }

      // Check for URL changes that might indicate success
      const currentUrl = this.page.url();
      if (currentUrl !== this.initialUrl) {
        console.error(`URL changed from ${this.initialUrl} to ${currentUrl} - possible success`);
        return this.recordClassification('success', `URL changed to ${currentUrl}`);
      }

      // Check for common form submission success patterns
      const submissionSuccess = await this.page.evaluate((phrases) => {
        const pageText = document.body ? document.body.innerText.toLowerCase() : '';
        return phrases.some((phrase) => pageText.includes(phrase));
      }, [
        'thank you',
        'message sent',
        'form submitted',
        'successfully',
        'received',
        'sent successfully',
        'submitted successfully',
        ...this.validationRules.successPhrases,
      ]);

      if (submissionSuccess) {
        console.error('Success phrases found in page text');
        return this.recordClassification('success', 'Success phrases found after submission');
      }

      // If we can't determine, be conservative and assume failure
      console.error('Could not determine form submission result - assuming failure');
      return this.recordClassification('failure', 'Unable to determine submission outcome');
    } catch (error) {
      console.error('Result classification failed:', error.message);
      return this.recordClassification('failure', `Classification error: ${error.message}`);
    }
  }

  async validateFormSubmission(formSelector) {
    try {
      // Check if form is still present (indicates submission failure)
      // Check if form is still present (indicates submission failure OR ajax form)
      const formStillPresent = await this.page.$(formSelector);
      if (formStillPresent) {
        console.error('Form is still present after submission - checking for other success indicators');
        // Do not return false immediately, as it might be an AJAX form
      }

      // Check for URL changes
      const currentUrl = this.page.url();
      if (currentUrl !== this.initialUrl) {
        const keywords = this.validationRules.urlChangeKeywords || [];
        const keywordMatched = keywords.length === 0 || keywords.some((keyword) => currentUrl.includes(keyword));
        console.error(`URL changed from ${this.initialUrl} to ${currentUrl} - possible success`);
        if (keywordMatched) {
          return this.recordValidation(true, `URL changed to ${currentUrl}`);
        }
        this.recordValidation(false, `URL changed but keywords ${keywords.join(', ')} not found`);
      }

      // Check for form submission success messages
      const successMessages = await this.page.evaluate((phrases) => {
        const pageText = document.body ? document.body.innerText.toLowerCase() : '';
        return phrases.some((phrase) => pageText.includes(phrase));
      }, [
        'thank you',
        'message sent',
        'form submitted',
        'successfully',
        'received',
        'sent successfully',
        'submitted successfully',
        'email sent',
        'contact form submitted',
        ...this.validationRules.successPhrases,
      ]);

      if (successMessages) {
        console.error('Success messages found in page text');
        return this.recordValidation(true, 'Success phrases detected after submission');
      }

      // Check for form reset or disappearance
      const formInputs = await this.page.evaluate((selector) => {
        const form = document.querySelector(selector);
        if (!form) return 'form_gone';

        const inputs = form.querySelectorAll('input, textarea, select');
        let hasValues = false;
        for (const input of inputs) {
          if (input.value && input.value.trim()) {
            hasValues = true;
            break;
          }
        }

        if (!hasValues) {
          return 'form_cleared';
        }

        return 'form_with_values';
      }, formSelector);

      if (formInputs === 'form_gone') {
        console.error('Form disappeared after submission - likely success');
        return this.recordValidation(true, 'Form disappeared after submission');
      }

      if (formInputs === 'form_cleared') {
        console.error('Form was cleared after submission - possible success');
        return this.recordValidation(true, 'Form inputs cleared after submission');
      }

      console.error('Form submission validation failed - form still present with values');
      return this.recordValidation(false, 'Form still present with user-entered values');
    } catch (error) {
      console.error('Form submission validation failed:', error.message);
      return this.recordValidation(false, `Validation error: ${error.message}`);
    }
  }

  async extractMessage(successSelector, errorSelector) {
    try {
      // Try to extract message from success/error selectors
      const selectors = [successSelector, errorSelector].filter(Boolean);

      for (const selector of selectors) {
        const element = await this.page.$(selector);
        if (element) {
          const text = await element.textContent();
          if (text && text.trim()) {
            return text.trim();
          }
        }
      }

      // Try common message selectors
      const messageSelectors = [
        '.message',
        '.alert',
        '.notification',
        '.flash-message',
        '[role="alert"]'
      ];

      for (const selector of messageSelectors) {
        const element = await this.page.$(selector);
        if (element) {
          const text = await element.textContent();
          if (text && text.trim()) {
            return text.trim();
          }
        }
      }

      return null;
    } catch (error) {
      console.error('Message extraction failed:', error.message);
      return null;
    }
  }

  async cleanup() {
    try {
      if (this.page) {
        await this.page.close();
      }
      if (this.browser) {
        await this.browser.close();
      }
    } catch (error) {
      console.error('Cleanup failed:', error.message);
    }
  }

  processValue(value) {
    if (typeof value !== 'string') return value;

    const timestamp = Date.now();
    const randomId = Math.floor(Math.random() * 10000);

    return value
      .replace(/%EMAIL%/g, `test.user.${timestamp}.${randomId}@example.com`)
      .replace(/%NAME%/g, `Test User ${randomId}`)
      .replace(/%FIRST_NAME%/g, `Test`)
      .replace(/%LAST_NAME%/g, `User ${randomId}`)
      .replace(/%PHONE%/g, `555${String(randomId).padStart(4, '0')}`)
      .replace(/%TEXT%/g, `This is a test message generated at ${new Date().toISOString()} (ID: ${randomId})`)
      .replace(/%TIMESTAMP%/g, timestamp.toString())
      .replace(/%RANDOM%/g, randomId.toString());
  }

  async autoFillForm(formSelector) {
    try {
      // Find all visible inputs, textareas, and selects within the form
      const elements = await this.page.$$(
        `${formSelector} input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="reset"]), ${formSelector} textarea, ${formSelector} select`
      );

      console.error(`Found ${elements.length} potential fields to auto-fill`);

      for (const element of elements) {
        try {
          // Check visibility and enabled state using evaluate for better compatibility
          const { isVisible, isEnabled } = await element.evaluate(el => {
            const style = window.getComputedStyle(el);
            const visible = style && style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
            const enabled = !el.disabled;
            return { isVisible: visible, isEnabled: enabled };
          });

          if (!isVisible || !isEnabled) continue;

          // Get element attributes for heuristics
          const tagName = await element.evaluate(el => el.tagName.toLowerCase());
          const type = await element.evaluate(el => el.type || '');
          const name = await element.evaluate(el => el.name || '');
          const id = await element.evaluate(el => el.id || '');
          const placeholder = await element.evaluate(el => el.placeholder || '');

          // Combine attributes for matching
          const attributes = `${name} ${id} ${type} ${placeholder}`.toLowerCase();

          let valueToFill = null;

          // Heuristics
          if (tagName === 'select') {
            // For select, choose the second option (often the first valid one) or first if only one
            await element.evaluate(el => {
              if (el.options.length > 1) {
                el.selectedIndex = 1;
              } else if (el.options.length > 0) {
                el.selectedIndex = 0;
              }
              el.dispatchEvent(new Event('change', { bubbles: true }));
            });
            console.error(`Auto-filled select: ${name || id}`);
            continue;
          } else if (type === 'checkbox') {
            // Check checkboxes (often terms of service)
            const isChecked = await element.evaluate(el => el.checked);
            if (!isChecked) {
              await element.click();
              console.error(`Auto-checked checkbox: ${name || id}`);
            }
            continue;
          } else if (type === 'radio') {
            // Click radio if not checked
            const isChecked = await element.evaluate(el => el.checked);
            if (!isChecked) {
              await element.click();
              console.error(`Auto-selected radio: ${name || id}`);
            }
            continue;
          }

          // Text-based inputs
          if (attributes.includes('email') || attributes.includes('mail')) {
            valueToFill = '%EMAIL%';
          } else if (attributes.includes('phone') || attributes.includes('tel') || attributes.includes('mobile') || attributes.includes('number')) {
            valueToFill = '%PHONE%';
          } else if (attributes.includes('name') || attributes.includes('first') || attributes.includes('last') || attributes.includes('full')) {
            valueToFill = '%NAME%';
          } else if (tagName === 'textarea' || attributes.includes('message') || attributes.includes('comment') || attributes.includes('body') || attributes.includes('desc')) {
            valueToFill = '%TEXT%';
          } else if (attributes.includes('date')) {
            // Simple date fallback
            valueToFill = new Date().toISOString().split('T')[0];
          } else {
            // Default fallback
            valueToFill = 'Test Value %RANDOM%';
          }

          if (valueToFill) {
            const processedValue = this.processValue(valueToFill);

            // Clear and type
            await element.click({ clickCount: 3 });
            await this.page.keyboard.press('Backspace');
            await element.type(processedValue, { delay: 50 });

            console.error(`Auto-filled ${name || id} (${type}) with: ${processedValue}`);
          }

        } catch (elError) {
          console.error('Error auto-filling element:', elError.message);
        }
      }
    } catch (error) {
      console.error('Auto-fill failed:', error.message);
    }
  }

  async autoDetectResult() {
    try {
      console.error('Running Auto-Detection Scanner...');

      // Get all visible elements with text content
      const elements = await this.page.evaluate(() => {
        function isVisible(el) {
          const style = window.getComputedStyle(el);
          return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0' && el.offsetParent !== null;
        }

        const candidates = [];
        const allElements = document.querySelectorAll('div, p, span, h1, h2, h3, h4, h5, h6, li, .alert, .message, .toast');

        for (const el of allElements) {
          if (!isVisible(el)) continue;

          const text = el.innerText.trim().toLowerCase();
          if (text.length < 3 || text.length > 200) continue; // Ignore too short or too long text

          const style = window.getComputedStyle(el);
          const bgColor = style.backgroundColor;
          const color = style.color;

          candidates.push({
            text: text,
            tagName: el.tagName.toLowerCase(),
            className: el.className,
            bgColor: bgColor,
            color: color
          });
        }
        return candidates;
      });

      const successPhrases = this.validationRules?.success_phrases || ['success', 'sent', 'thank you', 'received', 'confirmed'];
      const errorPhrases = this.validationRules?.error_phrases || ['error', 'failed', 'invalid', 'required', 'problem'];

      // Check captured dialog message first
      if (this.lastDialogMessage) {
        const dialogText = this.lastDialogMessage.toLowerCase();
        console.error(`Debug: Checking dialog message: "${dialogText}"`);
        // Check for success keywords in dialog
        for (const phrase of successPhrases) {
          if (dialogText.includes(phrase.toLowerCase())) {
            const reason = `Auto-detected success in alert dialog: "${this.lastDialogMessage}"`;
            console.error(reason);
            return this.recordClassification('success', reason);
          }
        }
        // Check for error keywords in dialog
        for (const phrase of errorPhrases) {
          if (dialogText.includes(phrase.toLowerCase())) {
            const reason = `Auto-detected error in alert dialog: "${this.lastDialogMessage}"`;
            console.error(reason);
            return this.recordClassification('failure', reason);
          }
        }
      }

      for (const el of elements) {
        // Check for success keywords
        for (const phrase of successPhrases) {
          if (el.text.includes(phrase.toLowerCase())) {
            const reason = `Auto-detected success message: "${el.text.substring(0, 50)}..."`;
            console.error(reason);
            return this.recordClassification('success', reason);
          }
        }

        // Check for error keywords
        for (const phrase of errorPhrases) {
          if (el.text.includes(phrase.toLowerCase())) {
            const reason = `Auto-detected error message: "${el.text.substring(0, 50)}..."`;
            console.error(reason);
            return this.recordClassification('failure', reason);
          }
        }

        // Check for visual cues (Green/Red backgrounds)
        // Simple heuristic: Green often has '128' or higher in G channel, Red has '128' or higher in R channel
        // This is very basic and could be improved
        if (el.bgColor.includes('rgb')) {
          const rgb = el.bgColor.match(/\d+/g);
          if (rgb && rgb.length >= 3) {
            const [r, g, b] = rgb.map(Number);
            // Green-ish (Success)
            if (g > r + 50 && g > b + 50) {
              const reason = `Auto-detected success by color (green): "${el.text.substring(0, 50)}..."`;
              console.error(reason);
              return this.recordClassification('success', reason);
            }
            // Red-ish (Error)
            if (r > g + 50 && r > b + 50) {
              const reason = `Auto-detected error by color (red): "${el.text.substring(0, 50)}..."`;
              console.error(reason);
              return this.recordClassification('failure', reason);
            }
          }
        }
      }

      console.error('Auto-detection found no matches.');
      return null;

    } catch (error) {
      console.error('Auto-detection failed:', error.message);
      return null;
    }
  }
}

// CLI interface
async function main() {
  if (process.argv.length < 3) {
    console.error('Usage: node puppeteer-form-checker.js <config-json>');
    process.exit(1);
  }

  const configJson = process.argv[2];
  let config;

  try {
    config = JSON.parse(configJson);
  } catch (error) {
    console.error('Invalid JSON configuration:', error.message);
    process.exit(1);
  }

  const checker = new PuppeteerFormChecker();

  try {
    await checker.initialize();
    const result = await checker.checkForm(config);
    console.log(JSON.stringify(result));
  } catch (error) {
    console.error('Form checking failed:', error.message);
    console.log(JSON.stringify({ success: false, error: error.message }));
  } finally {
    await checker.cleanup();
  }
}

// Run if called directly
if (import.meta.url === `file://${process.argv[1]}`) {
  main().catch(console.error);
}

export default PuppeteerFormChecker;
