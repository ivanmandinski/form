#!/usr/bin/env node

/**
 * CAPTCHA Solver Module
 * Supports multiple solving services: 2captcha, AntiCaptcha, and reCAPTCHA test keys
 */

import axios from 'axios';

export class CaptchaSolver {
  constructor(config = {}) {
    this.apiKey = config.apiKey || process.env.CAPTCHA_SOLVER_API_KEY || '';
    this.provider = config.provider || process.env.CAPTCHA_SOLVER_PROVIDER || '2captcha';
    this.timeout = config.timeout || 120000; // 2 minutes default
    this.enabled = config.enabled !== false && !!this.apiKey;
    
    // Log initialization for debugging
    if (this.enabled) {
      const apiKeyPreview = this.apiKey ? `${this.apiKey.substring(0, 8)}...${this.apiKey.substring(this.apiKey.length - 4)}` : 'NOT SET';
      console.error(`✅ CAPTCHA Solver initialized: ${this.provider} (API Key: ${apiKeyPreview})`);
    } else {
      console.error(`⚠️ CAPTCHA Solver disabled: No API key configured`);
    }
    
    // reCAPTCHA test keys (always pass)
    this.testKeys = {
      siteKey: '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
      secretKey: '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'
    };
  }

  /**
   * Check if CAPTCHA solving is enabled
   */
  isEnabled() {
    return this.enabled;
  }

  /**
   * Detect CAPTCHA type and site key from page
   */
  async detectCaptcha(page) {
    try {
      const captchaInfo = await page.evaluate(() => {
        // Check for reCAPTCHA v2/v3
        const recaptchaElement = document.querySelector('.g-recaptcha');
        if (recaptchaElement) {
          const siteKey = recaptchaElement.getAttribute('data-sitekey') || 
                        recaptchaElement.getAttribute('data-site-key');
          if (siteKey) {
            return {
              type: 'recaptcha_v2',
              siteKey: siteKey,
              element: '.g-recaptcha'
            };
          }
        }

        // Check for reCAPTCHA v3 (invisible)
        const recaptchaV3 = document.querySelector('[data-sitekey]');
        if (recaptchaV3) {
          const siteKey = recaptchaV3.getAttribute('data-sitekey');
          if (siteKey && siteKey.length > 20) {
            return {
              type: 'recaptcha_v3',
              siteKey: siteKey,
              element: '[data-sitekey]'
            };
          }
        }

        // Check for hCaptcha
        const hcaptchaElement = document.querySelector('.h-captcha');
        if (hcaptchaElement) {
          const siteKey = hcaptchaElement.getAttribute('data-sitekey');
          if (siteKey) {
            return {
              type: 'hcaptcha',
              siteKey: siteKey,
              element: '.h-captcha'
            };
          }
        }

        // Check for reCAPTCHA iframe
        const iframe = document.querySelector('iframe[src*="recaptcha"]');
        if (iframe) {
          const src = iframe.getAttribute('src');
          const siteKeyMatch = src.match(/k=([^&]+)/);
          if (siteKeyMatch) {
            return {
              type: 'recaptcha_v2',
              siteKey: siteKeyMatch[1],
              element: 'iframe[src*="recaptcha"]'
            };
          }
        }

        return null;
      });

      return captchaInfo;
    } catch (error) {
      console.error('CAPTCHA detection error:', error.message);
      return null;
    }
  }

  /**
   * Check if site key is a test key
   */
  isTestKey(siteKey) {
    return siteKey === this.testKeys.siteKey;
  }

