<?php
declare(strict_types=1);
namespace Agca\App\Http;

final class HttpException extends \RuntimeException
{
    public function __construct(public readonly int $statut, string $message = '')
    {
        parent::__construct($message, $statut);
    }
}
