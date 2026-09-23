<?php

declare(strict_types=1);

use Elegantly\Translator\Services\Proofread\AiProofreadService;
use Elegantly\Translator\Services\Translate\AiTranslateService;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\AiServiceProvider;

it('translates and proofreads through the AI SDK', function (string $service): void {
    app()->register(AiServiceProvider::class);
    config(['ai.providers.openai.key' => 'test-key']);
    Http::preventStrayRequests();
    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'id' => 'response-1',
            'model' => 'gpt-4.1-mini',
            'status' => 'completed',
            'output' => [[
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'output_text', 'text' => '{"greeting":"Hola"}']],
            ]],
            'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
        ]),
    ]);

    expect($service::execute(
        texts: ['greeting' => 'Hello'],
        prompt: 'Return the translated JSON.',
        provider: 'openai',
        model: 'gpt-4.1-mini',
        timeout: 30,
    ))->toBe(['greeting' => 'Hola']);

    Http::assertSentCount(1);
})->with([AiTranslateService::class, AiProofreadService::class]);
