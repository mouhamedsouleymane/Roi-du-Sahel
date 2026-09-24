<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $token;
    protected string $phoneNumberId;
    protected string $version;
    protected string $endpoint;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token') ?? '';
        $this->phoneNumberId = config('services.whatsapp.phone_number_id') ?? '';
        $this->version = config('services.whatsapp.version', 'v20.0');
        $this->endpoint = "https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages";
    }

    /**
     * Nettoie et formate le numéro de téléphone au format international (sans + ni espaces)
     */
    public function formatPhoneNumber(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Envoi d'un message texte standard
     */
    public function sendTextMessage(string $to, string $message): array
    {
        try {
            $response = Http::withToken($this->token)
                ->post($this->endpoint, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $this->formatPhoneNumber($to),
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->failed()) {
                Log::error('WhatsApp API Text Error', [
                    'to' => $to,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            }

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Exception (sendTextMessage): ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Envoi d'un message avec boutons de réponse rapide (Interactive Quick Reply Buttons)
     */
    public function sendInteractiveButtons(string $to, string $bodyText, array $buttons, string $headerText = '', string $footerText = 'Établissement Roi du Sahel'): array
    {
        try {
            $formattedButtons = [];
            foreach ($buttons as $btn) {
                $formattedButtons[] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => $btn['id'],
                        'title' => mb_substr($btn['title'], 0, 20), // Max 20 caractères selon Meta
                    ],
                ];
            }

            $interactive = [
                'type' => 'button',
                'body' => ['text' => $bodyText],
                'action' => ['buttons' => $formattedButtons],
            ];

            if (!empty($headerText)) {
                $interactive['header'] = ['type' => 'text', 'text' => $headerText];
            }

            if (!empty($footerText)) {
                $interactive['footer'] = ['text' => $footerText];
            }

            $response = Http::withToken($this->token)->post($this->endpoint, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $this->formatPhoneNumber($to),
                'type' => 'interactive',
                'interactive' => $interactive,
            ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('WhatsApp Interactive Button Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Envoi d'un Template officiel Meta (recommandé pour les notifications sortantes)
     */
    public function sendTemplateMessage(string $to, string $templateName, string $languageCode = 'fr', array $parameters = []): array
    {
        $components = [];

        if (!empty($parameters)) {
            $formattedParams = [];
            foreach ($parameters as $param) {
                $formattedParams[] = [
                    'type' => 'text',
                    'text' => (string)$param,
                ];
            }

            $components[] = [
                'type' => 'body',
                'parameters' => $formattedParams,
            ];
        }

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $this->formatPhoneNumber($to),
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => $languageCode],
                ],
            ];

            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withToken($this->token)->post($this->endpoint, $payload);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('WhatsApp Template Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Envoi d'un document (Bulletin, Facture, Reçu, Certificat de scolarité)
     */
    public function sendDocument(string $to, string $documentUrl, string $filename, string $caption = ''): array
    {
        try {
            $response = Http::withToken($this->token)
                ->post($this->endpoint, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $this->formatPhoneNumber($to),
                    'type' => 'document',
                    'document' => [
                        'link' => $documentUrl,
                        'filename' => $filename,
                        'caption' => $caption,
                    ],
                ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('WhatsApp Document Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // =========================================================================
    // 1. NOTIFICATIONS AUTOMATIQUES SCOLAIRES (Absences, Retards, Résultats)
    // =========================================================================

    /**
     * Notification d'absence ou de retard
     */
    public function notifyAttendance(string $parentPhone, string $studentName, string $status, string $date, string $details = ''): array
    {
        $statusLabel = match (strtolower($status)) {
            'absent', 'absence' => 'une absence non justifiée',
            'late', 'retard' => 'un retard',
            default => "un événement d'assiduité ({$status})"
        };

        $msg = "🏫 *COMPLEXE SCOLAIRE ROI DU SAHEL*\n"
            . "⚠️ *Avis d'Assiduité*\n\n"
            . "Bonjour Cher Parent,\n"
            . "Nous vous informons que l'élève *{$studentName}* a enregistré *{$statusLabel}* ce jour (*{$date}*).\n"
            . ($details ? "Détails: {$details}\n" : "")
            . "Merci de prendre attache avec la vie scolaire pour toute justification ou renseignement.";

        return $this->sendTextMessage($parentPhone, $msg);
    }

    /**
     * Notification de résultat trimestriel / Bulletin disponible
     */
    public function notifyTermResults(string $parentPhone, string $studentName, string $termName, float|string $average, string $rank, ?string $reportCardPdfUrl = null): array
    {
        $msg = "🏫 *COMPLEXE SCOLAIRE ROI DU SAHEL*\n"
            . "📊 *Résultats du {$termName}*\n\n"
            . "Bonjour Cher Parent,\n"
            . "Les résultats de l'élève *{$studentName}* sont disponibles :\n"
            . "• *Moyenne générale* : {$average} / 20\n"
            . "• *Rang* : {$rank}\n\n"
            . "Le bulletin détaillé est consultable sur votre espace parent ou téléchargeable ci-joint.";

        if ($reportCardPdfUrl) {
            return $this->sendDocument($parentPhone, $reportCardPdfUrl, "Bulletin_{$studentName}_{$termName}.pdf", $msg);
        }

        return $this->sendTextMessage($parentPhone, $msg);
    }

    // =========================================================================
    // 2. RAPPELS DE PAIEMENT AUTOMATIQUES (Scolarité / Inscription)
    // =========================================================================

    /**
     * Rappel d'échéance ou de facture en attente
     */
    public function sendPaymentReminder(string $parentPhone, string $studentName, string $invoiceTitle, float|string $amountDue, string $dueDate, string $paymentMethods = 'Guichet, Mobile Money'): array
    {
        $formattedAmount = is_numeric($amountDue) ? number_format($amountDue, 0, ',', ' ') . ' FCFA' : $amountDue;

        $msg = "🏫 *COMPLEXE SCOLAIRE ROI DU SAHEL*\n"
            . "💳 *Rappel d'Échéance de Paiement*\n\n"
            . "Bonjour Cher Parent,\n"
            . "Sauf erreur ou règlement récent de votre part, nous vous rappelons l'échéance concernant *{$studentName}* :\n\n"
            . "• *Objet* : {$invoiceTitle}\n"
            . "• *Montant restant dû* : *{$formattedAmount}*\n"
            . "• *Date limite* : {$dueDate}\n"
            . "• *Moyens de paiement* : {$paymentMethods}\n\n"
            . "Merci de procéder au règlement avant la date indiquée pour assurer la continuité pédagogique.";

        $buttons = [
            ['id' => 'BTN_HOW_TO_PAY', 'title' => 'Moyens de paiement'],
            ['id' => 'BTN_CONTACT_ACCOUNT', 'title' => 'Contacter comptabilité'],
        ];

        return $this->sendInteractiveButtons($parentPhone, $msg, $buttons, 'Service Comptabilité');
    }

    // =========================================================================
    // 3. COMMUNICATION INSTITUTIONNELLE (Annonces générales)
    // =========================================================================

    /**
     * Envoi d'une annonce générale ou institutionnelle
     */
    public function sendInstitutionalAnnouncement(string $parentPhone, string $title, string $content, string $signature = 'La Direction'): array
    {
        $msg = "🏫 *COMPLEXE SCOLAIRE ROI DU SAHEL*\n"
            . "📢 *COMMUNIQUÉ OFFICIEL*\n\n"
            . "*{$title}*\n\n"
            . "{$content}\n\n"
            . "_{$signature}_";

        return $this->sendTextMessage($parentPhone, $msg);
    }

    /**
     * Diffusion groupée (Broadcast) avec temporisation pour respecter les quotas Meta
     */
    public function broadcastAnnouncement(array $phoneNumbers, string $title, string $content): array
    {
        $success = 0;
        $failed = 0;

        foreach ($phoneNumbers as $phone) {
            $res = $this->sendInstitutionalAnnouncement($phone, $title, $content);
            if (!isset($res['error']) && !isset($res['errors'])) {
                $success++;
            } else {
                $failed++;
            }
            // Temporisation légère (50ms) pour éviter le rate-limiting
            usleep(50000);
        }

        return [
            'total' => count($phoneNumbers),
            'success' => $success,
            'failed' => $failed,
        ];
    }
}
