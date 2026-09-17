<?php

namespace Kirimi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendMessage(string $deviceId, string $receiver, string $message, ?string $mediaUrl = null, array $options = [])
 * @method static array sendMessageFast(string $deviceId, string $receiver, string $message, ?string $mediaUrl = null, array $options = [])
 * @method static array sendMessageFile(string $deviceId, string $receiver, string $filePath, array $options = [])
 * @method static array broadcastMessage(string $deviceId, string $label, array $numbers, string $message, array $options = [])
 * @method static array sendWabaMessage(string $wabaId, string $to, string $templateName, array $options = [])
 * @method static array wabaReply(string $wabaId, string $to, array $message)
 * @method static array wabaConversations(?int $limit = null, ?int $page = null)
 * @method static array wabaTemplatesSync(string $wabaId)
 * @method static array wabaSendOtp(string $wabaId, string $to, string $templateName)
 * @method static array wabaVerifyOtp(string $wabaId, string $to, string $otpCode)
 * @method static array createDevice(int|string $packageId, ?string $voucherCode = null)
 * @method static array connectDevice(string $deviceId)
 * @method static array renewDevice(string $deviceId, int|string $packageId, ?string $voucherCode = null)
 * @method static array listDevices(?int $page = null, ?int $limit = null)
 * @method static array deviceStatus(string $deviceId)
 * @method static array deviceStatusEnhanced(string $deviceId)
 * @method static array userInfo()
 * @method static array saveContact(string $nama, string $nomor, ?string $deviceId = null)
 * @method static array saveContactsBulk(array $contacts, ?string $deviceId = null)
 * @method static array generateOTP(string $deviceId, string $phone, array $options = [])
 * @method static array validateOTP(string $deviceId, string $phone, string $otp)
 * @method static array sendOtpV2(string $phone, array $options = [])
 * @method static array verifyOtpV2(string $phone, string $otpCode)
 * @method static array otpReverseCreate(string $phone, string $deviceId, array $options = [])
 * @method static array otpReverseStatus(string $token)
 * @method static array listPackages()
 * @method static array createDeposit(int $nominal)
 * @method static array depositStatus(string $ref)
 * @method static array cancelDeposit(string $ref)
 * @method static array listDeposits(array $options = [])
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
