<?php $automation = $automation ?? []; $editing = ! empty($automation); ?>
<section class="mt-7">
    <form action="<?= workspace_url($editing ? 'automations/'.$automation['id'].'/update' : 'automations/create') ?>" method="post" class="card space-y-4 p-6">
        <?= csrf_field() ?>

        <div>
            <h2 class="text-lg font-extrabold"><?= $editing ? 'Edit automation' : 'New automation' ?></h2>
            <p class="text-sm text-[#78908c]">Configure triggers, segments, and SMTP for automatic follow-up emails.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block text-xs font-bold">
                Name
                <input name="name" required class="field mt-2" value="<?= esc(old('name', $automation['name'] ?? '')) ?>">
            </label>
            <label class="block text-xs font-bold">
                Trigger
                <select id="triggerType" name="trigger_type" class="field mt-2">
                    <option value="welcome" <?= old('trigger_type', $automation['trigger_type'] ?? '') === 'welcome' ? 'selected' : '' ?>>Welcome message</option>
                    <option value="birthday" <?= old('trigger_type', $automation['trigger_type'] ?? '') === 'birthday' ? 'selected' : '' ?>>Birthday reminder</option>
                    <option value="follow_up" <?= old('trigger_type', $automation['trigger_type'] ?? '') === 'follow_up' ? 'selected' : '' ?>>Follow-up</option>
                    <option value="api_event" <?= old('trigger_type', $automation['trigger_type'] ?? '') === 'api_event' ? 'selected' : '' ?>>API event</option>
                </select>
            </label>
        </div>

        <section id="apiTriggerPanel" class="card mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-5 <?= old('trigger_type', $automation['trigger_type'] ?? '') === 'api_event' ? '' : 'hidden' ?>">
            <div>
                <h3 class="text-sm font-semibold">API trigger</h3>
                <p class="text-xs text-[#78908c]">Trigger this automation externally using the event name below.</p>
            </div>
            <label class="block text-xs font-bold mt-4">
                Event name
                <input name="trigger_event" class="field mt-2" value="<?= esc(old('trigger_event', $automation['trigger_event'] ?? '')) ?>" placeholder="e.g. user_signup">
            </label>
            <pre class="mt-4 overflow-x-auto rounded-3xl bg-slate-950 p-4 text-xs text-white/80">POST <?= rtrim(base_url(), '/') ?>/api/v1/automations/trigger
Headers: X-API-Key: YOUR_KEY
Body: {"automation_id": <?= $automation['id'] ?? 'null' ?>, "event": "<?= esc(old('trigger_event', $automation['trigger_event'] ?? '')) ?>"}
            </pre>
        </section>

        <div class="grid gap-4 md:grid-cols-2 mt-5">
            <label class="block text-xs font-bold">
                Flow action
                <select id="flowAction" name="flow_action" class="field mt-2">
                    <option value="send_email" <?= old('flow_action', $automation['flow_action'] ?? 'send_email') === 'send_email' ? 'selected' : '' ?>>Send email</option>
                    <option value="call_webhook" <?= old('flow_action', $automation['flow_action'] ?? '') === 'call_webhook' ? 'selected' : '' ?>>Call webhook</option>
                </select>
            </label>
            <label class="block text-xs font-bold">
                Delay (minutes)
                <input name="delay_minutes" type="number" class="field mt-2" value="<?= esc(old('delay_minutes', $automation['delay_minutes'] ?? 0)) ?>">
            </label>
        </div>

        <section id="webhookPanel" class="card mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-5 <?= old('flow_action', $automation['flow_action'] ?? 'send_email') === 'call_webhook' ? '' : 'hidden' ?>">
            <div>
                <h3 class="text-sm font-semibold">Webhook action</h3>
                <p class="text-xs text-[#78908c]">Send a webhook each time this automation runs.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2 mt-4">
                <label class="block text-xs font-bold">
                    Webhook URL
                    <input name="webhook_url" class="field mt-2" value="<?= esc(old('webhook_url', $automation['webhook_url'] ?? '')) ?>" placeholder="https://example.com/webhook">
                </label>
                <label class="block text-xs font-bold">
                    Method
                    <select name="webhook_method" class="field mt-2">
                        <option value="POST" <?= old('webhook_method', $automation['webhook_method'] ?? 'POST') === 'POST' ? 'selected' : '' ?>>POST</option>
                        <option value="GET" <?= old('webhook_method', $automation['webhook_method'] ?? '') === 'GET' ? 'selected' : '' ?>>GET</option>
                    </select>
                </label>
            </div>
            <label class="block text-xs font-bold mt-4">
                Payload
                <textarea name="webhook_payload" rows="4" class="field mt-2 font-mono text-sm" placeholder='{"automation":"{{name}}","email":"{{email}}"}'><?= esc(old('webhook_payload', $automation['webhook_payload'] ?? '')) ?></textarea>
            </label>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block text-xs font-bold">
                Status
                <select name="status" class="field mt-2">
                    <option value="paused" <?= old('status', $automation['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
                    <option value="active" <?= old('status', $automation['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                </select>
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block text-xs font-bold">
                Segment
                <select name="segment_id" class="field mt-2">
                    <option value="">All subscribers</option>
                    <?php foreach ($segments as $segment): ?>
                        <option value="<?= esc($segment['id']) ?>" <?= (string) old('segment_id', $automation['segment_id'] ?? '') === (string) $segment['id'] ? 'selected' : '' ?>><?= esc($segment['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
            <label class="block text-xs font-bold">
                Send using SMTP
                <select name="smtp_id" class="field mt-2">
                    <option value="">Workspace default</option>
                    <?php foreach ($smtpAccounts as $smtp): ?>
                        <option value="<?= esc($smtp['id']) ?>" <?= (string) old('smtp_id', $automation['smtp_id'] ?? '') === (string) $smtp['id'] ? 'selected' : '' ?>><?= esc($smtp['from_name'] . ' <' . $smtp['from_email'] . '>') ?></option>
                    <?php endforeach ?>
                </select>
            </label>
        </div>

        <label class="block text-xs font-bold">
            Email subject
            <input name="subject" class="field mt-2" value="<?= esc(old('subject', $automation['subject'] ?? '')) ?>">
        </label>

        <label class="block text-xs font-bold">
            Email content
            <textarea name="content_html" rows="14" class="field mt-2 font-mono text-sm"><?= esc(old('content_html', $automation['content_html'] ?? '<h1>Hello {{first_name}}</h1><p>Thanks for joining.</p>')) ?></textarea>
        </label>

        <div class="flex justify-end gap-3">
            <a href="<?= workspace_url('automations') ?>" class="btn btn-soft">Cancel</a>
            <button class="btn btn-primary"><?= $editing ? 'Update automation' : 'Save automation' ?></button>
        </div>
    </form>
</section>

<script>
    const triggerType = document.getElementById('triggerType');
    const flowAction = document.getElementById('flowAction');
    const apiTriggerPanel = document.getElementById('apiTriggerPanel');
    const webhookPanel = document.getElementById('webhookPanel');

    function updateAutomationFlowUI() {
        const apiSelected = triggerType.value === 'api_event';
        const webhookSelected = flowAction.value === 'call_webhook';

        apiTriggerPanel.classList.toggle('hidden', !apiSelected);
        webhookPanel.classList.toggle('hidden', !webhookSelected);
    }

    triggerType?.addEventListener('change', updateAutomationFlowUI);
    flowAction?.addEventListener('change', updateAutomationFlowUI);
    updateAutomationFlowUI();
</script>
