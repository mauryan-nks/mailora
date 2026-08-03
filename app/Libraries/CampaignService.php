<?php

namespace App\Libraries;

use App\Models\CampaignModel;
use RuntimeException;

class CampaignService
{
    private int $workspaceId;
    private array $workspace;
    private EmailService $mailer;
    private ContactService $contactService;

    public function __construct(int $workspaceId, ?int $smtpId = null)
    {
        $this->workspaceId = $workspaceId;
        $this->workspace = db_connect()->table('workspaces')->where('id', $workspaceId)->get()->getRowArray();

        if ($this->workspace === null) {
            throw new RuntimeException('Workspace not found.');
        }

        $this->mailer = new EmailService($workspaceId, $smtpId);
        $this->contactService = new ContactService();
    }

    public function sendTest(int $campaignId, string $toEmail): bool
    {
        $campaign = $this->loadCampaign($campaignId);
        $this->ensureMailerForCampaign($campaign);

        $contact = [
            'uuid' => 'test-' . bin2hex(random_bytes(8)),
            'email' => $toEmail,
            'first_name' => 'Test',
            'last_name' => 'User',
            'company' => '',
        ];

        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('A valid test recipient email is required.');
        }

        if (! $this->mailer->hasConfiguredSmtp()) {
            throw new RuntimeException('Workspace SMTP settings are not configured.');
        }

        $payload = [
            'from_email' => $campaign['from_email'],
            'from_name' => $campaign['from_name'],
            'to_email' => $toEmail,
            'subject' => $campaign['subject'],
            'body_html' => $this->renderHtml($campaign, $contact),
            'body_text' => $this->renderText($campaign, $contact),
        ];

        if (! $this->mailer->send($payload)) {
            throw new RuntimeException('Unable to send test email: ' . $this->mailer->getError());
        }

