<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Track extends BaseController
{
    public function open(int $campaignId, string $contactUuid)
    {
        $db = db_connect();
        $contact = $db->table('contacts')->where('uuid', $contactUuid)->get()->getRowArray();
        $campaign = $db->table('campaigns')->where('id', $campaignId)->get()->getRowArray();

        if ($contact && $campaign && (int) $contact['workspace_id'] === (int) $campaign['workspace_id']) {
            $db->table('campaign_events')->insert([
                'campaign_id' => $campaignId,
                'contact_id' => (int) $contact['id'],
                'event_type' => 'open',
                'url' => null,
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->pixelResponse();
    }

    public function click(int $campaignId, string $contactUuid)
    {
        $url = $this->request->getGet('u');
        $decoded = $url ? base64_decode($url, true) : false;
        $db = db_connect();
        $contact = $db->table('contacts')->where('uuid', $contactUuid)->get()->getRowArray();
        $campaign = $db->table('campaigns')->where('id', $campaignId)->get()->getRowArray();

        if ($contact && $campaign && $decoded && (int) $contact['workspace_id'] === (int) $campaign['workspace_id']) {
            $db->table('campaign_events')->insert([
                'campaign_id' => $campaignId,
                'contact_id' => (int) $contact['id'],
                'event_type' => 'click',
                'url' => $decoded,
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to($decoded);
        }

        return redirect()->to(base_url('/'));
    }

    public function unsubscribe(int $campaignId, string $contactUuid)
    {
        $db = db_connect();
        $contact = $db->table('contacts')->where('uuid', $contactUuid)->get()->getRowArray();
        $campaign = $db->table('campaigns')->where('id', $campaignId)->get()->getRowArray();

        if ($contact && $campaign && (int) $contact['workspace_id'] === (int) $campaign['workspace_id']) {
            $db->table('contacts')->where('id', (int) $contact['id'])->update(['status' => 'unsubscribed', 'updated_at' => date('Y-m-d H:i:s')]);
            $db->table('campaign_events')->insert([
                'campaign_id' => $campaignId,
                'contact_id' => (int) $contact['id'],
                'event_type' => 'unsubscribe',
                'url' => null,
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setBody('You have been unsubscribed from future messages.');
        }

        return $this->response->setBody('Unable to unsubscribe from this campaign.');
    }

    private function pixelResponse(): ResponseInterface
    {
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        return $this->response->setHeader('Content-Type', 'image/gif')->setBody($gif);
    }
}
