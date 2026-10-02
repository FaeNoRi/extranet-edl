<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Ressource;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TelechargementController extends Controller
{
    public function document(Request $request, Document $document): StreamedResponse
    {
        $session = auth()->user()->sessionStagiaire();

        // Document commun à la structure, ou document de la session du stagiaire.
        abort_unless(
            is_null($document->session_formation_id)
            || ($session && $document->session_formation_id === $session->id),
            403,
        );
        abort_unless(Storage::exists($document->chemin_fichier), 404);

        // Un stagiaire OP n'a jamais le droit de télécharger : consultation à
        // l'écran uniquement (cf. lecteur durci), quel que soit le paramètre reçu.
        if (auth()->user()->isStagiaireOp()) {
            return $this->lectureSeule($request, $document->chemin_fichier);
        }

        return Storage::download($document->chemin_fichier, $document->nom_fichier_original);
    }

    public function apercuDocument(Document $document): View
    {
        $session = auth()->user()->sessionStagiaire();

        abort_unless(
            is_null($document->session_formation_id)
            || ($session && $document->session_formation_id === $session->id),
            403,
        );
        abort_unless(Storage::exists($document->chemin_fichier), 404);

        return view('stagiaire.documents.apercu', [
            'document' => $document,
            'url' => route('stagiaire.documents.download', $document),
            'type' => $this->typeDepuisExtension($document->nom_fichier_original),
        ]);
    }

    public function apercuRessource(Ressource $ressource): View
    {
        $this->autoriserRessource($ressource);

        return view('stagiaire.ressources.apercu', [
            'ressource' => $ressource,
            'url' => route('stagiaire.ressources.download', $ressource),
            'type' => $ressource->type_fichier,
        ]);
    }

    public function ressource(Request $request, Ressource $ressource): StreamedResponse
    {
        $this->autoriserRessource($ressource);

        // Un stagiaire OP n'a jamais le droit de télécharger, même en forçant
        // l'URL : consultation à l'écran uniquement.
        if (auth()->user()->isStagiaireOp()) {
            return $this->lectureSeule($request, $ressource->chemin_fichier);
        }

        if ($request->boolean('apercu')) {
            return Storage::response($ressource->chemin_fichier);
        }

        $ressource->increment('nb_telechargement');

        return Storage::download($ressource->chemin_fichier, $ressource->nom_fichier_original);
    }

    /**
     * La ressource doit être transmise via une séance réalisée de la session du
     * stagiaire, ou rattachée à un module du référentiel d'une telle séance.
     */
    private function autoriserRessource(Ressource $ressource): void
    {
        $session = auth()->user()->sessionStagiaire();
        abort_unless($session, 403);
        abort_unless($this->ressourceAutorisee($ressource, $session->id), 403);
        abort_unless(Storage::exists($ressource->chemin_fichier), 404);
    }

    /**
     * Réponse en lecture seule pour un stagiaire OP : jamais de pièce jointe,
     * pas de mise en cache, et refus d'une navigation directe vers le fichier
     * (le navigateur le ferait afficher par son lecteur natif, avec boutons
     * d'enregistrement/impression). Seuls les appels du lecteur durci passent
     * (fetch, <video>, <img>). L'en-tête Sec-Fetch-Dest est un frein, pas une
     * garantie : il disparaît avec un client qui ne l'envoie pas.
     */
    private function lectureSeule(Request $request, string $chemin): StreamedResponse
    {
        abort_if(
            in_array($request->header('Sec-Fetch-Dest'), ['document', 'iframe', 'frame', 'embed', 'object'], true),
            403,
            'Consultation uniquement depuis le lecteur intégré.',
        );

        $reponse = Storage::response($chemin);
        $reponse->headers->set('Cache-Control', 'no-store, private');

        return $reponse;
    }

    private function typeDepuisExtension(string $nomFichier): string
    {
        return match (strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION))) {
            'pdf' => 'pdf',
            'mp4', 'mov', 'avi', 'webm', 'mkv' => 'video',
            'mp3', 'wav', 'ogg' => 'audio',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            default => 'autre',
        };
    }

    private function ressourceAutorisee(Ressource $ressource, int $sessionId): bool
    {
        $stagiaireId = auth()->id();

        $seances = Seance::where('session_formation_id', $sessionId)
            ->whereDate('date', '<=', Carbon::today())
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $stagiaireId));

        // NB : wherePivot() n'existe pas sur le query builder reçu par whereHas()
        // (il est propre à l'instance de relation) ; il faut qualifier la
        // colonne de la table pivot directement, sous peine d'une colonne
        // "pivot" inconnue en SQL.
        $transmise = (clone $seances)
            ->whereHas('ressources', fn ($q) => $q->whereKey($ressource->id)->where('seances_ressources.transmis', true))
            ->exists();

        $referentiel = (clone $seances)
            ->whereHas('referentiels.ressources', fn ($q) => $q->whereKey($ressource->id))
            ->exists();

        return $transmise || $referentiel;
    }
}
