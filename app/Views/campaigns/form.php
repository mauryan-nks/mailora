<?php $c = $campaign ?? []; $editing = ! empty($c); ?>
<form action="<?= workspace_url($editing ? 'campaigns/'.$c['id'].'/update' : 'campaigns/create') ?>" method="post" class="mt-7 grid gap-5 xl:grid-cols-[1fr_1.4fr]">
    <?= csrf_field() ?>

    <section class="card space-y-4 p-6">
        <h2 class="text-lg font-extrabold">Campaign details</h2>

        <label class="block text-xs font-bold">
            Internal name
            <input name="name" required value="<?= esc(old('name', $c['name'] ?? '')) ?>" class="field mt-2">
        </label>

        <label class="block text-xs font-bold">
            Subject
            <input name="subject" required value="<?= esc(old('subject', $c['subject'] ?? '')) ?>" class="field mt-2">
        </label>

        <label class="block text-xs font-bold">
            Preview text
            <input name="preview_text" value="<?= esc(old('preview_text', $c['preview_text'] ?? '')) ?>" class="field mt-2">
        </label>

        <div class="grid grid-cols-2 gap-3">
            <label class="text-xs font-bold">
                From name
                <input name="from_name" required value="<?= esc(old('from_name', $c['from_name'] ?? 'Brightside')) ?>" class="field mt-2">
            </label>
            <label class="text-xs font-bold">
                From email
                <input name="from_email" type="email" required value="<?= esc(old('from_email', $c['from_email'] ?? 'hello@example.com')) ?>" class="field mt-2">
            </label>
        </div>


        <?php
            $selectedSmtpId = (int) old('smtp_id', $c['smtp_id'] ?? 0);
            $selectedSmtp = null;
            $defaultSmtp = null;
            foreach ($smtpAccounts as $smtp) {
                if (! $defaultSmtp && ! empty($smtp['is_active'])) {
                    $defaultSmtp = $smtp;
                }
                if ($selectedSmtpId && (int) $smtp['id'] === $selectedSmtpId) {
                    $selectedSmtp = $smtp;
                }
            }
        ?>

        <label class="block text-xs font-bold">
            Send from SMTP account
            <select name="smtp_id" class="field mt-2">
                <option value="">Workspace default SMTP</option>
                <?php foreach ($smtpAccounts as $smtp): ?>
                    <option value="<?= esc($smtp['id']) ?>" <?= (string) $selectedSmtpId === (string) $smtp['id'] ? 'selected' : '' ?>><?= esc($smtp['provider'] ?: 'Custom SMTP') ?> — <?= esc($smtp['from_name'] . ' <' . $smtp['from_email'] . '>') ?></option>
                <?php endforeach ?>
            </select>
            <p class="text-xs text-[#78908c] mt-1">Select a specific SMTP account, or use the workspace default.</p>
        </label>

        <?php if (empty($smtpAccounts)): ?>
            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-semibold">No SMTP accounts configured.</p>
                <p class="mt-2">You need at least one SMTP account in workspace settings to send campaigns. The workspace default SMTP will be used if none is selected.</p>
                <a href="<?= workspace_url('settings?open=smtp') ?>" class="mt-3 inline-flex items-center rounded-full border border-amber-200 bg-white px-3 py-2 text-sm font-semibold text-amber-700">Configure SMTP account</a>
            </div>
        <?php else: ?>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <p class="font-semibold">SMTP sending account</p>
                <?php if ($selectedSmtp): ?>
                    <p class="mt-2">Using selected SMTP account: <?= esc($selectedSmtp['provider'] ?: 'Custom SMTP') ?> — <?= esc($selectedSmtp['from_name'] . ' <' . $selectedSmtp['from_email'] . '>') ?></p>
                    <?php if (empty($selectedSmtp['is_active'])): ?>
                        <p class="mt-2 text-amber-700">Warning: this SMTP account is currently inactive. It may not be available for sending.</p>
                    <?php endif ?>
                <?php elseif ($defaultSmtp): ?>
                    <p class="mt-2">Using workspace default SMTP account: <?= esc($defaultSmtp['provider'] ?: 'Custom SMTP') ?> — <?= esc($defaultSmtp['from_name'] . ' <' . $defaultSmtp['from_email'] . '>') ?></p>
                <?php else: ?>
                    <p class="mt-2">No default SMTP account is active. You can select one or configure an account in settings.</p>
                <?php endif ?>
                <a href="<?= workspace_url('settings?open=smtp') ?>" class="mt-3 inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900">Open SMTP settings</a>
            </div>
        <?php endif ?>

        <label class="block text-xs font-bold">
            Schedule later (leave empty for draft)
            <input name="scheduled_at" type="datetime-local" value="<?= ! empty($c['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($c['scheduled_at'])) : '' ?>" class="field mt-2">
        </label>

        <label class="block text-xs font-bold">
            Timezone
            <select name="timezone" class="field mt-2">
                <option <?= (old('timezone', $c['timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : '') ?>>Asia/Kolkata</option>
                <option <?= (old('timezone', $c['timezone'] ?? '') === 'UTC' ? 'selected' : '') ?>>UTC</option>
                <option <?= (old('timezone', $c['timezone'] ?? '') === 'America/New_York' ? 'selected' : '') ?>>America/New_York</option>
                <option <?= (old('timezone', $c['timezone'] ?? '') === 'Europe/London' ? 'selected' : '') ?>>Europe/London</option>
            </select>
        </label>


        <label class="block text-xs font-bold">
            Audience segment
            <select name="segment_id" class="field mt-2">
                <option value="">All active subscribers</option>
                <?php foreach ($segments as $segment): ?>
                    <option value="<?= esc($segment['id']) ?>" <?= (string) old('segment_id', $c['segment_id'] ?? '') === (string) $segment['id'] ? 'selected' : '' ?>><?= esc($segment['name']) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </section>

    <section class="card p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold">Email editor</h2>
        </div>
        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#526c6a]">Spam score checker</p>
                    <p class="mt-2 text-sm text-[#33434a]">Score is updated as you edit the subject, from email, and content.</p>
                </div>
                <span id="spamSeverityBadge" class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] bg-slate-100 text-slate-700">Pending</span>
            </div>
            <p id="spamScoreText" class="mt-3 text-sm text-[#33434a]">Spam score is not calculated yet.</p>
        </section>
        <label class="block text-xs font-bold">
            Template
            <select id="templateSelector" name="template_id" class="field mt-2">
                <option value="">Use manual HTML</option>
                <?php foreach ($templates as $template): ?>
                    <option value="<?= esc($template['id']) ?>" <?= (string) old('template_id', $c['template_id'] ?? '') === (string) $template['id'] ? 'selected' : '' ?>><?= esc($template['name']) ?><?= $template['category'] ? ' — '.esc($template['category']) : '' ?></option>
                <?php endforeach ?>
            </select>
            <p class="text-xs text-[#78908c] mt-1">Selected template content will be applied at send-time.</p>
        </label>

        <textarea id="contentHtml" name="content_html" rows="18" class="field mt-5 font-mono text-sm" placeholder="Build your email with HTML or rich text…"><?= esc(old('content_html', $c['content_html'] ?? '<h1>Hello {{first_name}}</h1>
<p>Share your story here.</p>')) ?></textarea>

        <div class="mt-5 flex flex-wrap justify-end gap-3">
            <?php if ($editing): ?>
                <button type="button" id="sendTestButton" class="btn shadow-soft">Send test</button>
                <button formaction="<?= workspace_url('campaigns/'.$c['id'].'/send') ?>" class="btn btn-secondary">Send now</button>
            <?php endif ?>
            <button type="button" onclick="previewEmail()" class="btn shadow-soft">Preview</button>
            <button class="btn btn-primary">Save campaign</button>
        </div>
        <div id="testStatus" class="mt-4 text-sm"></div>
    </section>
</form>

<dialog id="preview" class="h-[80vh] w-full max-w-2xl rounded-3xl bg-white p-6 backdrop:bg-[#153b39]/30">
    <button onclick="this.closest('dialog').close()" class="float-right"><i data-lucide="x"></i></button>
    <div class="max-h-[78vh] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <p id="previewFrom" class="text-sm text-slate-500"></p>
            <p id="previewSubject" class="mt-2 text-lg font-semibold text-slate-900"></p>
            <p id="previewPreviewText" class="mt-1 text-sm text-slate-500"></p>
        </div>
        <div id="previewBody" class="prose max-h-[60vh] overflow-auto p-6"></div>
    </div>
</dialog>

<script>
    const templateContents = <?= json_encode(array_column($templates, 'content_html', 'id')) ?>;
    const templateSelector = document.getElementById('templateSelector');
    const contentHtml = document.getElementById('contentHtml');

    templateSelector.addEventListener('change', function () {
        const html = templateContents[this.value] ?? '';
        if (html) {
            contentHtml.value = html;
        }
    });

    if (templateSelector.value) {
        const html = templateContents[templateSelector.value] ?? '';
        if (html) {
            contentHtml.value = html;
        }
    }

    function previewEmail() {
        const subject = (document.querySelector('input[name="subject"]')?.value || '').trim();
        const fromName = (document.querySelector('input[name="from_name"]')?.value || '').trim();
        const fromEmail = (document.querySelector('input[name="from_email"]')?.value || '').trim();
        const previewText = (document.querySelector('input[name="preview_text"]')?.value || '').trim();

        document.getElementById('previewFrom').textContent = `From: ${fromName || 'Unknown'} <${fromEmail || 'no-reply@example.com'}>`;
        document.getElementById('previewSubject').textContent = subject || 'No subject';
        document.getElementById('previewPreviewText').textContent = previewText || 'Preview text will appear here.';
        document.getElementById('previewBody').innerHTML = contentHtml.value;
        document.getElementById('preview').showModal();
    }

    async function sendTestEmail() {
        const button = document.getElementById('sendTestButton');
        const status = document.getElementById('testStatus');
        const form = document.querySelector('form');
        const csrfInput = form?.querySelector('input[type="hidden"][name*="csrf"]');
        const url = <?= $editing ? "'" . workspace_url('campaigns/'.$c['id'].'/test') . "'" : 'null' ?>;

        if (!button || !csrfInput || !url) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Sending...';
        status.textContent = '';
        status.className = 'mt-4 text-sm';

        const body = new URLSearchParams();
        body.append(csrfInput.name, csrfInput.value);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body,
            });

            const data = await response.json();
            if (! response.ok || data.success === false) {
                status.className = 'mt-4 text-sm text-red-600';
                status.textContent = data.error || 'Unable to send test email.';
            } else {
                status.className = 'mt-4 text-sm text-emerald-700';
                status.textContent = data.message || 'Test email sent successfully.';
            }
        } catch (error) {
            status.className = 'mt-4 text-sm text-red-600';
            status.textContent = error.message || 'Unable to send test email.';
        } finally {
            button.disabled = false;
            button.textContent = 'Send test';
        }
    }

    function calculateSpamScore() {
        const subject = (document.querySelector('input[name="subject"]')?.value || '').trim();
        const fromEmail = (document.querySelector('input[name="from_email"]')?.value || '').trim();
        const content = (document.getElementById('contentHtml')?.value || '').trim();
        const lowerContent = content.toLowerCase();
        const lowerSubject = subject.toLowerCase();
        let score = 100;

        const spamWords = ['free', 'earn', 'winner', 'bonus', 'buy now', 'limited time', 'cheap', 'urgent', 'act now', 'click here', 'offer', 'money back', '$$$', 'risk-free', 'no obligation', 'limited offer', 'sale', 'cash', 'investment', 'guaranteed', 'roi', 'return on investment', 'financial consultant', 'private funds', 'long term investment', 'answer asap', 'dear friend', 'projects', 'funds'];
        const financialScamPhrases = ['financial consultant', 'long term investments', 'guaranteed 5% roi', 'guaranteed 5% return', 'private owned funds', 'invest these funds', 'answer asap', 'please answer asap', 'funds placed', 'funds placed for', 'willing to finance projects', 'investments in projects'];

        if (!subject) {
            score -= 18;
        }

        if (!fromEmail || !/^\S+@\S+\.\S+$/.test(fromEmail)) {
            score -= 25;
        }

        if (lowerSubject.includes('dear friend')) {
            score -= 12;
        }

        const badWordMatches = spamWords.reduce((count, word) => {
            const regex = new RegExp(word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
            return count + ((content.match(regex) || []).length + (subject.match(regex) || []).length);
        }, 0);
        score -= Math.min(badWordMatches * 5, 45);

        const scamPhraseMatches = financialScamPhrases.reduce((count, phrase) => {
            const regex = new RegExp(phrase.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
            return count + ((lowerContent.match(regex) || []).length + (lowerSubject.match(regex) || []).length);
        }, 0);
        score -= Math.min(scamPhraseMatches * 10, 50);

        if (lowerContent.includes('answer asap') || lowerSubject.includes('answer asap')) {
            score -= 15;
        }

        if (lowerContent.includes('guaranteed') && lowerContent.includes('roi')) {
            score -= 20;
        }

        const uppercaseWords = (content.match(/\b[A-Z]{3,}\b/g) || []).length;
        score -= Math.min(uppercaseWords * 2, 15);

        const exclamationCount = (subject.match(/!/g) || []).length + (content.match(/!/g) || []).length;
        score -= Math.min(exclamationCount * 4, 20);

        const urlCount = (content.match(/https?:\/\//gi) || []).length;
        score -= Math.min(urlCount * 4, 20);

        const imageWithoutAlt = (content.match(/<img[^>]*>/gi) || []).filter(tag => !/alt=/.test(tag)).length;
        score -= Math.min(imageWithoutAlt * 5, 15);

        if (content.length < 200) {
            score -= 15;
        }

        if (content.length > 5000) {
            score -= 5;
        }

        if (content.split('\n').length < 5) {
            score -= 10;
        }

        score = Math.max(0, Math.min(100, score));
        const severity = score >= 80 ? 'Excellent' : score >= 65 ? 'Good' : score >= 50 ? 'Fair' : 'Poor';
        const color = score >= 80 ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : score >= 65 ? 'bg-amber-100 text-amber-700 border-amber-200' : score >= 50 ? 'bg-orange-100 text-orange-700 border-orange-200' : 'bg-red-100 text-red-700 border-red-200';
        const spamText = document.getElementById('spamScoreText');
        const badge = document.getElementById('spamSeverityBadge');

        if (spamText) {
            spamText.innerHTML = `Estimated spam score: <strong>${score} / 100</strong>. ${severity} delivery risk.`;
        }

        if (badge) {
            badge.textContent = severity;
            badge.className = `rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] ${color}`;
        }
    }

    const spamInputs = [
        document.querySelector('input[name="subject"]'),
        document.querySelector('input[name="from_email"]'),
        document.getElementById('contentHtml'),
    ];

    spamInputs.forEach(el => el?.addEventListener('input', calculateSpamScore));
    calculateSpamScore();

    document.getElementById('sendTestButton')?.addEventListener('click', sendTestEmail);
</script>
