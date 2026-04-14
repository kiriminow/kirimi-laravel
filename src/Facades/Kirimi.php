<?php

namespace Kirimi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendMessage(string $deviceId, string $phone, string $message, ?string $mediaUrl = null)
 * @method static array sendMessageFast(string $deviceId, string $phone, string $message, ?string $mediaUrl = null)
 * @method static array sendMessageFile(string $deviceId, string $phone, string $filePath, array $options = [])
 * @method static array sendWabaMessage(string $deviceId, string $phone, string $message)
 * @method static array listDevices()
 * @method static array deviceStatus(string $deviceId)
 * @method static array deviceStatusEnhanced(string $deviceId)
 * @method static array userInfo()
 * @method static array saveContact(string $phone, array $options = [])
 * @method static array generateOTP(string $deviceId, string $phone, array $options = [])
 * @method static array validateOTP(string $deviceId, string $phone, string $otp)
 * @method static array sendOtpV2(string $phone, string $deviceId, array $options = [])
 * @method static array verifyOtpV2(string $phone, string $otpCode)
 * @method static array broadcastMessage(string $deviceId, array|string $phones, string $message, array $options = [])
 * @method static array listDeposits(?string $status = null)
 * @method static array listPackages()
 * @method static array healthCheck()
 *
 * @see \Kirimi\KirimiClient
 */
class Kirimi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'kirimi';
    }
}
