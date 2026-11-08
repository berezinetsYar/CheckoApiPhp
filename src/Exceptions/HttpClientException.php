<?php
declare(strict_types=1);
# Строгая типизация

namespace App\Exceptions;

/**
 * Специфичное исключение для ошибок HTTP-клиента (аналог Custom Exception)
 */
class HttpClientException extends \RuntimeException 
{    
}