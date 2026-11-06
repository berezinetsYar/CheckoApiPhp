<?php

# Контракт для сервиса
interface CompanyServiceInterface
{
    # Основная функция для получения данных
    public function getCompanyInfo(string $inn): array;
}
