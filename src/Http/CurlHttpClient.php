<?php

# Строгая типизация
declare(strict_types=1);

# Пространство имен
namespace App\Http;

// аналог using
use App\Interfaces\HttpClientInterface;
use App\Exceptions\HttpClientException;

/**
 * Реализация HTTP-клиента на базе cURL (аналог обертки над HttpClient)
 */
final class CurlHttpClient implements HttpClientInterface
{
    # Таймаут запроса (с)
    private int $timeout;
    
    /**
     * Конструктор
     * @param int $timeout Таймаут запроса
     */
    public function __construct (int $timeout = 10)
    {
        // Присваиваем в таймаут полученное значение
        $this->timeout = $timeout;
    }

    #[\Override]
    public function get(string $url): array {
        
        // Инициализация cURL-сессии ( new HttpRequestMessage)
        $ch = curl_init($url);
        
        if ($ch === false)
        {
            throw new HttpClientException('Не удалось инициализировать cURL');
        }
        
        // Настройка опций (аналог - настройка HttpRequestMessage, HttpClient)
        curl_setopt_array($ch,
                [
                    // Возвращение результата как строки, без вывода в браузер. Иначе - вернет true/false, без тела ответа
                    CURLOPT_RETURNTRANSFER => true,
                    // Таймаут выполнения 
                    CURLOPT_TIMEOUT => $this->timeout,
                    // Без проверки SSL-сертификата 
                    CURLOPT_SSL_VERIFYPEER => false,
                    // Заголовки запроса ( аналог - request.Headers.Add("..."))
                    CURLOPT_HTTPHEADER =>
                    [
                        'Accept: application/json',
                        'User-Agent: Php-Curl-Client/1.0'
                    ],
                ]);
        
        // Выполнение запроса (httpClient.SendAsync)
        $response = curl_exec($ch);
        
        // Обработка ошибок сети 
        if ($response === false)
        {
            // Код ошибки последней операции
            $errorCode = curl_errno($ch);
            // Сообщение ошибки (+- ex.Message)
            $errorMessage = curl_error($ch);
            // Освобождение ресурсов (Dispose)
            curl_close($ch);
            
            throw new HttpClientException("cURL Error [{$errorCode}]: {$errorMessage}", $errorCode);
        }
        
        // Проверка HTTP-статуса (аналог - response.EnsureSuccessStatusCode)
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Закрытие соединения (аналогично .Close())
        curl_close($ch);
        
        // Если статус запроса не соответстует успешным (Success, Accepted и т.п.)
        if ($httpCode < 200 || $httpCode >= 300)
        {
            throw  new HttpClientException(
                "HTTP Request failed with status code: {$httpCode}. Response: {$response}",
                $httpCode                    
            );
        }
        
        // Json декодирование ()        
        try
        {
            // $response - запрос, ответ которого декодируем
            // true - (?bool $associative) - превращение jsonки в ассоциативный массив php (аналог - Dictionary <string, object>)
            // если false (либо оставить null(по умолчанию)) - возвращает объект stdClass (аналог - dynamic)
            // 512 - (int $depth) - максимальная глубина вложенности структуры для декодирования
            // JSON_THROW_ON_ERROR - выброс JsonException при невалидном json, вместо возврата null. Делает поведение функции схожей с JsonSerializer.Deserialize<T>(json, options)
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            
        } 
        catch (\JsonException $ex)
        {
            throw new HttpClientException("Invalid JSON response: ". $ex->getMessage(), 0, $ex);
        }
        
        // Возвращаем результат
        // Проверка и гарантия возвращения массива на случай, если json оказался [] или {}
        // тернарная операция
        return is_array($data) ? $data : [];
    }    
}