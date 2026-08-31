<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class TwilioWhatsAppService
{
    /**
     * @return bool
     */
    public function isConfigured()
    {
        $sid = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.auth_token');
        $from = (string) config('services.twilio.whatsapp_from');

        return $sid !== '' && $token !== '' && $from !== '';
    }

    /**
     * Send a WhatsApp message. Failures are logged and never thrown to callers
     * unless $throwOnError is true (always false from order flow).
     *
     * @param string $toE164
     * @param string $body
     * @param string|null $mediaUrl
     * @return bool
     */
    public function sendWhatsApp($toE164, $body, $mediaUrl = null)
    {
        if (!$this->isConfigured()) {
            Log::error('Twilio WhatsApp skipped: missing configuration', array(
                'has_sid' => (string) config('services.twilio.sid') !== '',
                'has_token' => (string) config('services.twilio.auth_token') !== '',
                'has_from' => (string) config('services.twilio.whatsapp_from') !== '',
            ));

            return false;
        }

        $to = $this->formatWhatsAppAddress($toE164);
        $from = $this->formatWhatsAppAddress(config('services.twilio.whatsapp_from'));

        if ($to === null || $from === null) {
            Log::error('Twilio WhatsApp skipped: invalid from/to address', array(
                'to_masked' => $this->maskPhone($toE164),
            ));

            return false;
        }

        $payload = array(
            'from' => $from,
            'body' => $body,
        );

        if (!empty($mediaUrl)) {
            $payload['mediaUrl'] = array($mediaUrl);
        }

        try {
            $client = new Client(
                (string) config('services.twilio.sid'),
                (string) config('services.twilio.auth_token')
            );
            $message = $client->messages->create($to, $payload);

            Log::info('Twilio WhatsApp message queued', array(
                'to_masked' => $this->maskPhone($toE164),
                'sid' => $message->sid,
                'status' => $message->status,
                'has_media' => !empty($mediaUrl),
            ));

            return true;
        } catch (TwilioException $e) {
            Log::error('Twilio WhatsApp API error', array(
                'to_masked' => $this->maskPhone($toE164),
                'code' => $e->getCode(),
                'error' => $e->getMessage(),
                'has_media' => !empty($mediaUrl),
            ));

            if (!empty($mediaUrl)) {
                Log::info('Twilio WhatsApp retrying without media attachment', array(
                    'to_masked' => $this->maskPhone($toE164),
                ));

                return $this->sendWhatsApp($toE164, $body, null);
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Twilio WhatsApp unexpected error', array(
                'to_masked' => $this->maskPhone($toE164),
                'error' => $e->getMessage(),
            ));

            return false;
        }
    }

    /**
     * @param string $number
     * @return string|null
     */
    public function formatWhatsAppAddress($number)
    {
        $number = trim((string) $number);
        if ($number === '') {
            return null;
        }

        if (stripos($number, 'whatsapp:') === 0) {
            $number = trim(substr($number, strlen('whatsapp:')));
        }

        $e164 = $this->normalizeToE164($number);
        if ($e164 === null) {
            return null;
        }

        return 'whatsapp:' . $e164;
    }

    /**
     * Normalize a stored phone into E.164. Indian numbers default to +91
     * only when no country code is already present.
     *
     * @param string $phone
     * @return string|null
     */
    public function normalizeToE164($phone)
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return null;
        }

        if (stripos($raw, 'whatsapp:') === 0) {
            $raw = trim(substr($raw, strlen('whatsapp:')));
        }

        $hasPlus = strpos($raw, '+') === 0;
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === null || $digits === '') {
            return null;
        }

        if ($hasPlus) {
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                return null;
            }

            return '+' . $digits;
        }

        if (strpos($raw, '00') === 0 && strlen($digits) >= 10) {
            $digits = ltrim($digits, '0');
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                return null;
            }

            return '+' . $digits;
        }

        // 00-prefixed country code already stripped above; handle 91xxxxxxxxxx
        if (strlen($digits) === 12 && strpos($digits, '91') === 0) {
            return '+' . $digits;
        }

        if (strlen($digits) === 11 && strpos($digits, '0') === 0) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10 && preg_match('/^[6-9][0-9]{9}$/', $digits)) {
            return '+91' . $digits;
        }

        return null;
    }

    /**
     * @param string $phone
     * @return string
     */
    public function maskPhone($phone)
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === null || strlen($digits) < 4) {
            return '****';
        }

        return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
    }
}
