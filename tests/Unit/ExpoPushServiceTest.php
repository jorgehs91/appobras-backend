<?php

namespace Tests\Unit;

use App\Services\ExpoPushService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExpoPushServiceTest extends TestCase
{
    private ExpoPushService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExpoPushService;
    }

    public function test_is_valid_token_returns_true_for_valid_exponent_token(): void
    {
        $validToken = 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]';

        $result = $this->service->isValidToken($validToken);

        $this->assertTrue($result);
    }

    public function test_is_valid_token_returns_true_for_valid_expo_token(): void
    {
        $validToken = 'ExpoPushToken[xxxxxxxxxxxxxxxxxxxxxx]';

        $result = $this->service->isValidToken($validToken);

        $this->assertTrue($result);
    }

    public function test_is_valid_token_returns_false_for_invalid_token(): void
    {
        $invalidTokens = [
            'invalid-token',
            'ExponentPushToken',
            'ExpoPushToken',
            'ExponentPushToken[]',
            'ExpoPushToken[]',
            '',
        ];

        foreach ($invalidTokens as $token) {
            $result = $this->service->isValidToken($token);
            $this->assertFalse($result, "Token '{$token}' should be invalid");
        }
    }

    public function test_send_push_returns_null_for_empty_token(): void
    {
        Http::fake();

        $result = $this->service->sendPush('', 'Title', 'Body');

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    public function test_send_push_sends_correct_payload_to_expo_api(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    [
                        'status' => 'ok',
                        'id' => 'test-id',
                    ],
                ],
            ], 200),
        ]);

        $token = 'ExponentPushToken[test-token]';
        $title = 'Test Title';
        $body = 'Test Body';
        $data = ['key' => 'value'];

        $result = $this->service->sendPush($token, $title, $body, $data);

        Http::assertSent(function ($request) use ($token, $title, $body, $data) {
            $payload = $request->data()[0];

            return $request->url() === 'https://exp.host/--/api/v2/push/send'
                && $payload['to'] === $token
                && $payload['title'] === $title
                && $payload['body'] === $body
                && $payload['data'] === $data;
        });

        $this->assertNotNull($result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_send_push_handles_api_error_response(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    [
                        'status' => 'error',
                        'message' => 'Invalid token',
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->sendPush('ExponentPushToken[test]', 'Title', 'Body');

        $this->assertNotNull($result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_send_push_handles_http_error(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([], 500),
        ]);

        $result = $this->service->sendPush('ExponentPushToken[test]', 'Title', 'Body');

        $this->assertNull($result);
    }

    public function test_send_push_handles_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Network error');
        });

        $result = $this->service->sendPush('ExponentPushToken[test]', 'Title', 'Body');

        $this->assertNull($result);
    }

    public function test_send_batch_returns_null_for_empty_messages(): void
    {
        Http::fake();

        $result = $this->service->sendBatch([]);

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    public function test_send_batch_sends_multiple_messages(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    ['status' => 'ok', 'id' => 'id1'],
                    ['status' => 'ok', 'id' => 'id2'],
                ],
            ], 200),
        ]);

        $messages = [
            [
                'to' => 'ExponentPushToken[token1]',
                'title' => 'Title 1',
                'body' => 'Body 1',
            ],
            [
                'to' => 'ExponentPushToken[token2]',
                'title' => 'Title 2',
                'body' => 'Body 2',
            ],
        ];

        $result = $this->service->sendBatch($messages);

        Http::assertSent(function ($request) use ($messages) {
            $sentMessages = $request->data();

            return count($sentMessages) === 2
                && $sentMessages[0]['to'] === $messages[0]['to']
                && $sentMessages[1]['to'] === $messages[1]['to'];
        });

        $this->assertNotNull($result);
    }

    public function test_send_push_includes_options(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([
                'data' => [['status' => 'ok']],
            ], 200),
        ]);

        $options = [
            'sound' => 'default',
            'badge' => 5,
            'priority' => 'high',
        ];

        $this->service->sendPush('ExponentPushToken[test]', 'Title', 'Body', [], $options);

        Http::assertSent(function ($request) use ($options) {
            $payload = $request->data()[0];

            return $payload['sound'] === $options['sound']
                && $payload['badge'] === $options['badge']
                && $payload['priority'] === $options['priority'];
        });
    }
}

