<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\I18n;
use App\Repositories\UserRepository;

/**
 * Password reset via single-use hashed tokens (valid for 1 hour).
 */
final class PasswordResetService
{
    private const TTL_SECONDS = 3600;

    private Database $db;
    private UserRepository $users;
    private I18n $i18n;
    private Config $config;

    public function __construct(Database $db, UserRepository $users, I18n $i18n, Config $config)
    {
        $this->db = $db;
        $this->users = $users;
        $this->i18n = $i18n;
        $this->config = $config;
    }

    /**
     * Creates a token and sends the mail when the account exists.
     * Returns the reset link only when debug mode is on (local testing), otherwise null.
     * Callers must always show the same neutral message to avoid account enumeration.
     *
     * @param callable(string): string $linkForToken builds the full reset link for a token
     */
    public function request(string $email, callable $linkForToken): ?string
    {
        $user = $this->users->findByEmail($email);
        if ($user === null) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $now = time();
        $this->db->execute(
            'INSERT INTO password_resets (user_id, token_hash, expires_at, used_at, created_at) VALUES (?, ?, ?, NULL, ?)',
            [
                $user->id,
                hash('sha256', $token),
                date('Y-m-d H:i:s', $now + self::TTL_SECONDS),
                date('Y-m-d H:i:s', $now),
            ]
        );

        $link = $linkForToken($token);
        $this->sendMail($user->email, $link);

        return $this->config->isDebug() ? $link : null;
    }

    public function isValid(string $token): bool
    {
        return $this->findActive($token) !== null;
    }

    public function reset(string $token, string $newPassword): bool
    {
        $row = $this->findActive($token);
        if ($row === null) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->beginTransaction();
        $this->db->execute(
            'UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?',
            [password_hash($newPassword, PASSWORD_DEFAULT), $now, (int) $row['user_id']]
        );
        $this->db->execute('UPDATE password_resets SET used_at = ? WHERE user_id = ? AND used_at IS NULL', [$now, (int) $row['user_id']]);
        $this->db->commit();

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findActive(string $token): ?array
    {
        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return null;
        }

        return $this->db->fetchOne(
            'SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > ? LIMIT 1',
            [hash('sha256', $token), date('Y-m-d H:i:s')]
        );
    }

    private function sendMail(string $to, string $link): void
    {
        $from = (string) $this->config->get('app.mail_from', 'noreply@localhost');
        $subject = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($this->i18n->t('auth.mail_subject'), 'UTF-8')
            : $this->i18n->t('auth.mail_subject');
        $body = $this->i18n->t('auth.mail_body', ['link' => $link]);

        $headers = [
            'From: XOrder by AssurIO <' . $from . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        @mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
