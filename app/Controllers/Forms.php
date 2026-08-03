<?php
namespace App\Controllers;

use App\Models\FormModel;

class Forms extends BaseController
{
    public function index(): string
    {
        $selectedType = $this->request->getGet('type');
        $db = db_connect();
        $query = $db->table('forms')->where('workspace_id', $this->workspaceId);

        if (in_array($selectedType, ['embedded', 'popup', 'landing_page'], true)) {
            $query->where('form_type', $selectedType);
        }

        $forms = $query->orderBy('id', 'DESC')->get()->getResultArray();
        $counts = $db->table('forms')
            ->select('form_type, COUNT(*) as total')
            ->where('workspace_id', $this->workspaceId)
            ->groupBy('form_type')
            ->get()
            ->getResultArray();

        $typeCounts = array_column($counts, 'total', 'form_type');
        $typeCounts['all'] = array_sum($typeCounts);

        return $this->page('forms/index', [
            'title' => 'Forms & Pages',
            'active' => 'forms',
            'subtitle' => 'Embedded forms, popups and hosted landing pages',
            'forms' => $forms,
            'selectedType' => $selectedType,
            'typeCounts' => $typeCounts,
        ]);
    }

    public function new(): string
    {
        return $this->page('forms/form', [
            'title' => 'New form',
            'active' => 'forms',
            'form' => null,
        ]);
    }

    public function create()
    {
        helper(['text', 'url']);

        $model = new FormModel();
        $data = $this->payload(true);

        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(workspace_url('forms'))->with('success', 'Form created.');
    }

    public function edit(int $id): string
    {
        $model = new FormModel();
        $form = $model->where('workspace_id', $this->workspaceId)->find($id);

        if (! $form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->page('forms/form', [
            'title' => 'Edit form',
            'active' => 'forms',
            'form' => $form,
        ]);
    }

    public function update(int $id)
    {
        helper(['text', 'url']);

        $model = new FormModel();
        $existing = $model->find($id);

        if (! $existing || (int) $existing['workspace_id'] !== $this->workspaceId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->payload(false, $id);

        if (! $model->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $model->errors()));
        }

        return redirect()->to(workspace_url('forms'))->with('success', 'Form updated.');
    }

    public function delete(int $id)
    {
        $model = new FormModel();
        $form = $model->find($id);

        if (! $form || (int) $form['workspace_id'] !== $this->workspaceId) {
            return redirect()->back()->with('error', 'Form not found.');
        }

        $model->delete($id);

        return redirect()->to(workspace_url('forms'))->with('success', 'Form deleted.');
    }

    private function payload(bool $isNew = false, ?int $id = null): array
    {
        $name = trim((string) $this->request->getPost('name'));
        $slug = trim((string) $this->request->getPost('slug')) ?: url_title($name, '-', true) ?: 'form';
        $slug = $this->uniqueSlug($slug, $id);

        $rawFields = $this->request->getPost('fields');
        $fields = is_array($rawFields)
            ? array_values(array_filter($rawFields))
            : array_values(array_filter(array_map('trim', explode(',', (string) $rawFields))));

        $data = [
            'workspace_id' => $this->workspaceId,
            'name' => $name,
            'form_type' => $this->request->getPost('form_type') ?: 'embedded',
            'slug' => $slug,
            'headline' => $this->request->getPost('headline'),
            'fields' => json_encode($fields),
            'status' => $this->request->getPost('status') ?: 'draft',
            'design_style' => $this->request->getPost('design_style') ?: 'classic',
            'background_color' => $this->request->getPost('background_color') ?: '#ffffff',
            'accent_color' => $this->request->getPost('accent_color') ?: '#44B89D',
            'parallax' => $this->request->getPost('parallax') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($isNew) {
            $data['submissions'] = 0;
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    private function uniqueSlug(string $slug, ?int $id = null): string
    {
        $db = db_connect();
        $baseSlug = $slug;
        $suffix = 1;

        while (true) {
            $query = $db->table('forms')
                ->where('workspace_id', $this->workspaceId)
                ->where('slug', $slug);

            if ($id !== null) {
                $query->where('id !=', $id);
            }

            if ($query->countAllResults() === 0) {
                break;
            }

            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
