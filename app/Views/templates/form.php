<?php $template = $template ?? []; $editing = ! empty($template); $editorType = old('editor_type', $template['editor_type'] ?? 'manual'); $sourceUrl = old('source_url', $template['source_url'] ?? ''); ?>
<section class="mt-7">
    <form action="<?= workspace_url($editing ? 'templates/'.$template['id'].'/update' : 'templates/create') ?>" method="post" class="card space-y-4 p-6" id="templateForm">
        <?= csrf_field() ?>

        <div>
            <h2 class="text-lg font-extrabold"><?= $editing ? 'Edit template' : 'New template' ?></h2>
            <p class="text-sm text-[#78908c]">Choose a source and build reusable email HTML templates.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block text-xs font-bold">
                Name
                <input name="name" required class="field mt-2" value="<?= esc(old('name', $template['name'] ?? '')) ?>">
            </label>
            <label class="block text-xs font-bold">
                Category
                <input name="category" class="field mt-2" value="<?= esc(old('category', $template['category'] ?? '')) ?>">
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-3 items-end">
            <label class="block text-xs font-bold">
                Template source
                <select id="editorType" name="editor_type" class="field mt-2">
                    <option value="manual" <?= $editorType === 'manual' ? 'selected' : '' ?>>Manual editor</option>
                    <option value="url" <?= $editorType === 'url' ? 'selected' : '' ?>>Import from URL</option>
                    <option value="upload" <?= $editorType === 'upload' ? 'selected' : '' ?>>Drag & drop HTML</option>
                </select>
            </label>
            <div id="sourceUrlGroup" class="space-y-2" style="<?= $editorType !== 'url' ? 'display:none;' : '' ?>">
                <label class="block text-xs font-bold">Page URL</label>
                <div class="flex gap-2">
                    <input id="sourceUrl" name="source_url" class="field flex-1" value="<?= esc($sourceUrl) ?>" placeholder="https://example.com/newsletter">
                    <button type="button" id="importUrlBtn" class="btn btn-soft inline-flex items-center whitespace-nowrap py-2 px-4">
                        <span class="mr-2">▶</span>Import
                    </button>
                </div>
            </div>
            <div id="uploadGroup" class="space-y-2" style="<?= $editorType !== 'upload' ? 'display:none;' : '' ?>">
                <label class="block text-xs font-bold">HTML file</label>
                <input id="htmlFile" type="file" accept=".html,.htm" class="hidden">
                <div id="dropZone" class="group relative rounded-2xl border border-slate-200 bg-slate-50 px-4 py-8 text-center transition hover:border-slate-400 hover:bg-slate-100">
                    <div class="mx-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm">
                        <span class="text-lg">⇪</span>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-900">Drag HTML file here</p>
                    <p class="mt-1 text-xs text-slate-500">or click to browse and upload</p>
                </div>
            </div>
        </div>

        <div id="editorBlock" class="space-y-3">
            <label class="block text-xs font-bold">
                HTML content
            </label>
            <textarea id="contentHtml" name="content_html" rows="18" class="field mt-2 min-h-[280px] font-mono text-sm bg-slate-50">
<?= esc(old('content_html', $template['content_html'] ?? '<h1>Hello {{first_name}}</h1><p>Start building your email template.</p>')) ?></textarea>
        </div>

        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-bold">Live view</h3>
                <div class="flex gap-2">
                    <button type="button" id="previewModeBtn" class="btn btn-soft btn-sm inline-flex items-center whitespace-nowrap">
                        <span class="mr-2">▶</span>Live view
                    </button>
                    <button type="button" id="refreshBtn" class="btn btn-soft btn-sm">Refresh preview</button>
                </div>
            </div>
            <div id="previewWrapper" class="hidden">
                <div id="iframePreviewWrapper" class="rounded-3xl border border-slate-200 bg-white p-0 relative overflow-hidden">
                    <div class="absolute right-4 top-4 z-20">
                        <button type="button" onclick="exitLivePreview()" class="btn btn-soft btn-xs inline-flex items-center gap-2">
                            <span>←</span>Back to editor
                        </button>
                    </div>
                    <iframe id="templateIframe" sandbox="allow-scripts allow-same-origin" class="w-full min-h-[320px] rounded-b-xl" style="border:none; width:100%; min-height:320px;"></iframe>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= workspace_url('templates') ?>" class="btn btn-soft">Cancel</a>
            <button class="btn btn-primary"><?= $editing ? 'Update template' : 'Save template' ?></button>
        </div>
    </form>
