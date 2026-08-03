<div class="page-header">
    <h1>Worker & Cron Setup</h1>
    <p class="text-muted">Use the commands below to run campaign workers reliably on your server.</p>
</div>

<div class="grid gap-4 lg:grid-cols-2">
    <section class="card mb-4">
        <div class="card-header">Route banner</div>
        <div class="card-body">
            <p class="text-sm text-[#526c6a]">Quick access for the admin worker page.</p>
            <div class="mt-4 rounded-2xl bg-[#eef7f1] p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-[#4a6a5d]">Route</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <pre class="w-full overflow-x-auto rounded-xl bg-white p-3 text-sm text-[#1e3b34] shadow-sm"><code id="route-url"><?= esc(base_url('admin/workers')) ?></code></pre>
                    <button type="button" class="copy-button btn btn-secondary" data-copy-target="route-url">Copy route</button>
                </div>
                <div class="mt-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-[#4a6a5d]">Worker command</p>
                    <div class="relative mt-2 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <pre class="overflow-x-auto"><code id="worker-command">php <?= esc(FCPATH) ?>spark campaign:process</code></pre>
                        <button type="button" class="copy-button btn btn-sm btn-secondary absolute right-3 top-3" data-copy-target="worker-command">Copy command</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Status</div>
        <div class="card-body">
            <dl class="grid gap-3 text-sm text-[#526c6a]">
                <div class="rounded-2xl bg-slate-50 p-4 shadow-sm">
                    <dt class="font-semibold">Last worker run</dt>
                    <dd class="mt-1"><?= esc($workerStatus['last_run']) ?></dd>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 shadow-sm">
                    <dt class="font-semibold">Last run age</dt>
                    <dd class="mt-1"><?= $workerStatus['last_run_age_minutes'] !== null ? esc($workerStatus['last_run_age_minutes']) . ' minutes ago' : 'Unknown' ?></dd>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 shadow-sm">
                    <dt class="font-semibold">Log writable</dt>
                    <dd class="mt-1"><?= $workerStatus['logs_writable'] ? 'Yes' : 'No' ?></dd>
                </div>
            </dl>
            <div class="rounded-2xl bg-slate-100 p-4 text-sm text-[#526c6a]">
                <strong>Worker status last checked:</strong> <?= esc($workerStatus['last_run']) ?>
            </div>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <form action="<?= base_url('admin/workers/verify') ?>" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary w-full sm:w-auto">Verify worker</button>
                </form>
                <form action="<?= base_url('admin/workers/verify-cron') ?>" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-secondary w-full sm:w-auto">Verify cron</button>
                </form>
                <form action="<?= base_url('admin/workers/run') ?>" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-success w-full sm:w-auto">Run manually</button>
                </form>
            </div>
            <?php if (session('worker_output')): ?>
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-[#33434a]">
                    <p class="font-semibold">Last manual run output</p>
                    <pre class="whitespace-pre-wrap break-words mt-2"><?= esc(session('worker_output')) ?></pre>
                </div>
            <?php endif ?>
        </div>
    </section>
</div>

<div class="grid gap-4 lg:grid-cols-2">
    <section class="card mb-4">
        <div class="card-header">Cron job instructions</div>
        <div class="card-body">
            <p>To run the scheduler every minute, add this cron entry for the server user:</p>
            <div class="relative rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <pre class="overflow-x-auto"><code id="cron-job-command">* * * * * cd <?= esc(FCPATH) ?> && php spark campaign:process >> <?= esc(WRITEPATH) ?>logs/campaign-worker.log 2>&1</code></pre>
                <button type="button" class="copy-button btn btn-sm btn-secondary absolute right-4 top-4" data-copy-target="cron-job-command">Copy</button>
            </div>
            <p class="mt-4 text-sm text-[#526c6a]">Ensure the server user can write to <code>writable/logs/</code>.</p>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">systemd service example</div>
        <div class="card-body">
            <div class="relative rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <pre class="overflow-x-auto"><code id="systemd-command">[Unit]
Description=EVS Panel Campaign Worker
After=network.target

[Service]
Type=simple
WorkingDirectory=<?= esc(FCPATH) ?>
ExecStart=/usr/bin/php <?= esc(FCPATH) ?>spark campaign:process
Restart=always
RestartSec=10
User=www-data

[Install]
WantedBy=multi-user.target</code></pre>
                <button type="button" class="copy-button btn btn-sm btn-secondary absolute right-4 top-4" data-copy-target="systemd-command">Copy</button>
            </div>
        </div>
    </section>
</div>

<div class="card mb-4">
    <div class="card-header">SMTP Delivery Notes</div>
    <div class="card-body">
        <p>Each user can configure their own SMTP settings in workspace settings. The worker will send queued campaign emails on schedule using the workspace-specific SMTP account.</p>
        <p>Recommended VPS setup:</p>
        <ul class="mt-3 list-disc pl-5 text-sm text-[#526c6a]">
            <li>Use a dedicated Linux server or VM.</li>
            <li>Install PHP 8.x and required extensions.</li>
            <li>Configure <code>cron</code> or <code>systemd</code> to run the worker continuously.</li>
            <li>Ensure outbound SMTP is allowed by your hosting provider.</li>
        </ul>
    </div>
</div>

<div id="copy-toast" class="fixed bottom-6 right-6 z-50 hidden rounded-2xl bg-[#1f4d3b] px-4 py-3 text-sm text-white shadow-soft">Copied to clipboard</div>
<script>
    const toast = document.getElementById('copy-toast');
    document.querySelectorAll('.copy-button').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-copy-target');
            const target = document.getElementById(targetId);
            if (!target) return;
            const text = target.textContent.trim();
            navigator.clipboard.writeText(text).then(() => {
                const original = button.textContent;
                button.textContent = 'Copied';
                toast.classList.remove('hidden');
                setTimeout(() => {
                    button.textContent = original;
                    toast.classList.add('hidden');
                }, 1500);
            });
        });
    });
</script>
