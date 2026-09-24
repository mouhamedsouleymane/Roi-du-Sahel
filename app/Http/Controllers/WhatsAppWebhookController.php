<?php

namespace App\Http\Controllers;

use App\Services\SchoolChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Vérification initiale du Webhook par Meta (Requête GET)
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.webhook_verify_token');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp Webhook vérifié avec succès par Meta.');
            return response($challenge, 200);
        }

        Log::warning('WhatsApp Webhook échec de vérification du token.', [
            'mode' => $mode,
            'token_recu' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Réception et traitement des événements (Requête POST)
     * (Messages entrants, réponses interactives, statuts)
     */
    public function handle(Request $request, SchoolChatbotService $chatbot): JsonResponse
    {
        $payload = $request->all();

        // 1. Détection des messages entrants
        if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0])) {
            $msgObj = $payload['entry'][0]['changes'][0]['value']['messages'][0];
            $from = $msgObj['from'] ?? null;
            $type = $msgObj['type'] ?? 'text';

            $text = '';
            $buttonId = null;

            if ($type === 'text') {
                $text = $msgObj['text']['body'] ?? '';
            } elseif ($type === 'interactive') {
                // Si l'utilisateur a cliqué sur un bouton interactif
                $buttonId = $msgObj['interactive']['button_reply']['id'] ?? null;
                $text = $msgObj['interactive']['button_reply']['title'] ?? '';
            }

            if ($from && ($text !== '' || $buttonId !== null)) {
                // Traiter automatiquement via le Chatbot scolaire
                $chatbot->handleIncomingMessage($from, $text, $buttonId);
            }
        }

        // 2. Détection des accusés de distribution/lecture
        if (isset($payload['entry'][0]['changes'][0]['value']['statuses'][0])) {
            $statusData = $payload['entry'][0]['changes'][0]['value']['statuses'][0];
            Log::debug('WhatsApp Message Status', [
                'recipient' => $statusData['recipient_id'] ?? null,
                'status' => $statusData['status'] ?? null,
            ]);
        }

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }
}
