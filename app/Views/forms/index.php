<?php $types = ['all' => 'All', 'embedded' => 'Embedded', 'popup' => 'Popup', 'landing_page' => 'Landing pages']; ?>
<section class="mt-7">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold">Forms & Pages</h2>
            <p class="text-sm text-[#78908c]">Embedded forms, popups and hosted landing pages.</p>
        </div>
        <a href="<?= workspace_url('forms/new') ?>" class="btn btn-primary">New form</a>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <?php foreach ($types as $typeKey => $typeLabel): ?>
            <a href="<?= workspace_url('forms') . ($typeKey === 'all' ? '' : '?type=' . $typeKey) ?>" class="btn <?= $selectedType === $typeKey || ($typeKey === 'all' && empty($selectedType)) ? 'btn-primary' : 'btn-soft' ?> btn-sm"><?= esc($typeLabel) ?> (<?= esc($typeCounts[$typeKey] ?? 0) ?>)</a>
        <?php endforeach ?>
    </div>

    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($forms as $form): ?>
            <article class="card p-6">
                <div class="flex items-start justify-between">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#daf2eb] text-mint">
                        <i data-lucide="<?= $form['form_type'] === 'popup' ? 'popup' : ($form['form_type'] === 'landing_page' ? 'monitor' : 'form') ?>" class="h-5"></i>
                    </span>
                    <span class="rounded-full bg-[#e7f2ef] px-2 py-1 text-xs font-bold text-[#28775f]">
                        <?= esc(ucfirst(str_replace('_', ' ', $form['form_type']))) ?>
                    </span>
                </div>
                <h3 class="mt-5 font-extrabold"><?= esc($form['name']) ?></h3>
                <p class="mt-1 text-sm text-[#78908c]"><?= esc($form['headline']) ?></p>
                <p class="mt-4 text-sm font-bold"><?= number_format($form['submissions']) ?> submissions</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="<?= workspace_url('forms/' . $form['id'] . '/edit') ?>" class="btn btn-secondary">Edit</a>
                    <form action="<?= workspace_url('forms/' . $form['id'] . '/delete') ?>" method="post" class="inline" onsubmit="return confirm('Delete this form?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-soft">Delete</button>
                    </form>
                </div>
            </article>
        <?php endforeach ?>
    </div>
</section>
