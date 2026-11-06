<?php

# Строгая типизация
declare(strict_types=1);

# Пространство имен
namespace App\Interfaces;

/**
 *  Контракт для HTTP-клиента. Аналог IHttpClient.
 */
interface HttpClientInterface
{
    /**
     * Выполнение GET запроса.
     * @param string $url URL для запроса
     * @return array Декодированные данные (ассоциативный массив)
     * @throws \Exception Если запрос не удался или ответ не является валидным JSON
     */
    public function get(string $url): array;
}
