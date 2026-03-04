<?php

declare(strict_types=1);

namespace App\Validation;

final class UploadedFileValidator
{
    /**
     * @param array<string, mixed> $file
     * @param array<int, string> $allowedMimeTypes
     * @return array{ok: bool, error: string|null}
     */
    public function validate(array $file, int $maxSize, array $allowedMimeTypes): array
    {
        if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'error' => null];
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Falha no upload do arquivo.'];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxSize) {
            return ['ok' => false, 'error' => 'Arquivo invalido ou acima do tamanho permitido.'];
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['ok' => false, 'error' => 'Arquivo temporario invalido.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!is_string($mimeType) || !in_array($mimeType, $allowedMimeTypes, true)) {
            return ['ok' => false, 'error' => 'Tipo de arquivo nao permitido.'];
        }

        return ['ok' => true, 'error' => null];
    }
}
