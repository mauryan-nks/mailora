<?php $form = $form ?? []; $editing = ! empty($form); $type = old('form_type', $form['form_type'] ?? 'embedded'); $fields = json_decode(old('fields', $form['fields'] ?? '[]'), true) ?: []; ?>
<section class="mt-7">
    <form action="<?= workspace_url($editing ? 'forms/'.$form['id'].'/update' : 'forms/create') ?>" method="post" class="card space-y-6 p-6">
        <?= csrf_field() ?>

        <div>
            <h2 class="text-lg font-extrabold"><?= $editing ? 'Edit form' : 'New form' ?></h2>
            <p class="text-sm text-[#78908c]">Create a form or landing page that matches your campaign flow.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block text-xs font-bold">
                Name
                <input name="name" required class="field mt-2" value="<?= esc(old('name', $form['name'] ?? '')) ?>">
            </label>
            <label class="block text-xs font-bold">
                Form type
                <select name="form_type" class="field mt-2">
                    <option value="embedded" <?= $type === 'embedded' ? 'selected' : '' ?>>Embedded</option>
                    <option value="popup" <?= $type === 'popup' ? 'selected' : '' ?>>Popup</option>
                    <option value="landing_page" <?= $type === 'landing_page' ? 'selected' : '' ?>>Landing page</option>
                </select>
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block text-xs font-bold">
                Headline
                <input name="headline" class="field mt-2" value="<?= esc(old('headline', $form['headline'] ?? '')) ?>">
            </label>
            <label class="block text-xs font-bold">
                Slug
                <input name="slug" class="field mt-2" value="<?= esc(old('slug', $form['slug'] ?? '')) ?>" placeholder="form-name">
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block text-xs font-bold">
                Status
                <select name="status" class="field mt-2">
                    <option value="draft" <?= old('status', $form['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= old('status', $form['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </label>
            <label class="block text-xs font-bold">
                Fields
                <input name="fields" class="field mt-2" value="<?= esc(join(',', $fields)) ?>" placeholder="email, first_name, phone">
                <p class="mt-2 text-xs text-[#78908c]">Comma-separated form fields.</p>
            </label>
        </div>

        <section class="card rounded-3xl border border-slate-200 bg-slate-50 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-extrabold">Design settings</h3>
                    <p class="text-sm text-[#78908c]">Choose a popup style, colors, and parallax look to match your site.</p>
                </div>
                <span class="rounded-full bg-[#e5f1ee] px-3 py-2 text-xs font-bold text-[#28775f]">Live style</span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="block text-xs font-bold">
                    Preset style
                    <input type="hidden" id="designStyleInput" name="design_style" value="<?= esc(old('design_style', $form['design_style'] ?? 'classic')) ?>">
                    <?php $selectedStyle = old('design_style', $form['design_style'] ?? 'classic'); ?>
                    <div id="designPresetOptions" class="mt-3 grid gap-3 sm:grid-cols-2">
                        <button type="button" data-style="classic" class="preset-card group rounded-3xl border p-4 text-left transition<?= $selectedStyle === 'classic' ? ' border-slate-900 bg-slate-900/5' : ' border-slate-200 bg-white' ?>">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">Classic</p>
                                    <p class="mt-1 text-xs text-[#78908c]">White surface with soft border and shadow.</p>
                                </div>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#44B89D] text-white text-xs">A</span>
                            </div>
                            <div class="mt-4 h-24 rounded-3xl border border-slate-200 bg-white p-3 shadow-sm">
                                <div class="h-full rounded-2xl bg-slate-50 p-3">
                                    <div class="h-2.5 w-10 rounded-full bg-slate-300"></div>
                                    <div class="mt-3 h-12 rounded-2xl bg-slate-100"></div>
                                </div>
                            </div>
                        </button>
                        <button type="button" data-style="glass" class="preset-card group rounded-3xl border p-4 text-left transition<?= $selectedStyle === 'glass' ? ' border-slate-900 bg-slate-900/5' : ' border-slate-200 bg-white' ?>">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">Glass</p>
                                    <p class="mt-1 text-xs text-[#78908c]">Translucent panel with blurred glass effect.</p>
                                </div>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#60A5FA] text-white text-xs">B</span>
                            </div>
                            <div class="mt-4 h-24 rounded-3xl border border-slate-200 bg-slate-100/70 p-3 backdrop-blur-sm">
                                <div class="h-2.5 w-10 rounded-full bg-slate-300/70"></div>
                                <div class="mt-3 h-12 rounded-2xl bg-white/70"></div>
                            </div>
                        </button>
                        <button type="button" data-style="bold" class="preset-card group rounded-3xl border p-4 text-left transition<?= $selectedStyle === 'bold' ? ' border-slate-900 bg-slate-900/5' : ' border-slate-200 bg-white' ?>">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">Bold</p>
                                    <p class="mt-1 text-xs text-[#78908c]">Dark background with strong accent and contrast.</p>
                                </div>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#EAB308] text-slate-900 text-xs">C</span>
                            </div>
                            <div class="mt-4 h-24 rounded-3xl bg-slate-950 p-3 text-white">
                                <div class="h-2.5 w-10 rounded-full bg-white/20"></div>
                                <div class="mt-3 h-12 rounded-2xl bg-white/10"></div>
                            </div>
                        </button>
                        <button type="button" data-style="minimal" class="preset-card group rounded-3xl border p-4 text-left transition<?= $selectedStyle === 'minimal' ? ' border-slate-900 bg-slate-900/5' : ' border-slate-200 bg-white' ?>">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">Minimal</p>
                                    <p class="mt-1 text-xs text-[#78908c]">Clean layout with subtle spacing and light tones.</p>
                                </div>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#CBD5E1] text-slate-900 text-xs">D</span>
                            </div>
                            <div class="mt-4 h-24 rounded-3xl border border-slate-200 bg-[#f8fafc] p-3">
                                <div class="h-2.5 w-10 rounded-full bg-slate-300"></div>
                                <div class="mt-3 h-12 rounded-2xl bg-white"></div>
                            </div>
                        </button>
                    </div>
                </label>
                <label class="block text-xs font-bold">
                    Background color
                    <input type="color" id="backgroundColor" name="background_color" class="field mt-2 h-12 p-0" value="<?= esc(old('background_color', $form['background_color'] ?? '#ffffff')) ?>">
                </label>
                <label class="block text-xs font-bold">
                    Accent color
                    <input type="color" id="accentColor" name="accent_color" class="field mt-2 h-12 p-0" value="<?= esc(old('accent_color', $form['accent_color'] ?? '#44B89D')) ?>">
                </label>
                <label class="inline-flex items-center gap-2 text-xs font-bold pt-6">
                    <input type="checkbox" id="parallaxToggle" name="parallax" value="1" <?= old('parallax', $form['parallax'] ?? 0) ? 'checked' : '' ?> class="form-checkbox">
                    Parallax background
                </label>
            </div>

            <div id="designPreview" class="mt-6 rounded-3xl border p-5 shadow-soft transition-all duration-300">
                <div class="rounded-3xl p-5" id="designPreviewCard">
                    <p class="text-sm font-semibold">Popup preview</p>
                    <h4 class="mt-2 text-lg font-extrabold"><?= esc(old('headline', $form['headline'] ?? 'Your form headline')) ?></h4>
                    <p class="mt-2 text-sm text-[#5b6b68]">A live preview of the selected style settings.</p>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="<?= workspace_url('forms') ?>" class="btn btn-soft">Cancel</a>
            <button class="btn btn-primary"><?= $editing ? 'Update form' : 'Create form' ?></button>
        </div>
    </form>
</section>

<script>
    const designStyleInput = document.getElementById('designStyleInput');
    const presetButtons = document.querySelectorAll('[data-style]');
    const backgroundColor = document.getElementById('backgroundColor');
    const accentColor = document.getElementById('accentColor');
    const parallaxToggle = document.getElementById('parallaxToggle');
    const designPreview = document.getElementById('designPreview');
    const designPreviewCard = document.getElementById('designPreviewCard');

    const styleMap = {
        classic: {
            cardBg: '#ffffff',
            cardBorder: '1px solid rgba(148,163,184,.25)',
            cardShadow: '0 20px 45px rgba(15,23,42,.08)',
            textColor: '#0f172a',
        },
        glass: {
            cardBg: 'rgba(255,255,255,.72)',
            cardBorder: '1px solid rgba(148,163,184,.18)',
            cardShadow: '0 20px 45px rgba(15,23,42,.05)',
            textColor: '#0f172a',
        },
        bold: {
            cardBg: '#0f172a',
            cardBorder: 'none',
            cardShadow: '0 25px 60px rgba(15,23,42,.25)',
            textColor: '#ffffff',
        },
        minimal: {
            cardBg: '#f8fafc',
            cardBorder: '1px solid rgba(148,163,184,.18)',
            cardShadow: '0 10px 25px rgba(15,23,42,.04)',
            textColor: '#0f172a',
        },
    };

    function updatePresetSelection() {
        const selected = designStyleInput.value;
        presetButtons.forEach((button) => {
            button.classList.toggle('border-slate-900', button.dataset.style === selected);
            button.classList.toggle('bg-slate-900/5', button.dataset.style === selected);
            button.classList.toggle('border-slate-200', button.dataset.style !== selected);
            button.classList.toggle('bg-white', button.dataset.style !== selected);
        });
    }

    function selectPreset(style) {
        designStyleInput.value = style;
        updatePresetSelection();
        updatePreview();
    }

    function updatePreview() {
        const selectedStyle = designStyleInput.value;
        const style = styleMap[selectedStyle] || styleMap.classic;
        const pageBg = backgroundColor.value;
        const accent = accentColor.value;
        const parallax = parallaxToggle.checked;

        designPreview.style.background = pageBg;
        designPreviewCard.style.background = style.cardBg;
        designPreviewCard.style.border = style.cardBorder;
        designPreviewCard.style.boxShadow = style.cardShadow;
        designPreviewCard.style.color = style.textColor;
        designPreviewCard.style.borderColor = accent;
        designPreviewCard.style.borderWidth = style.cardBorder === 'none' ? '0' : '1px';
        designPreviewCard.style.borderStyle = style.cardBorder === 'none' ? 'none' : 'solid';
        designPreviewCard.style.backgroundImage = parallax
            ? 'radial-gradient(circle at top left, rgba(255,255,255,.3), transparent 25%), radial-gradient(circle at bottom right, rgba(255,255,255,.18), transparent 20%)'
            : 'none';
        designPreviewCard.style.backgroundBlendMode = parallax ? 'screen' : 'normal';

        if (selectedStyle === 'bold') {
            designPreviewCard.style.background = accent;
            designPreviewCard.style.color = '#ffffff';
        }
    }

    presetButtons?.forEach((button) => {
        button.addEventListener('click', () => selectPreset(button.dataset.style));
    });
    backgroundColor?.addEventListener('change', updatePreview);
    accentColor?.addEventListener('change', updatePreview);
    parallaxToggle?.addEventListener('change', updatePreview);

    updatePresetSelection();
    updatePreview();
</script>
