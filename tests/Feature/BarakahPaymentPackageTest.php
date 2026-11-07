<?php

use Illuminate\Support\Facades\Http;
use RelicSoftware\BarakahPayment\BarakahClient;
use RelicSoftware\BarakahPayment\Exceptions\BarakahConfigurationException;
use RelicSoftware\BarakahPayment\Exceptions\BarakahPaymentException;
use RelicSoftware\BarakahPayment\Facades\Barakah;

test('package client throws exception when api key is not configured', function () {
    $client = new BarakahClient(['api_key' => '']);
    $client->createPayment([
        'amount' => 100,
        'description' => 'Test',
    ]);
})->throws(BarakahConfigurationException::class);

test('package client throws exception when amount is zero or negative', function () {
    $client = new BarakahClient(['api_key' => 'test_key']);
    $client->createPayment([
        'amount' => 0,
        'description' => 'Test',
    ]);
})->throws(BarakahPaymentException::class);

test('package creates payment request and provides clean redirect response', function () {
    Http::fake([
        '*/api/v1/payment/create' => Http::response([
            'success' => true,
            'message' => 'Payment request initiated.',
            'data' => [
                'payment_id' => 'brk_pay_99887766',
                'amount' => 450.00,
                'currency' => 'BDT',
                'description' => 'Semester Cafeteria Pass',
                'status' => 'pending',
                'checkout_url' => 'https://barakah.test/payment/checkout/brk_pay_99887766',
                'expires_at' => now()->addMinutes(30)->toISOString(),
            ],
        ], 201),
    ]);

    $client = new BarakahClient([
        'base_url' => 'https://barakah.test',
        'api_key' => 'valid_api_key_123',
    ]);

    $response = $client->createPayment([
        'amount' => 450.00,
        'description' => 'Semester Cafeteria Pass',
        'callback_url' => 'https://merchant.test/callback',
    ]);

    expect($response->getPaymentId())->toBe('brk_pay_99887766');
    expect($response->getAmount())->toBe(450.0);
    expect($response->getCheckoutUrl())->toBe('https://barakah.test/payment/checkout/brk_pay_99887766');
    expect($response->getStatus())->toBe('pending');
    expect($response->redirect()->getTargetUrl())->toBe('https://barakah.test/payment/checkout/brk_pay_99887766');
});

test('package queries payment status and isPaid helper', function () {
    Http::fake([
        '*/api/v1/payment/brk_pay_12345/status' => Http::response([
            'success' => true,
            'data' => [
                'payment_id' => 'brk_pay_12345',
                'status' => 'paid',
                'amount' => 1200.00,
                'description' => 'Tuition Fee Q1',
                'transaction_reference' => 'TXN-20261107-1234',
                'paid_at' => now()->toISOString(),
            ],
        ], 200),
    ]);

    $client = new BarakahClient([
        'base_url' => 'https://barakah.test',
        'api_key' => 'valid_api_key_123',
    ]);

    $status = $client->status('brk_pay_12345');
    expect($status->isPaid())->toBeTrue();
    expect($status->isPending())->toBeFalse();
    expect($status->getAmount())->toBe(1200.0);
    expect($status->getTransactionReference())->toBe('TXN-20261107-1234');

    expect($client->isPaid('brk_pay_12345'))->toBeTrue();
});

test('package facade is bound and resolvable', function () {
    expect(app(BarakahClient::class))->toBeInstanceOf(BarakahClient::class);
    expect(app('barakah'))->toBeInstanceOf(BarakahClient::class);
});

