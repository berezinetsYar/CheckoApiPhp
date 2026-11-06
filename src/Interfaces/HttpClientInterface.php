<?php

# Контракт для HTTP-клиента. Аналог IHttpClient.
interface HttpClientInterface
{
    public function get(string $url): array;
}