</section>

<script>
    const editorType = document.getElementById('editorType');
    const sourceUrlGroup = document.getElementById('sourceUrlGroup');
    const uploadGroup = document.getElementById('uploadGroup');
    const sourceUrl = document.getElementById('sourceUrl');
    const htmlFile = document.getElementById('htmlFile');
    const dropZone = document.getElementById('dropZone');
    const contentHtml = document.getElementById('contentHtml');
    const importUrlBtn = document.getElementById('importUrlBtn');
    const previewModeBtn = document.getElementById('previewModeBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const previewWrapper = document.getElementById('previewWrapper');
    const iframeWrapper = document.getElementById('iframePreviewWrapper');
    const templateIframe = document.getElementById('templateIframe');
    const editorBlock = document.getElementById('editorBlock');

    function updateEditorVisibility() {
        const mode = editorType.value;
        sourceUrlGroup.style.display = mode === 'url' ? 'block' : 'none';
        uploadGroup.style.display = mode === 'upload' ? 'block' : 'none';
        if (mode !== 'upload') {
            htmlFile.value = '';
        }
    }

    function refreshTemplatePreview() {
        if (previewWrapper.classList.contains('hidden')) {
            enterLivePreview();
            return;
        }
        templateIframe.srcdoc = contentHtml.value;
    }

    function readFile(file) {
        const reader = new FileReader();
        reader.onload = function () {
            contentHtml.value = reader.result;
            refreshTemplatePreview();
        };
        reader.readAsText(file);
    }

    function handleFileSelection(event) {
        const file = event.target.files[0];
        if (file) {
            readFile(file);
        }
    }

    function handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        const file = event.dataTransfer.files[0];
        if (file) {
            readFile(file);
        }
    }

    async function importFromUrl() {
        const url = sourceUrl.value.trim();
        if (!url) {
            alert('Enter a valid URL first.');
            return;
        }

        importUrlBtn.disabled = true;
        importUrlBtn.textContent = 'Importing...';

        const formData = new URLSearchParams();
        formData.append('url', url);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch('<?= workspace_url('templates/import-url') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        importUrlBtn.disabled = false;
        importUrlBtn.textContent = 'Import';

        const data = await response.json();

        if (data.error) {
            alert(data.error);
            return;
        }

        contentHtml.value = data.html;
        refreshTemplatePreview();
    }

    function enterLivePreview() {
        const html = contentHtml.value;
        editorBlock.classList.add('hidden');
        previewWrapper.classList.remove('hidden');
        templateIframe.srcdoc = html;
        previewModeBtn.innerHTML = '<span class="mr-2">✕</span>Back to editor';
        previewModeBtn.classList.remove('btn-soft');
        previewModeBtn.classList.add('btn-danger');
    }

    function exitLivePreview() {
        editorBlock.classList.remove('hidden');
        previewWrapper.classList.add('hidden');
        previewModeBtn.innerHTML = '<span class="mr-2">▶</span>Live view';
        previewModeBtn.classList.remove('btn-danger');
        previewModeBtn.classList.add('btn-soft');
    }

    previewModeBtn.addEventListener('click', function () {
        if (previewWrapper.classList.contains('hidden')) {
            enterLivePreview();
        } else {
            exitLivePreview();
        }
    });

    refreshBtn.addEventListener('click', refreshTemplatePreview);
    editorType.addEventListener('change', updateEditorVisibility);
    contentHtml.addEventListener('input', function () {
        if (!previewWrapper.classList.contains('hidden')) {
            templateIframe.srcdoc = contentHtml.value;
        }
    });
    htmlFile.addEventListener('change', handleFileSelection);
    dropZone.addEventListener('click', function () {
        htmlFile.click();
    });
    dropZone.addEventListener('dragover', function (event) {
        event.preventDefault();
        dropZone.classList.add('border-slate-500');
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('border-slate-500');
    });
    dropZone.addEventListener('drop', handleDrop);
    importUrlBtn.addEventListener('click', importFromUrl);

    updateEditorVisibility();
    refreshTemplatePreview();
</script>
