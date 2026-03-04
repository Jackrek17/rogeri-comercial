<?php

declare(strict_types=1);

namespace App\Validation;

final class ContactFormValidator
{
    /**
     * @var array<int, string>
     */
    private array $blockedPatterns = [
        '/<\s*script\b/i',
        '/javascript\s*:/i',
        '/\bon\w+\s*=\s*/i',
        '/\b(select|insert|update|delete|drop|union|truncate|alter)\b\s+/i',
        '/(--|\/\*|\*\/|;)/',
    ];

    /**
     * @param array<string, mixed> $input
     * @return array{clean: array<string, string>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        $clean = [
            'name' => $this->cleanText($input['name'] ?? ''),
            'company' => $this->cleanText($input['company'] ?? ''),
            'whatsapp' => $this->cleanPhone($input['whatsapp'] ?? ''),
            'email' => $this->cleanEmail($input['email'] ?? ''),
            'project_volume' => $this->cleanText($input['project_volume'] ?? ''),
            'message' => $this->cleanMessage($input['message'] ?? ''),
        ];

        $errors = [];

        if ($clean['name'] === '' || mb_strlen($clean['name']) < 2) {
            $errors['name'] = 'Nome invalido.';
        }

        if ($clean['company'] === '' || mb_strlen($clean['company']) < 2) {
            $errors['company'] = 'Empresa invalida.';
        }

        if (!preg_match('/^\+?[0-9\s\-()]{10,20}$/', $clean['whatsapp'])) {
            $errors['whatsapp'] = 'WhatsApp invalido.';
        }

        if ($clean['email'] === '') {
            $errors['email'] = 'Email invalido.';
        }

        $allowedVolumes = [
            'Grande (Adutoras / Grandes interceptores / Estatais)',
            'Médio (Sistemas municipais)',
            'Pequeno (Redes locais / Loteamentos)',
        ];

        if (!in_array($clean['project_volume'], $allowedVolumes, true)) {
            $errors['project_volume'] = 'Volume da obra invalido.';
        }

        if ($clean['message'] !== '' && mb_strlen($clean['message']) < 5) {
            $errors['message'] = 'Mensagem invalida. Minimo de 5 caracteres.';
        }

        foreach ($clean as $field => $value) {
            if ($value !== '' && $this->containsBlockedPattern($value)) {
                $errors[$field] = 'Conteudo suspeito detectado no campo ' . $field . '.';
            }
        }

        return ['clean' => $clean, 'errors' => $errors];
    }

    private function cleanText(mixed $value): string
    {
        $text = trim((string) $value);
        $text = strip_tags($text);
        return preg_replace('/\s+/', ' ', $text) ?? '';
    }

    private function cleanMessage(mixed $value): string
    {
        $text = $this->cleanText($value);
        return mb_substr($text, 0, 2000);
    }

    private function cleanPhone(mixed $value): string
    {
        return $this->cleanText($value);
    }

    private function cleanEmail(mixed $value): string
    {
        $email = trim((string) $value);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!is_string($email)) {
            return '';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '';
        }

        return $email;
    }

    private function containsBlockedPattern(string $value): bool
    {
        foreach ($this->blockedPatterns as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
