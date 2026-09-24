<?php

namespace App\Services;

class SchoolChatbotService
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Traite un message entrant ou un clic sur bouton interactif et retourne la réponse appropriée
     */
    public function handleIncomingMessage(string $from, string $messageText, ?string $buttonId = null): void
    {
        $input = trim(mb_strtolower($messageText));

        // 1. Gestion des clics sur boutons interactifs
        if ($buttonId) {
            $this->handleButtonAction($from, $buttonId);
            return;
        }

        // 2. Détection par mots-clés / NLP simple
        if ($this->matchesAny($input, ['bonjour', 'salut', 'salam', 'hello', 'menu', 'aide', 'start', 'help', 'aidez-moi'])) {
            $this->sendMainMenu($from);
        } elseif ($this->matchesAny($input, ['horaire', 'horaires', 'heure', 'ouverture', 'fermeture', 'cours'])) {
            $this->sendSchedulesInfo($from);
        } elseif ($this->matchesAny($input, ['programme', 'programmes', 'filiere', 'serie', 'matiere', 'classe', 'niveau', 'primaire', 'college', 'lycee'])) {
            $this->sendProgramsInfo($from);
        } elseif ($this->matchesAny($input, ['tarif', 'tarifs', 'frais', 'scolarite', 'prix', 'combien', 'paiement', 'inscription', 'payer'])) {
            $this->sendPaymentAndFeesInfo($from);
        } elseif ($this->matchesAny($input, ['contact', 'adresse', 'localisation', 'siege', 'telephone', 'email', 'direction', 'secretariat'])) {
            $this->sendContactAndLocationInfo($from);
        } elseif ($this->matchesAny($input, ['calendrier', 'vacances', 'conges', 'rentree', 'trimestre', 'examen'])) {
            $this->sendAcademicCalendar($from);
        } else {
            $this->sendDefaultFallback($from);
        }
    }

    protected function handleButtonAction(string $from, string $buttonId): void
    {
        match ($buttonId) {
            'MENU_HORAIRES' => $this->sendSchedulesInfo($from),
            'MENU_PROGRAMMES' => $this->sendProgramsInfo($from),
            'MENU_TARIFS' => $this->sendPaymentAndFeesInfo($from),
            'MENU_CONTACT' => $this->sendContactAndLocationInfo($from),
            'BTN_HOW_TO_PAY' => $this->sendPaymentAndFeesInfo($from),
            'BTN_CONTACT_ACCOUNT' => $this->sendContactAndLocationInfo($from),
            default => $this->sendMainMenu($from)
        };
    }

    /**
     * Menu Principal avec Boutons Interactifs
     */
    public function sendMainMenu(string $from): void
    {
        $body = "👋 *Bienvenue sur le service interactif de l'Établissement Roi du Sahel !*\n\n"
            . "Je suis votre assistant virtuel scolaire 🤖.\n"
            . "Comment puis-je vous aider aujourd'hui ? Choisissez une option ou posez directement votre question.";

        $buttons = [
            ['id' => 'MENU_HORAIRES', 'title' => '⏰ Horaires & Cours'],
            ['id' => 'MENU_PROGRAMMES', 'title' => '📚 Cycles & Offres'],
            ['id' => 'MENU_TARIFS', 'title' => '💳 Frais & Modalités'],
        ];

        $this->whatsapp->sendInteractiveButtons($from, $body, $buttons, '🎓 ROI DU SAHEL - ASSISTANT');
    }

    /**
     * Horaires d'ouverture et des cours
     */
    public function sendSchedulesInfo(string $from): void
    {
        $text = "⏰ *HORAIRES DES COURS ET ADMINISTRATIFS*\n\n"
            . "🏫 *Administration & Secrétariat :*\n"
            . "• Du Lundi au Vendredi : 07h30 - 17h00\n"
            . "• Samedi : 08h00 - 12h00\n\n"
            . "🎒 *Horaires des Cours :*\n"
            . "• *Primaire* : 08h00 - 12h30 & 15h00 - 17h30\n"
            . "• *Collège* : 07h30 - 12h30 & 15h00 - 18h00\n"
            . "• *Lycée* : 07h30 - 13h30 & 15h00 - 18h30\n\n"
            . "_Pour toute demande spécifique, tapez *Contact*._";

        $this->whatsapp->sendTextMessage($from, $text);
    }

    /**
     * Programmes et Cycles d'enseignement
     */
    public function sendProgramsInfo(string $from): void
    {
        $text = "📚 *CYCLES ET FORMATIONS OFFERTES*\n\n"
            . "1️⃣ *Cycle Primaire (CI au CM2) :*\n"
            . "• Apprentissage bilingue, renforcement lecture/calcul, initiation informatique.\n\n"
            . "2️⃣ *Cycle Collège (6ème à la 3ème) :*\n"
            . "• Enseignement général complet, préparation rigoureuse au BEPC.\n\n"
            . "3️⃣ *Cycle Lycée (2nde à la Terminale) :*\n"
            . "• Séries Scientifiques (C, D)\n"
            . "• Séries Littéraires & Économiques (A, SES)\n"
            . "• Préparation d'excellence au Baccalauréat.";

        $this->whatsapp->sendTextMessage($from, $text);
    }

    /**
     * Frais de scolarité, Inscriptions et Moyens de Paiement
     */
    public function sendPaymentAndFeesInfo(string $from): void
    {
        $text = "💳 *FRAIS DE SCOLARITÉ ET MOYENS DE PAIEMENT*\n\n"
            . "💰 *Modalités de règlement :*\n"
            . "• Espèces ou chèque au guichet de la comptabilité de l'école.\n"
            . "• Mobile Money (Airtel Money, Moov Money, Wave, Orange Money).\n"
            . "• Virement bancaire.\n\n"
            . "📋 Les frais peuvent être réglés en *tranches trimestrielles* ou *annuellement* avec remise.\n\n"
            . "⚠️ _Tout paiement génère automatiquement un reçu électronique officiel._";

        $this->whatsapp->sendTextMessage($from, $text);
    }

    /**
     * Coordonnées, Localisation et Secrétariat
     */
    public function sendContactAndLocationInfo(string $from): void
    {
        $text = "📞 *CONTACTS & ACCÈS*\n\n"
            . "📍 *Adresse* : Complexe Scolaire Roi du Sahel\n"
            . "☎️ *Standard / Direction* : +227 00 00 00 00\n"
            . "💼 *Service Comptabilité* : +227 00 00 00 01\n"
            . "📧 *Email officiel* : contact@roidusahel.edu\n"
            . "🌐 *Portail web* : " . config('app.url') . "\n\n"
            . "_Nos équipes sont disponibles pour vous accueillir aux heures ouvrables._";

        $this->whatsapp->sendTextMessage($from, $text);
    }

    /**
     * Calendrier scolaire / Vacances
     */
    public function sendAcademicCalendar(string $from): void
    {
        $text = "🗓️ *CALENDRIER SCOLAIRE ET ÉVÉNEMENTS*\n\n"
            . "• *1er Trimestre* : Septembre - Décembre (Évaluations & Bulletin T1)\n"
            . "• *2ème Trimestre* : Janvier - Mars (Évaluations & Bulletin T2)\n"
            . "• *3ème Trimestre* : Avril - Juin (Examens officiels & Bulletin T3)\n\n"
            . "Consultez l'espace parent en ligne pour les dates exactes des compositions.";

        $this->whatsapp->sendTextMessage($from, $text);
    }

    /**
     * Message d'assistance en cas de question non reconnue
     */
    protected function sendDefaultFallback(string $from): void
    {
        $text = "🤖 Je n'ai pas bien compris votre demande.\n\n"
            . "Vous pouvez taper :\n"
            . "👉 *Menu* (pour afficher les options)\n"
            . "👉 *Horaires* (cours et secrétariat)\n"
            . "👉 *Programmes* (cycles d'enseignement)\n"
            . "👉 *Tarifs* ou *Paiement* (frais et modalités)\n"
            . "👉 *Contact* (joindre l'administration)";

        $this->whatsapp->sendTextMessage($from, $text);
    }

    /**
     * Vérifie si l'un des mots-clés est présent dans le texte utilisateur
     */
    protected function matchesAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}
