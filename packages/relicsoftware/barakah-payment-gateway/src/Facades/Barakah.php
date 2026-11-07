<?php

namespace RelicSoftware\BarakahPayment\Facades;

use Illuminate\Support\Facades\Facade;
use RelicSoftware\BarakahPayment\BarakahClient;

/**
 * @method static \RelicSoftware\BarakahPayment\Responses\PaymentResponse createPayment(array $params)
 * @method static \RelicSoftware\BarakahPayment\Responses\PaymentStatusResponse status(string $paymentId)
 * @method static bool isPaid(string $paymentId)
 * @method static array sendOtp(string $paymentId, string $phone)
 * @method static array verifyOtp(string $paymentId, string $otp)
 * @method static array execute(string $paymentId, string $pin)
 *
 * @see \RelicSoftware\BarakahPayment\BarakahClient
 */
class Barakah extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BarakahClient::class;
    }
}

