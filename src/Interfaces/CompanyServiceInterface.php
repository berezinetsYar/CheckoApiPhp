<?php

# Пространство имен
namespace App\Interfaces;

/**
 * Контракт для сервиса
 */
interface CompanyServiceInterface
{
    /**
     * Основная функция для получения данных
     * @param string $inn ИНН компании 
     * @return array Массив с основными данными компании
     * @throws \InvalidArgumentException ИНН невалиден
     * @throws \Exception Внешний сервис вернул ошибку
     */
    public function getCompanyInfo(string $inn): array;
}