  /**
   * Solve reCAPTCHA using 2captcha service
   */
  async solveWith2Captcha(siteKey, pageUrl) {
    if (!this.apiKey) {
      throw new Error('2captcha API key not configured');
    }

    const apiUrl = 'https://2captcha.com/in.php';
    const checkUrl = 'https://2captcha.com/res.php';

    console.error(`🔄 Submitting CAPTCHA to 2captcha API...`);
    console.error(`   Site Key: ${siteKey.substring(0, 20)}...`);
    console.error(`   Page URL: ${pageUrl}`);

    try {
      // Submit CAPTCHA to 2captcha
      const submitUrl = `${apiUrl}?key=${this.apiKey}&method=userrecaptcha&googlekey=${siteKey}&pageurl=${encodeURIComponent(pageUrl)}&json=1`;
      console.error(`   API URL: ${apiUrl} (key hidden)`);
      
      const submitResponse = await axios.get(submitUrl);
      const submitData = submitResponse.data;

      if (submitData.status !== 1) {
        throw new Error(`2captcha submission failed: ${submitData.request}`);
      }

      const taskId = submitData.request;
      console.error(`✅ CAPTCHA submitted to 2captcha successfully`);
      console.error(`   Task ID: ${taskId}`);
      console.error(`   Status: ${submitData.status === 1 ? 'OK' : 'ERROR'}`);

      // Poll for solution
      const startTime = Date.now();
      let pollCount = 0;
      const maxPollTime = this.timeout;
      
      while (Date.now() - startTime < maxPollTime) {
        pollCount++;
        const elapsed = Date.now() - startTime;
        const remaining = maxPollTime - elapsed;
        
        // Don't wait if we're close to timeout
        if (remaining < 6000) {
          console.error(`   Approaching timeout, stopping poll (remaining: ${Math.round(remaining/1000)}s)`);
          break;
        }
        
        await new Promise(resolve => setTimeout(resolve, 5000)); // Wait 5 seconds

        console.error(`   Polling for solution (attempt ${pollCount}, elapsed: ${Math.round(elapsed/1000)}s, remaining: ${Math.round(remaining/1000)}s)...`);
        const checkResponse = await axios.get(`${checkUrl}?key=${this.apiKey}&action=get&id=${taskId}&json=1`);
        const checkData = checkResponse.data;

        if (checkData.status === 1) {
          const elapsed = Math.round((Date.now() - startTime) / 1000);
          console.error(`✅ CAPTCHA solved by 2captcha successfully!`);
          console.error(`   Time taken: ${elapsed} seconds`);
          console.error(`   Token preview: ${checkData.request.substring(0, 50)}...`);
          return checkData.request; // This is the g-recaptcha-response token
        }

        if (checkData.request === 'CAPCHA_NOT_READY') {
          console.error(`   Status: Not ready yet, continuing to poll...`);
          continue; // Keep polling
        }

        console.error(`❌ 2captcha API error: ${checkData.request}`);
        throw new Error(`2captcha solving failed: ${checkData.request}`);
      }

      throw new Error('2captcha timeout: Solution not received in time');
    } catch (error) {
      console.error('2captcha solving error:', error.message);
      throw error;
    }
  }

  /**
   * Solve reCAPTCHA using AntiCaptcha service
   */
  async solveWithAntiCaptcha(siteKey, pageUrl) {
    if (!this.apiKey) {
      throw new Error('AntiCaptcha API key not configured');
    }

    const apiUrl = 'https://api.anti-captcha.com/createTask';
    const checkUrl = 'https://api.anti-captcha.com/getTaskResult';

    try {
      // Create task
      const taskResponse = await axios.post(apiUrl, {
        clientKey: this.apiKey,
        task: {
          type: 'RecaptchaV2TaskProxyless',
          websiteURL: pageUrl,
          websiteKey: siteKey
        }
      });

      const taskData = taskResponse.data;
      if (taskData.errorId !== 0) {
        throw new Error(`AntiCaptcha task creation failed: ${taskData.errorDescription}`);
      }

      const taskId = taskData.taskId;
      console.error(`CAPTCHA task created with AntiCaptcha, task ID: ${taskId}`);

      // Poll for solution
      const startTime = Date.now();
      while (Date.now() - startTime < this.timeout) {
        await new Promise(resolve => setTimeout(resolve, 5000)); // Wait 5 seconds

        const checkResponse = await axios.post(checkUrl, {
          clientKey: this.apiKey,
          taskId: taskId
        });

        const checkData = checkResponse.data;

        if (checkData.errorId !== 0) {
          throw new Error(`AntiCaptcha check failed: ${checkData.errorDescription}`);
        }

        if (checkData.status === 'ready') {
          console.error(`CAPTCHA solved by AntiCaptcha`);
          return checkData.solution.gRecaptchaResponse;
        }

        if (checkData.status === 'processing') {
          continue; // Keep polling
        }
      }

      throw new Error('AntiCaptcha timeout: Solution not received in time');
    } catch (error) {
      console.error('AntiCaptcha solving error:', error.message);
      throw error;
    }
  }

