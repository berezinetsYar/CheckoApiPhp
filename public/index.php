<?php
declare(strict_types=1);
# Строгая типизация


# Точка входа (Front Controller). Аналог Program.cs + Middleware


// Подключение используемых файлов
require_once __DIR__ . '/../src/Interfaces/HttpClientInterface.php';
require_once __DIR__ . '/../src/Interfaces/CompanyServiceInterface.php';
require_once __DIR__ . '/../src/Exceptions/HttpClientException.php';
require_once __DIR__ . '/../src/Http/CurlHttpClient.php';
require_once __DIR__ . '/../src/Services/CompanyService.php';

use App\Http\CurlHttpClient;
use App\Services\CompanyService;

// Установка заголовков ответа (аналог [Produces("application/json")])
header('Content-Type: application/json; charset=utf-8');
//header('Access-Control-Allow-Origin: *'); // Для CORS

// Получаем HTTP-метод и путь запроса (аналог [HttpGet] + Route)
$httpMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Простой роутинг (аналог минимальных API - app.MapGet("/api/company", ...))
// GET /index.php?inn=7707083893
if ($httpMethod === 'GET' && str_ends_with($requestUri, '/index.php')) {
    
    // 5. Получаем параметр из query string (аналог [FromQuery] string inn)
    $inn = $_GET['inn'] ?? null;
    
    // Валидация входных данных
    if (empty($inn)) 
    {
        // Bad Request
        http_response_code(400); 
        echo json_encode([
            'error' => 'Параметр "inn" обязателен',
            'example' => '/index.php?inn=7707083893'
        ], JSON_UNESCAPED_UNICODE);
        // JSON_UNESCAPED_UNICODE - управление выводом при преобразовании данных в JSON.
        // Не кодировать многобайтовые символы Юникода, которые по умолчанию кодируются как последовательности \uXXXX
        
        // Останавливаем скрипт
        exit;
    }
    
    // Сборка зависимостей (DI - аналог Program.cs)
    try 
    {
        $httpClient = new CurlHttpClient(10);
        $companyService = new CompanyService($httpClient);
        
        //Вызов бизнес-логики
        $result = $companyService->getCompanyInfo($inn);
        
        // Успешный ответ (200 OK)
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $result
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);        
    } 
    // Блоки обработки ошибок
    catch (\InvalidArgumentException $e) 
    {
        // 400 Bad Request - ошибка валидации
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);        
    } 
    catch (\RuntimeException $e) 
    {
        // 502 Bad Gateway - ошибка внешнего API
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'error' => 'Ошибка внешнего сервиса',
            'details' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);        
    } 
    catch (\Exception $e) 
    {
        // 500 Internal Server Error - непредвиденная ошибка
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Внутренняя ошибка сервера'
        ], JSON_UNESCAPED_UNICODE);
    }    
}
else
{
    // 404 Not Found - неправильный путь или метод
    http_response_code(404);
    echo json_encode([
        'error' => 'Endpoint not found',
        'available_endpoints' => [
            'GET /index.php?inn={inn}' => 'Получить информацию о компании по ИНН'
        ]
    ], JSON_UNESCAPED_UNICODE);
}