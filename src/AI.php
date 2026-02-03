<?php

namespace Nano\Framework;

class AI
{
    protected string $apiKey;
    protected string $model = 'gemini-1.5-flash';
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = $_ENV['AI_KEY'] ?? '';
    }

    /**
     * Ask the AI a question or give a command.
     */
    public function ask(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            throw new \Exception("AI_KEY is not set in your .env file. Run 'php artisan ai:setup' first.");
        }

        $url = $this->apiUrl . $this->model . ":generateContent?key=" . $this->apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }

        $data = json_decode($response, true);

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    /**
     * Set the AI model to use.
     */
    public function useModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }
}

