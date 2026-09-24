<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WhatsAppBroadcastController extends Controller
{
    /**
     * Envoie une annonce institutionnelle ciblée ou générale
     */
    public function sendAnnouncement(Request $request, WhatsAppService $whatsapp)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string',
            'target' => 'required|in:all,primary,college,lycee,class',
            'class_id' => 'nullable|integer',
        ]);

        $query = DB::table('guardians')
            ->join('students', 'guardians.id', '=', 'students.guardian_id')
            ->whereNotNull('guardians.phone');

        // Filtrage selon la cible demandée
        if ($request->target === 'class' && $request->class_id) {
            $query->join('enrollments', 'students.id', '=', 'enrollments.student_id')
                  ->where('enrollments.school_class_id', $request->class_id);
        }

        $phoneNumbers = $query->distinct()->pluck('guardians.phone')->toArray();

        if (empty($phoneNumbers)) {
            return back()->with('warning', 'Aucun numéro de parent trouvé pour cette sélection.');
        }

        // Diffusion via le service
        $report = $whatsapp->broadcastAnnouncement(
            phoneNumbers: $phoneNumbers,
            title: $request->title,
            content: $request->content
        );

        return back()->with('success', "Communication envoyée avec succès : {$report['success']}/{$report['total']} destinataires notifiés.");
    }
}
