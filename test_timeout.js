
import PuppeteerFormChecker from './resources/js/puppeteer-form-checker.js';

const checker = new PuppeteerFormChecker();

const config = {
    url: 'https://www.scsengineers.com/contact-scs-engineers/',
    selectorType: 'css',
    selectorValue: '.cfp-contact-form',
    fieldMappings: [], // Empty to trigger auto-fill
    successSelector: null,
    errorSelector: null,
    timeout: 60000, // 60s for local test
    waitForJavaScript: true,
    captchaExpected: true,
    validationRules: {
        success_selectors: [".wpcf7-response-output.wpcf7-mail-sent-ok", ".cf7-success"],
        error_selectors: [".wpcf7-validation-errors", ".cf7-error", ".form-error"],
        success_phrases: ["your message has been sent", "we will get back to you"],
        error_phrases: ["please fix the errors", "invalid captcha", "verification failed"],
        url_change_keywords: ["#thank", "thank-you", "success"]
    }
};

console.log('Testing Timeout Issue on:', config.url);

checker.initialize().then(async () => {
    console.log('Browser initialized');
    const result = await checker.checkForm(config);
    console.log('Result:', JSON.stringify(result, null, 2));
    await checker.cleanup();
}).catch(error => {
    console.error('Test failed:', error);
    process.exit(1);
});
