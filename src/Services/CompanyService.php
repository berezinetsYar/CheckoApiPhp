<?php
declare(strict_types=1);
# Строгая типизация

# Пространство имен
namespace App\Services;

use App\Interfaces\CompanyServiceInterface;
use App\Interfaces\HttpClientInterface;
use App\Exceptions\HttpClientException;
use App\Exceptions\ApiException;

# Бизнес-логика получения данных
class CompanyService implements CompanyServiceInterface
{
    
    private HttpClientInterface $httpClient;
    
    private const API_KEY = 'demo'; 
    private const API_URL = 'https://api.checko.ru/v2/company';
    
    // Конструктор
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    #[\Override]
    public function getCompanyInfo(string $inn): array 
    {
        // Валидация ИНН через регулярное выражение
        if (!preg_match('/^(\d{10}|\d{12})$/', $inn)) 
        {
            throw new \InvalidArgumentException("Неверный формат ИНН. Ожидалось 10 или 12 цифр.");
        }
        
        // Формирование URL
        // self - доступ к static/const элементу
        $queryParams = [
            'inn' => $inn,
            'key' => self::API_KEY
        ];
        $url = self::API_URL . '?' . http_build_query($queryParams);
        
        // Выполнение запроса
        try 
        {
            $response = $this->httpClient->get($url);
        } 
        catch (HttpClientException $ex)
        {
            throw new \RuntimeException("Ошибка при запросе к внешнему API: " . $ex->getMessage(), $ex->getCode(), $ex);
        }
        
        // возможно возвращение ошибки от внешнего API, обработка
        if (isset($response['error'])) 
        {
            throw new \RuntimeException("API Checko вернул ошибку: " . $response['error']);
        }
        
         // Извлечение полезных данных. 
         // Если данных не оказалось в 'data', то смотрим в корень jsonки
        $data = $response['data'] ?? $response;
        
        // Маппинг полей от API. 
        // Вместо отдельного класса DTO тут просто массив.
        return [
            'inn'      => $inn,
            'name'     => (string) ($data['НаимСокр'] ?? $data['НаимПолн'] ?? 'Неизвестно'),
            'ogrn'     => $data['ОГРН'] ?? null,
            'address'  => $data['ЮрАдрес']['АдресРФ'] ?? null,
            'status'   => $data['Статус']['Наим'] ?? null,         
            'director' => isset($data['Руковод'][0]['ФИО']) ? (string) $data['Руковод'][0]['ФИО'] : null,
        ];
    
    }
}