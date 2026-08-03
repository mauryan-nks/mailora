<?php
namespace App\Controllers;

use App\Models\TemplateModel;
use Config\Services;

class Templates extends BaseController
{
    public function index(): string
    {
        $model = new TemplateModel();

        return $this->page('templates/index', [
            'title' => 'Templates',
            'active' => 'templates',
            'templates' => $model->where('workspace_id', $this->workspaceId)->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function new(): string
    {
        return $this->page('templates/form', [
            'title' => 'New Template',
            'active' => 'templates',
            'template' => null,
        ]);
    }

    public function create()
    {
        $model = new TemplateModel();
        $data = $this->payload();

        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(workspace_url('templates'))->with('success', 'Template saved.');
    }

    public function edit(int $id): string
    {
        $model = new TemplateModel();
        $template = $model->where('workspace_id', $this->workspaceId)->find($id);

        if (! $template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->page('templates/form', [
            'title' => 'Edit Template',
            'active' => 'templates',
            'template' => $template,
        ]);
    }

    public function update(int $id)
    {
        $model = new TemplateModel();
        $template = $model->find($id);

        if (! $template || (int) $template['workspace_id'] !== $this->workspaceId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $model->update($id, $this->payload())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(workspace_url('templates'))->with('success', 'Template updated.');
    }

    public function delete(int $id)
    {
        $model = new TemplateModel();
        $template = $model->find($id);

        if (! $template || (int) $template['workspace_id'] !== $this->workspaceId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $model->delete($id);

        return redirect()->to(workspace_url('templates'))->with('success', 'Template deleted.');
    }

    public function importUrl()
    {
        $url = (string) $this->request->getPost('url');

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->response->setJSON(['error' => 'Enter a valid URL.']);
        }

        try {
            $client = Services::curlrequest([
                'timeout' => 30,
                'allow_redirects' => true,
                'verify' => false,
            ]);

            $response = $client->get($url);

            if ($response->getStatusCode() !== 200) {
                return $this->response->setJSON(['error' => 'Unable to fetch the page.']);
            }

            $html = (string) $response->getBody();
            $html = $this->inlineAssets($url, $html);

            return $this->response->setJSON(['html' => $html]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['error' => 'Unable to import URL: ' . $e->getMessage()]);
        }
    }

    private function inlineAssets(string $baseUrl, string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $links = [];
        foreach ($dom->getElementsByTagName('link') as $link) {
            $links[] = $link;
        }

        foreach ($links as $link) {
            if (strtolower($link->getAttribute('rel')) !== 'stylesheet' || ! $link->hasAttribute('href')) {
                continue;
            }

            $href = $this->absoluteUrl($link->getAttribute('href'), $baseUrl);

            try {
                $css = Services::curlrequest(['timeout' => 20, 'verify' => false])->get($href)->getBody();
                $styleNode = $dom->createElement('style', $css);
                $link->parentNode->replaceChild($styleNode, $link);
            } catch (\Throwable $e) {
                // Keep original link if inline fetch fails.
            }
        }

        $scripts = [];
        foreach ($dom->getElementsByTagName('script') as $script) {
            $scripts[] = $script;
        }

        foreach ($scripts as $script) {
            if (! $script->hasAttribute('src')) {
                continue;
            }

            $src = $this->absoluteUrl($script->getAttribute('src'), $baseUrl);

            try {
                $js = Services::curlrequest(['timeout' => 20, 'verify' => false])->get($src)->getBody();
                $newScript = $dom->createElement('script', $js);
                $script->removeAttribute('src');
                while ($script->firstChild) {
                    $script->removeChild($script->firstChild);
                }
                $script->appendChild($dom->createTextNode($js));
            } catch (\Throwable $e) {
                // Leave original script tag intact.
            }
        }

        return $dom->saveHTML();
    }

    private function absoluteUrl(string $relativeOrAbsolute, string $base): string
    {
        if (parse_url($relativeOrAbsolute, PHP_URL_SCHEME)) {
            return $relativeOrAbsolute;
        }

        $baseParts = parse_url($base);
        if (! $baseParts || empty($baseParts['scheme']) || empty($baseParts['host'])) {
            return $relativeOrAbsolute;
        }

        $scheme = $baseParts['scheme'];
        $host = $baseParts['host'];
        $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';

        if (str_starts_with($relativeOrAbsolute, '//')) {
            return $scheme . ':' . $relativeOrAbsolute;
        }

        if (str_starts_with($relativeOrAbsolute, '/')) {
            return sprintf('%s://%s%s%s', $scheme, $host, $port, $relativeOrAbsolute);
        }

        $path = isset($baseParts['path']) ? rtrim(dirname($baseParts['path']), '/') . '/' : '/';

        return sprintf('%s://%s%s%s%s', $scheme, $host, $port, $path, ltrim($relativeOrAbsolute, '/'));
    }

    private function payload(): array
    {
        return [
            'workspace_id' => $this->workspaceId,
            'name' => (string) $this->request->getPost('name'),
            'category' => (string) $this->request->getPost('category'),
            'content_html' => (string) $this->request->getPost('content_html'),
            'editor_type' => (string) ($this->request->getPost('editor_type') ?: 'manual'),
            'source_url' => (string) $this->request->getPost('source_url'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
