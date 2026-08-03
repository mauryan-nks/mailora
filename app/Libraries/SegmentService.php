<?php

namespace App\Libraries;

use App\Models\ContactModel;
use App\Models\SegmentModel;
use RuntimeException;

class SegmentService
{
    private int $workspaceId;
    private SegmentModel $segmentModel;
    private ContactModel $contactModel;

    public function __construct(int $workspaceId)
    {
        $this->workspaceId = $workspaceId;
        $this->segmentModel = new SegmentModel();
        $this->contactModel = new ContactModel();
    }

    public function getContactsForSegment(int $segmentId): array
    {
        $segment = $this->segmentModel->where('workspace_id', $this->workspaceId)->find($segmentId);

        if (! $segment) {
            throw new RuntimeException('Segment not found.');
        }

        $rules = json_decode($segment['rules'] ?? '[]', true);
        $builder = db_connect()->table('contacts')->where('workspace_id', $this->workspaceId)->where('status', 'subscribed')->where('deleted_at', null);

        foreach ($rules as $rule) {
            if (! isset($rule['field'], $rule['operator'], $rule['value'])) {
                continue;
            }

            $field = $rule['field'];
            $operator = $rule['operator'];
            $value = $rule['value'];

            switch ($operator) {
                case 'equals':
                    $builder->where($field, $value);
                    break;
                case 'contains':
                    $builder->like($field, $value);
                    break;
                case 'starts_with':
                    $builder->like($field, $value, 'after');
                    break;
                case 'ends_with':
                    $builder->like($field, $value, 'before');
                    break;
                default:
                    break;
            }
        }

        return $builder->get()->getResultArray();
    }
}
