<?php

# Строгая типизация
declare(strict_types=1);

namespace App\Exceptions;

/**
 * Специфичное исключение для ошибок HTTP-клиента (аналог Custom Exception)
 */
class HttpClientException extends \RuntimeException 
{    
}