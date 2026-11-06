<?php

/**
 * Контракт для сервиса
 */
interface CompanyServiceInterface
{
    /**
     * Основная функция для получения данных
     * @param string $inn ИНН компании
     */
    public function getCompanyInfo(string $inn): array;
}
