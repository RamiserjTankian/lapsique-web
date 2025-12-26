<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ContactLog;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected Client $client;
    protected string $accountSid;
    protected string $authToken;

    public function __construct()
    {
        $this->accountSid = config('twilio.account_sid');
        $this->authToken = config('twilio.auth_token');
        
        $this->client = new Client($this->accountSid, $this->authToken);
    }

    /**
     * Enviar SMS a un cliente
     */
    public function sendSMS(Customer $customer, string $message, array $options = []): ?ContactLog
    {
        if (!$customer->phone) {
            Log::warning('Customer does not have a phone number', ['customer_id' => $customer->id]);
            return null;
        }

        if (!$customer->subscribed_sms) {
            Log::info('Customer not subscribed to SMS', ['customer_id' => $customer->id]);
            return null;
        }

        try {
            $from = config('twilio.from.sms');
            $to = $this->formatPhoneNumber($customer->phone);

            $twilioMessage = $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message,
                    'statusCallback' => config('twilio.webhooks.sms_status'),
                    'maxPrice' => $options['max_price'] ?? config('twilio.defaults.max_price'),
                    'validityPeriod' => $options['validity_period'] ?? config('twilio.defaults.validity_period'),
                ]
            );

            // Crear log de contacto
            $contactLog = ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $options['event_id'] ?? null,
                'campaign_id' => $options['campaign_id'] ?? null,
                'automation_id' => $options['automation_id'] ?? null,
                'channel' => 'sms',
                'type' => $options['type'] ?? 'notification',
                'subject' => $options['subject'] ?? null,
                'message' => $message,
                'metadata' => [
                    'twilio_sid' => $twilioMessage->sid,
                    'to' => $to,
                    'from' => $from,
                ],
                'status' => 'sent',
                'sent_at' => now(),
                'created_by' => $options['created_by'] ?? null,
            ]);

            Log::info('SMS sent successfully', [
                'customer_id' => $customer->id,
                'twilio_sid' => $twilioMessage->sid,
            ]);

            return $contactLog;
        } catch (TwilioException $e) {
            Log::error('Failed to send SMS', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            // Crear log de contacto con error
            return ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $options['event_id'] ?? null,
                'campaign_id' => $options['campaign_id'] ?? null,
                'automation_id' => $options['automation_id'] ?? null,
                'channel' => 'sms',
                'type' => $options['type'] ?? 'notification',
                'message' => $message,
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
                'created_by' => $options['created_by'] ?? null,
            ]);
        }
    }

    /**
     * Enviar mensaje de WhatsApp
     */
    public function sendWhatsApp(Customer $customer, string $message, array $options = []): ?ContactLog
    {
        $whatsapp = $customer->whatsapp ?? $customer->phone;
        
        if (!$whatsapp) {
            Log::warning('Customer does not have WhatsApp number', ['customer_id' => $customer->id]);
            return null;
        }

        if (!$customer->subscribed_whatsapp) {
            Log::info('Customer not subscribed to WhatsApp', ['customer_id' => $customer->id]);
            return null;
        }

        try {
            $from = config('twilio.from.whatsapp');
            $to = 'whatsapp:' . $this->formatPhoneNumber($whatsapp);

            $twilioMessage = $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message,
                    'statusCallback' => config('twilio.webhooks.whatsapp_status'),
                ]
            );

            // Crear log de contacto
            $contactLog = ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $options['event_id'] ?? null,
                'campaign_id' => $options['campaign_id'] ?? null,
                'automation_id' => $options['automation_id'] ?? null,
                'channel' => 'whatsapp',
                'type' => $options['type'] ?? 'notification',
                'subject' => $options['subject'] ?? null,
                'message' => $message,
                'metadata' => [
                    'twilio_sid' => $twilioMessage->sid,
                    'to' => $to,
                    'from' => $from,
                ],
                'status' => 'sent',
                'sent_at' => now(),
                'created_by' => $options['created_by'] ?? null,
            ]);

            Log::info('WhatsApp message sent successfully', [
                'customer_id' => $customer->id,
                'twilio_sid' => $twilioMessage->sid,
            ]);

            return $contactLog;
        } catch (TwilioException $e) {
            Log::error('Failed to send WhatsApp message', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $options['event_id'] ?? null,
                'campaign_id' => $options['campaign_id'] ?? null,
                'automation_id' => $options['automation_id'] ?? null,
                'channel' => 'whatsapp',
                'type' => $options['type'] ?? 'notification',
                'message' => $message,
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
                'created_by' => $options['created_by'] ?? null,
            ]);
        }
    }

    /**
     * Hacer una llamada
     */
    public function makeCall(Customer $customer, string $twimlUrl, array $options = []): ?ContactLog
    {
        if (!$customer->phone) {
            Log::warning('Customer does not have a phone number', ['customer_id' => $customer->id]);
            return null;
        }

        try {
            $from = config('twilio.from.voice');
            $to = $this->formatPhoneNumber($customer->phone);

            $call = $this->client->calls->create(
                $to,
                $from,
                [
                    'url' => $twimlUrl,
                    'statusCallback' => config('twilio.webhooks.voice_status'),
                    'statusCallbackEvent' => ['initiated', 'ringing', 'answered', 'completed'],
                ]
            );

            // Crear log de contacto
            $contactLog = ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $options['event_id'] ?? null,
                'channel' => 'call',
                'type' => $options['type'] ?? 'notification',
                'subject' => $options['subject'] ?? 'Automated Call',
                'metadata' => [
                    'twilio_sid' => $call->sid,
                    'to' => $to,
                    'from' => $from,
                    'twiml_url' => $twimlUrl,
                ],
                'status' => 'sent',
                'sent_at' => now(),
                'created_by' => $options['created_by'] ?? null,
            ]);

            Log::info('Call initiated successfully', [
                'customer_id' => $customer->id,
                'twilio_sid' => $call->sid,
            ]);

            return $contactLog;
        } catch (TwilioException $e) {
            Log::error('Failed to initiate call', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $options['event_id'] ?? null,
                'channel' => 'call',
                'type' => $options['type'] ?? 'notification',
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
                'created_by' => $options['created_by'] ?? null,
            ]);
        }
    }

    /**
     * Verificar número de teléfono con Twilio Verify
     */
    public function sendVerificationCode(Customer $customer): bool
    {
        if (!$customer->phone) {
            return false;
        }

        try {
            $verification = $this->client->verify
                ->v2
                ->services(config('twilio.verify_sid'))
                ->verifications
                ->create($this->formatPhoneNumber($customer->phone), 'sms');

            Log::info('Verification code sent', [
                'customer_id' => $customer->id,
                'status' => $verification->status,
            ]);

            return $verification->status === 'pending';
        } catch (TwilioException $e) {
            Log::error('Failed to send verification code', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verificar código de verificación
     */
    public function verifyCode(Customer $customer, string $code): bool
    {
        if (!$customer->phone) {
            return false;
        }

        try {
            $verificationCheck = $this->client->verify
                ->v2
                ->services(config('twilio.verify_sid'))
                ->verificationChecks
                ->create([
                    'to' => $this->formatPhoneNumber($customer->phone),
                    'code' => $code,
                ]);

            $isApproved = $verificationCheck->status === 'approved';

            if ($isApproved) {
                $customer->update(['phone_verified_at' => now()]);
                
                Log::info('Phone number verified successfully', [
                    'customer_id' => $customer->id,
                ]);
            }

            return $isApproved;
        } catch (TwilioException $e) {
            Log::error('Failed to verify code', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Formatear número de teléfono al formato E.164
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Eliminar caracteres no numéricos excepto el +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si no empieza con +, asumimos que es de México (+52)
        if (!str_starts_with($phone, '+')) {
            $phone = '+52' . $phone;
        }

        return $phone;
    }

    /**
     * Obtener estado de un mensaje
     */
    public function getMessageStatus(string $messageSid): ?string
    {
        try {
            $message = $this->client->messages($messageSid)->fetch();
            return $message->status;
        } catch (TwilioException $e) {
            Log::error('Failed to fetch message status', [
                'message_sid' => $messageSid,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}