        return true;
    }

    public function sendCampaign(int $campaignId): array
    {
        $campaign = $this->loadCampaign($campaignId);
        $this->ensureMailerForCampaign($campaign);

        if (! $this->mailer->hasConfiguredSmtp()) {
            throw new RuntimeException('Workspace SMTP settings are not configured.');
        }

        if (! empty($campaign['segment_id'])) {
            $recipients = (new SegmentService($this->workspaceId))->getContactsForSegment((int) $campaign['segment_id']);
        } else {
            $recipients = db_connect()->table('contacts')
                ->where('workspace_id', $this->workspaceId)
                ->where('status', 'subscribed')
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();
        }

        $recipients = array_filter($recipients, fn(array $contact) => $this->contactService->canReceive($contact));
        $total = count($recipients);

        $this->assertEmailLimits($total);

        $counts = ['total' => $total, 'delivered' => 0, 'bounced' => 0];

        foreach ($recipients as $contact) {
            $payload = [
                'from_email' => $campaign['from_email'],
                'from_name' => $campaign['from_name'],
                'to_email' => $contact['email'],
                'subject' => $campaign['subject'],
                'body_html' => $this->renderHtml($campaign, $contact),
                'body_text' => $this->renderText($campaign, $contact),
            ];

            if ($this->mailer->send($payload)) {
                $this->recordEvent((int) $campaign['id'], (int) $contact['id'], 'delivered', null);
                $counts['delivered']++;
            } else {
                $this->recordEvent((int) $campaign['id'], (int) $contact['id'], 'bounce', null);
                $counts['bounced']++;
            }
        }

        db_connect()->table('campaigns')->where('id', $campaign['id'])->update([
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        db_connect()->table('workspaces')->where('id', $this->workspaceId)->set('emails_sent_current_month', 'emails_sent_current_month + ' . $counts['delivered'], false)->update();

        return $counts;
    }

    public function renderHtml(array $campaign, array $contact): string
    {
        $html = $this->loadTemplateContent($campaign);
        $html = $this->replacePlaceholders($html, $campaign, $contact);
        $html = $this->rewriteLinks($html, $campaign['id'], $contact['uuid']);
        $html .= $this->trackingPixel($campaign['id'], $contact['uuid']);

        return $html;
    }

    public function renderText(array $campaign, array $contact): string
    {
        $textSource = ! empty($campaign['template_id'])
            ? strip_tags($this->loadTemplateContent($campaign))
            : ($campaign['content_text'] ?? strip_tags($this->loadTemplateContent($campaign)));

        $text = trim((string) $textSource);
        $text = $this->replacePlaceholders($text, $campaign, $contact);

        if (strpos($text, '{{unsubscribe_url}}') === false) {
            $text .= "\n\nUnsubscribe: " . $this->unsubscribeUrl((int) $campaign['id'], $contact['uuid']);
        }

        return $text;
    }

    private function loadTemplateContent(array $campaign): string
    {
        if (! empty($campaign['template_id'])) {
            $template = db_connect()->table('templates')->where('workspace_id', $this->workspaceId)->where('id', $campaign['template_id'])->get()->getRowArray();
            if ($template && ! empty($template['content_html'])) {
                return $template['content_html'];
            }
        }

        return $campaign['content_html'] ?? '';
    }

    private function replacePlaceholders(string $content, array $campaign, array $contact): string
    {
        $values = [
            '{{first_name}}' => $contact['first_name'] ?? '',
            '{{last_name}}' => $contact['last_name'] ?? '',
            '{{email}}' => $contact['email'] ?? '',
            '{{company}}' => $contact['company'] ?? '',
            '{{unsubscribe_url}}' => $this->unsubscribeUrl((int) $campaign['id'], $contact['uuid']),
        ];

        return str_replace(array_keys($values), array_values($values), $content);
    }

    private function rewriteLinks(string $html, int $campaignId, string $contactUuid): string
    {
        return preg_replace_callback("#<a\s+([^>]*?)href=[\"']([^\"']+)[\"']#i", function ($match) use ($campaignId, $contactUuid) {
            $href = $match[2];
            if (str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, '#')) {
                return $match[0];
            }

            $trackingUrl = $this->trackingBaseUrl() . '/track/click/' . $campaignId . '/' . $contactUuid . '?u=' . rawurlencode(base64_encode($href));
            return str_replace($href, $trackingUrl, $match[0]);
        }, $html) ?: $html;
    }

    private function trackingPixel(int $campaignId, string $contactUuid): string
    {
        return '<img src="' . $this->trackingBaseUrl() . '/track/open/' . $campaignId . '/' . $contactUuid . '" width="1" height="1" style="display:none" alt="" />';
    }

    private function unsubscribeUrl(int $campaignId, string $contactUuid): string
    {
        return $this->trackingBaseUrl() . '/track/unsubscribe/' . $campaignId . '/' . $contactUuid;
    }

    private function trackingBaseUrl(): string
    {
        if (! empty($this->workspace['custom_domain'])) {
            return 'https://' . rtrim($this->workspace['custom_domain'], '/');
        }

        return rtrim(base_url(), '/');
    }

    private function recordEvent(int $campaignId, int $contactId, string $eventType, ?string $url): void
    {
        db_connect()->table('campaign_events')->insert([
            'campaign_id' => $campaignId,
            'contact_id' => $contactId,
            'event_type' => $eventType,
            'url' => $url,
            'occurred_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function assertEmailLimits(int $count): void
    {
        if (! empty($this->workspace['monthly_email_limit']) && $this->workspace['emails_sent_current_month'] + $count > (int) $this->workspace['monthly_email_limit']) {
            throw new RuntimeException('Workspace monthly email limit reached.');
        }

        if (! empty($this->workspace['daily_email_limit'])) {
            $today = date('Y-m-d');
            $sentToday = (int) db_connect()->table('campaign_events ce')
                ->join('campaigns c', 'c.id = ce.campaign_id')
                ->where('c.workspace_id', $this->workspaceId)
                ->where('ce.event_type', 'delivered')
                ->where('DATE(ce.occurred_at)', $today)
                ->countAllResults();

            if ($sentToday + $count > (int) $this->workspace['daily_email_limit']) {
                throw new RuntimeException('Workspace daily email limit reached.');
            }
        }
    }

    private function loadCampaign(int $campaignId): array
    {
        $campaign = (new CampaignModel())->find($campaignId);

        if (! $campaign || (int) $campaign['workspace_id'] !== $this->workspaceId) {
            throw new RuntimeException('Campaign not found in this workspace.');
        }

        return $campaign;
    }

    private function ensureMailerForCampaign(array $campaign): void
    {
        if (! empty($campaign['smtp_id'])) {
            $this->mailer = new EmailService($this->workspaceId, (int) $campaign['smtp_id']);
        }
    }
}
