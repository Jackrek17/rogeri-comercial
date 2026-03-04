<?php

declare(strict_types=1);

use App\Controller\ContactController;
use App\Http\Request;
use App\Storage\FileSubmissionStore;
use App\Validation\ContactFormValidator;
use App\Validation\UploadedFileValidator;

require __DIR__ . '/bootstrap.php';

/** @var array<string, mixed> $config */
$controller = new ContactController(
    new Request(),
    new ContactFormValidator(),
    new UploadedFileValidator(),
    new FileSubmissionStore($config['form']),
    $config
);

$controller->handle();
