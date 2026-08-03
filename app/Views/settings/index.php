<section class="mt-7">
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <button class="btn btn-primary btn-sm">Email accounts</button>
        <button type="button" onclick="document.getElementById('workspaceDialog').showModal()" class="btn btn-soft btn-sm">Workspace & white label</button>
    </div>
    <section class="card p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-extrabold">Email accounts</h2>
                <p class="text-sm text-[#78908c]">Manage multiple SMTP accounts for this workspace.</p>
            </div>
            <button type="button" id="openSmtpDialog" class="btn btn-secondary">Add new SMTP account</button>
        </div>

        <div class="mt-5 space-y-3">
            <?php if (! empty($smtpAccounts)): ?>
                <?php foreach ($smtpAccounts as $account): ?>
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold"><?= esc($account['provider'] ?: 'Custom SMTP') ?> · <?= esc($account['host']) ?>:<?= esc($account['port']) ?></p>
                                <p class="text-sm text-[#78908c]">From <?= esc($account['from_name']) ?> &lt;<?= esc($account['from_email']) ?>&gt;</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-700"><?= $account['is_active'] ? 'active' : 'inactive' ?></span>
                                <button type="button" class="btn btn-sm" data-edit-smtp="<?= esc($account['id']) ?>">Edit</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            <?php else: ?>
                <div class="rounded-2xl bg-[#f5f5f5] p-4 text-sm text-[#6b7280]">
                    No SMTP accounts configured yet. Add one to start sending campaigns.
                </div>
            <?php endif ?>
        </div>

        <?php if ($plan && $plan['max_smtp_accounts'] !== null): ?>
            <p class="text-xs text-[#6b7280]">SMTP accounts: <?= count($smtpAccounts) ?> / <?= intval($plan['max_smtp_accounts']) ?></p>
        <?php endif ?>
    </section>

    <section class="card p-6 mt-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-extrabold">Email worker</h2>
                <p class="text-sm text-[#78908c]">Use a scheduled worker to process email campaigns and automations on time.</p>
            </div>
            <span class="rounded-full bg-[#e5f1ee] px-3 py-2 text-xs font-bold text-[#28775f]">Recommended</span>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-[#334155]">
            <p class="mb-3 font-semibold">Cron command</p>
            <pre class="rounded-2xl bg-slate-900 p-4 text-xs text-white">* * * * * cd /home/your-user/your-app && php spark campaigns:process 20 >> /var/log/mailora_campaigns.log 2>&1
* * * * * cd /home/your-user/your-app && php spark automations:process 20 >> /var/log/mailora_automations.log 2>&1</pre>
            <p class="mt-3">If you run on a VPS, add these commands to your crontab so scheduled broadcasts and API-triggered automations are processed automatically for each workspace.</p>
            <p class="mt-2">For a more durable worker, use systemd or supervisor to run the same commands on a regular interval.</p>
        </div>
    </section>
</section>

<dialog id="smtpDialog" class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl backdrop:bg-[#153b39]/30">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-extrabold"><?= $selectedSmtp ? 'Edit SMTP account' : 'New SMTP account' ?></h3>
            <p class="text-sm text-[#78908c]">Configure the SMTP account used for campaign delivery.</p>
        </div>
        <button type="button" onclick="document.getElementById('smtpDialog').close()" class="btn btn-soft btn-sm">Close</button>
    </div>

    <form action="<?= workspace_url('settings') ?>" method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="smtp_action" value="save_smtp">
        <input type="hidden" name="smtp_id" id="smtpId" value="<?= esc($selectedSmtp['id'] ?? '') ?>">

        <div class="grid grid-cols-2 gap-3">
            <label class="block text-xs font-bold">
                Provider
                <select name="smtp_provider" id="smtpProvider" class="field mt-2">
                    <option value="" <?= empty(old('smtp_provider', $selectedSmtp['provider'] ?? '')) ? 'selected' : '' ?>>Custom SMTP</option>
                    <option value="Gmail" <?= old('smtp_provider', $selectedSmtp['provider'] ?? '') === 'Gmail' ? 'selected' : '' ?>>Gmail</option>
                    <option value="Amazon SES" <?= old('smtp_provider', $selectedSmtp['provider'] ?? '') === 'Amazon SES' ? 'selected' : '' ?>>Amazon SES</option>
                    <option value="Mailgun" <?= old('smtp_provider', $selectedSmtp['provider'] ?? '') === 'Mailgun' ? 'selected' : '' ?>>Mailgun</option>
                    <option value="SendGrid" <?= old('smtp_provider', $selectedSmtp['provider'] ?? '') === 'SendGrid' ? 'selected' : '' ?>>SendGrid</option>
                    <option value="Brevo" <?= old('smtp_provider', $selectedSmtp['provider'] ?? '') === 'Brevo' ? 'selected' : '' ?>>Brevo</option>
                </select>
            </label>
            <label class="block text-xs font-bold">
                Encryption
                <select name="smtp_encryption" id="smtpEncryption" class="field mt-2">
                    <option value="tls" <?= old('smtp_encryption', $selectedSmtp['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= old('smtp_encryption', $selectedSmtp['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="" <?= empty(old('smtp_encryption', $selectedSmtp['encryption'] ?? '')) ? 'selected' : '' ?>>None</option>
                </select>
            </label>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <label class="block text-xs font-bold">
                SMTP host
                <input name="smtp_host" id="smtpHost" required class="field mt-2" value="<?= esc(old('smtp_host', $selectedSmtp['host'] ?? '')) ?>">
            </label>
            <label class="block text-xs font-bold">
                SMTP port
                <input name="smtp_port" id="smtpPort" type="number" required class="field mt-2" value="<?= esc(old('smtp_port', $selectedSmtp['port'] ?? '587')) ?>">
            </label>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <label class="block text-xs font-bold">
                Username
                <input name="smtp_username" id="smtpUsername" class="field mt-2" value="<?= esc(old('smtp_username', $selectedSmtp['username'] ?? '')) ?>">
            </label>
            <label class="block text-xs font-bold">
                Password
                <input name="smtp_password" id="smtpPassword" type="password" class="field mt-2" value="">
            </label>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <label class="block text-xs font-bold">
                From email
                <input name="smtp_from_email" id="smtpFromEmail" type="email" class="field mt-2" value="<?= esc(old('smtp_from_email', $selectedSmtp['from_email'] ?? $workspace['name'].'@example.com')) ?>">
            </label>
            <label class="block text-xs font-bold">
                From name
                <input name="smtp_from_name" id="smtpFromName" class="field mt-2" value="<?= esc(old('smtp_from_name', $selectedSmtp['from_name'] ?? $workspace['name'])) ?>">
            </label>
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="smtp_enabled" id="smtpEnabled" value="1" <?= old('smtp_enabled', $selectedSmtp['is_active'] ?? 0) ? 'checked' : '' ?> class="form-checkbox">
            Make this account active
        </label>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">Save SMTP account</button>
            <?php if ($selectedSmtp): ?>
                <button formaction="<?= workspace_url('settings') ?>" formmethod="post" name="smtp_action" value="delete_smtp" class="btn btn-secondary">Delete</button>
            <?php endif ?>
        </div>
    </form>
</dialog>

<dialog id="workspaceDialog" class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl backdrop:bg-[#153b39]/30">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-extrabold">Workspace & white label</h3>
            <p class="text-sm text-[#78908c]">Update workspace and custom domain settings.</p>
        </div>
        <button type="button" onclick="document.getElementById('workspaceDialog').close()" class="btn btn-soft btn-sm">Close</button>
    </div>

    <form action="<?= workspace_url('settings') ?>" method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="workspace_section" value="1">

        <label class="block text-xs font-bold">
            Workspace name
            <input name="name" required class="field mt-2" value="<?= esc(old('name', $workspace['name'])) ?>">
        </label>

        <label class="block text-xs font-bold">
            Custom domain
            <input name="custom_domain" class="field mt-2" value="<?= esc(old('custom_domain', $workspace['custom_domain'] ?? '')) ?>" placeholder="mail.yourdomain.com">
        </label>

        <div class="grid grid-cols-2 gap-3">
            <label class="text-xs font-bold">
                Timezone
                <select name="timezone" class="field mt-2">
                    <option <?= old('timezone', $workspace['timezone']) === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata</option>
                    <option <?= old('timezone', $workspace['timezone']) === 'UTC' ? 'selected' : '' ?>>UTC</option>
                    <option <?= old('timezone', $workspace['timezone']) === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                </select>
            </label>
            <label class="text-xs font-bold">
                Brand color
                <input name="brand_color" type="color" class="field mt-2 h-12" value="<?= esc(old('brand_color', $workspace['brand_color'])) ?>">
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Save workspace settings</button>
    </form>
</dialog>

<script>
    const smtpDialog = document.getElementById('smtpDialog');
    const workspaceDialog = document.getElementById('workspaceDialog');
    const openSmtpDialog = document.getElementById('openSmtpDialog');

    openSmtpDialog?.addEventListener('click', () => smtpDialog.showModal());

    document.querySelectorAll('[data-edit-smtp]').forEach(button => {
        button.addEventListener('click', () => {
            const smtpId = button.getAttribute('data-edit-smtp');
            window.location.href = '<?= workspace_url('settings') ?>?smtp_edit=' + smtpId + '&open=smtp';
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open') === 'smtp') {
        smtpDialog.showModal();
    }
    if (urlParams.get('open') === 'workspace') {
        workspaceDialog.showModal();
    }
</script>
