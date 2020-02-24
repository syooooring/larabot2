<?php

namespace App\Services;

use GuzzleHttp\Client;

class Qiita
{    
  private const QIITA_SEARCH_API_URL = 'https://qiita.com/api/v2/items';

    public function searchQiita(string $word): array
    {
        $client = new Client();
        $response = $client
            ->get(self::QIITA_SEARCH_API_URL, [
                'query' => [
                    'keyid' => env('QIITA_ACCESS_TOKEN'),
                    'freeword' => str_replace(' ', ',', $word),
                ],
              'http_errors' => false,
            ]);
            
        return json_decode($response->getBody()->getContents(), true);
    }
}