  /**
   * Inject reCAPTCHA test token (for test keys)
   */
  async injectTestToken(page, siteKey) {
    try {
      await page.evaluate((siteKey) => {
        // Wait for grecaptcha to be available
        if (typeof window.grecaptcha !== 'undefined') {
          // For reCAPTCHA v2, we need to trigger the callback
          const callback = window.grecaptchaCallback || function(token) {
            const textarea = document.querySelector('textarea[name="g-recaptcha-response"]');
            if (textarea) {
              textarea.value = token;
              textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
          };

          // Use test token
          const testToken = 'test-token-' + Date.now();
          callback(testToken);
        } else {
          // Inject token directly into textarea
          const textarea = document.querySelector('textarea[name="g-recaptcha-response"]');
          if (textarea) {
            textarea.value = 'test-token-' + Date.now();
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            textarea.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }
      }, siteKey);

      console.error('Test token injected for reCAPTCHA');
      return true;
    } catch (error) {
      console.error('Test token injection error:', error.message);
      return false;
    }
  }

  /**
   * Inject solved token into page
   */
  async injectToken(page, token) {
    try {
      await page.evaluate((token) => {
        // Find the g-recaptcha-response textarea
        const textarea = document.querySelector('textarea[name="g-recaptcha-response"]');
        if (textarea) {
          textarea.value = token;
          textarea.dispatchEvent(new Event('input', { bubbles: true }));
          textarea.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Also try to set it via grecaptcha callback if available
        if (typeof window.grecaptcha !== 'undefined' && window.grecaptchaCallback) {
          window.grecaptchaCallback(token);
        }

        // Trigger any reCAPTCHA callbacks
        if (typeof window.grecaptcha !== 'undefined' && window.grecaptcha.execute) {
          // For v3, we might need to execute
          try {
            window.grecaptcha.execute();
          } catch (e) {
            // Ignore errors
          }
        }
      }, token);

      console.error('CAPTCHA token injected into page');
      return true;
    } catch (error) {
      console.error('Token injection error:', error.message);
      return false;
    }
  }

  /**
   * Solve CAPTCHA (main method)
   */
  async solve(page, pageUrl) {
    if (!this.enabled) {
      console.error('CAPTCHA solving is disabled');
      return null;
    }

    try {
      // Detect CAPTCHA
      const captchaInfo = await this.detectCaptcha(page);
      if (!captchaInfo) {
        console.error('No CAPTCHA detected');
        return null;
      }

      console.error(`CAPTCHA detected: ${captchaInfo.type}, site key: ${captchaInfo.siteKey.substring(0, 20)}...`);

      // Check if it's a test key
      if (this.isTestKey(captchaInfo.siteKey)) {
        console.error('Test key detected - using test token');
        await this.injectTestToken(page, captchaInfo.siteKey);
        await new Promise(resolve => setTimeout(resolve, 1000)); // Wait for token to be processed
        return 'test-token';
      }

      // Solve using configured service
      let token;
      if (this.provider === 'anticaptcha') {
        token = await this.solveWithAntiCaptcha(captchaInfo.siteKey, pageUrl);
      } else {
        token = await this.solveWith2Captcha(captchaInfo.siteKey, pageUrl);
      }

      // Inject token into page
      await this.injectToken(page, token);
      await new Promise(resolve => setTimeout(resolve, 1000)); // Wait for token to be processed

      return token;
    } catch (error) {
      console.error('CAPTCHA solving failed:', error.message);
      throw error;
    }
  }
}

export default CaptchaSolver;

