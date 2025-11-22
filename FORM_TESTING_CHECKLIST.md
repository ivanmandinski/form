# Form Submission Testing Checklist

Use this guide when validating whether a monitored form still submits correctly or when triaging a failing run.

## 1. Capture a fresh run

1. Trigger a manual check in the UI (`Run Now`) **or** run the CLI helper:
   ```bash
   php artisan forms:test {form_id}
   # or machine-readable:
   php artisan forms:test {form_id} --json
   ```
2. For remote environments, run through Railway:
   ```bash
   railway run --service horizon "php artisan forms:test {form_id} --json"
   ```

## 2. Enable rich diagnostics (optional)

Set the following variables (locally or in Railway) while debugging, then redeploy/restart Horizon:

```
PUPPETEER_DEBUG=true
FORM_MONITOR_LOG_ARTIFACTS=true
```

They add verbose step-by-step logs and force HTML/screenshot artifacts to be stored for every run.

## 3. Review the run record

1. Open **Admin → Runs → (latest run)**.
2. Download the `*_html.html` and `*_debug.json` artifacts.
3. In the JSON file look at:
   - `debugInfo.validation` – why validation passed/failed.
   - `debugInfo.classification` – the heuristic that labeled the result.
   - `debugInfo.http.navigation` – HTTP status/headers from the initial load.
   - `debugInfo.steps` – chronological timeline produced by Puppeteer.
4. If CAPTCHA monitoring is enabled, check `captchaDetected` / `captchaBlocking` flags to see whether the block was intentional.

## 4. Troubleshoot common cases

| Symptom | Action |
| --- | --- |
| `CAPTCHA was expected but not detected` | Confirm the widget still renders. Update the form config if the site removed CAPTCHA. |
| `CAPTCHA did not block submission` | Inspect the HTML artifact for spam-block messages. Add site-specific phrases/selectors to `config/form-monitor.php → validation`. |
| `Unknown error` | Look at `debugInfo.steps` to see which step failed. Re-run locally with `PUPPETEER_DEBUG=true` for live console logs. |
| Timeout | Increase `FORM_MONITOR_PUPPETEER_TIMEOUT` or add `waitForElements` / custom actions so the script isn’t polling indefinitely. |

## 5. Reproduce locally

1. Copy the form configuration from the admin UI.
2. Run the checker directly:
   ```bash
   node resources/js/puppeteer-form-checker.js '{"url":"https://…","selectorType":"css","selectorValue":"form#contact","captchaExpected":false}'
   ```
3. Inspect the console output and generated summary for faster iteration.

## 6. Update selectors/phrases

When you notice a new success or error message:

1. Add custom selectors on the form record (`success_selector`, `error_selector`).
2. If it’s globally useful, extend the arrays under `config/form-monitor.php → validation` and redeploy so every run benefits.

## 7. Sign off

A form is considered “passing” when:

- `status` is `success` or `blocked`.
- The message matches the expected thank-you/blocked-by-CAPTCHA notice.
- The HTML artifact shows the correct confirmation message and no validation errors.

Document any deviations in project issues so future reviewers know which behaviour is expected.

