<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Storage\SubmissionStoreInterface;
use App\Support\ApiResponse;
use App\Validation\ContactFormValidator;
use App\Validation\UploadedFileValidator;

final class ContactController
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private Request $request,
        private ContactFormValidator $validator,
        private UploadedFileValidator $fileValidator,
        private SubmissionStoreInterface $store,
        private array $config
    ) {
    }

    public function handle(): void
    {
        if ($this->request->method() !== 'POST') {
            ApiResponse::json(405, [
                'ok' => false,
                'message' => 'Metodo nao permitido.',
            ]);
            return;
        }

        $result = $this->validator->validate($this->request->input());
        $clean = $result['clean'];
        $errors = $result['errors'];

        $files = $this->request->files();
        $file = isset($files['attachment']) && is_array($files['attachment'])
            ? $files['attachment']
            : null;

        if ($file !== null) {
            $fileCheck = $this->fileValidator->validate(
                $file,
                (int) $this->config['form']['max_attachment_size'],
                $this->config['form']['allowed_mime_types']
            );

            if (!$fileCheck['ok']) {
                $errors['attachment'] = (string) $fileCheck['error'];
            }
        }

        if ($errors !== []) {
            ApiResponse::json(422, [
                'ok' => false,
                'message' => 'Erro de validacao.',
                'errors' => $errors,
            ]);
            return;
        }

        $saved = $this->store->save($clean, $file);
        if (!$saved) {
            ApiResponse::json(500, [
                'ok' => false,
                'message' => 'Nao foi possivel salvar o formulario.',
            ]);
            return;
        }

        ApiResponse::json(200, [
            'ok' => true,
            'message' => 'Formulario enviado com sucesso.',
        ]);
    }
}
