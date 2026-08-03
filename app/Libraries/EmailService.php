<?php

namespace App\Libraries;

use App\Models\SmtpAccountModel;
use CodeIgniter\Email\Email;
use Config\Services;
use RuntimeException;

class EmailService
{
    private int $workspaceId;
    private ?int $smtpAccountId;
    private ?array $workspace = null;
    private ?array $smtpAccount = null;
    private string $lastError = '';

    public function __construct(int $workspaceId, ?int $smtpAccountId = null)
    {
        $this->workspaceId = $workspaceId;
        $this->smtpAccountId = $smtpAccountId;
        $this->workspace = db_connect()->table('workspaces')->where('id', $workspaceId)->get()->getRowArray();
        $this->smtpAccount = $this->loadSmtpAccount();
    }

    public function hasConfiguredSmtp(): bool
    {
        return $this->smtpAccount !== null && ! empty($this->smtpAccount['host']) && ! empty($this->smtpAccount['from_email']);
    }

    public function getAccounts(): array
    {
        return (new SmtpAccountModel())
            ->where('workspace_id', $this->workspaceId)
            ->orderBy('is_active', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    private function loadSmtpAccount(): ?array
    {
        if ($this->smtpAccountId !== null) {
            $account = (new SmtpAccountModel())
                ->where('workspace_id', $this->workspaceId)
                ->where('id', $this->smtpAccountId)
                ->first();

            if ($account) {
                return $account;
            }
        }

        $account = (new SmtpAccountModel())
            ->where('workspace_id', $this->workspaceId)
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->first();

        if ($account) {
            return $account;
        }

        return (new SmtpAccountModel())
            ->where('workspace_id', $this->workspaceId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function send(array $payload): bool
    {
        $email = $this->createEmailInstance();
        $email->setFrom($payload['from_email'], $payload['from_name']);
        $email->setTo($payload['to_email']);
        $email->setSubject($payload['subject']);
        $email->setMessage($payload['body_html']);
        $email->setAltMessage($payload['body_text']);

        if (! empty($payload['reply_to'])) {
            $email->setReplyTo($payload['reply_to']);
        }

        if (! $email->send(false)) {
            $this->lastError = $email->printDebugger(['headers']);
            return false;
        }

        return true;
    }

    public function getError(): string
    {
        return $this->lastError;
    }

    private function createEmailInstance(): Email
    {
        if (! $this->hasConfiguredSmtp()) {
            throw new RuntimeException('Workspace SMTP settings are not configured.');
        }

        $config = $this->buildConfig();

        return Services::email($config, false);
    }

    private function buildConfig(): array
    {
        $smtp = $this->smtpAccount;
        $password = $this->decryptPassword($smtp['encrypted_password'] ?? null);

        return [
            'protocol' => 'smtp',
            'SMTPHost' => $smtp['host'] ?? '',
            'SMTPUser' => $smtp['username'] ?? '',
            'SMTPPass' => $password,
            'SMTPPort' => (int) ($smtp['port'] ?? 587),
            'SMTPTimeout' => 30,
            'SMTPKeepAlive' => false,
            'SMTPCrypto' => $smtp['encryption'] ?: 'tls',
            'mailType' => 'html',
            'charset' => 'UTF-8',
            'wordWrap' => false,
            'newline' => "
",
            'crlf' => "
",
        ];
    }

    private function decryptPassword(?string $cipher): string
    {
        if (empty($cipher)) {
            return '';
        }

        try {
            return service('encrypter')->decrypt($cipher);
        } catch (\Throwable $e) {
            return '';
        }
    }
}
