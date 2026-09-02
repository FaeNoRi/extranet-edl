<?php

namespace App\Services\Gescof;

use App\Models\GescofImport;

class GescofImportReport
{
    public int $lignesLues = 0;

    public int $lignesIgnorees = 0;

    public int $comptesCrees = 0;

    public int $comptesReactives = 0;

    public int $comptesDisparus = 0;

    public int $sessionsCreees = 0;

    public int $sessionsMaj = 0;

    /** @var list<int> identifiants des comptes créés avec un e-mail exploitable */
    public array $comptesANotifier = [];

    /** @var list<array{ligne: int|null, type: string, message: string}> */
    public array $anomalies = [];

    /** Enregistrement persistant de cette exécution. */
    public ?GescofImport $import = null;

    public ?string $fichierPath = null;

    public function __construct(
        public string $fichierNom = '',
        public bool $applique = false,
    ) {}

    public function ignorer(int $ligne, string $type, string $message): void
    {
        $this->lignesIgnorees++;
        $this->anomalie($ligne, $type, $message);
    }

    public function anomalie(?int $ligne, string $type, string $message): void
    {
        $this->anomalies[] = compact('ligne', 'type', 'message');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'fichier_nom' => $this->fichierNom,
            'applique' => $this->applique,
            'lignes_lues' => $this->lignesLues,
            'lignes_ignorees' => $this->lignesIgnorees,
            'comptes_crees' => $this->comptesCrees,
            'comptes_reactives' => $this->comptesReactives,
            'comptes_disparus' => $this->comptesDisparus,
            'sessions_creees' => $this->sessionsCreees,
            'sessions_maj' => $this->sessionsMaj,
            'comptes_a_notifier' => count($this->comptesANotifier),
            'anomalies' => $this->anomalies,
        ];
    }
}
