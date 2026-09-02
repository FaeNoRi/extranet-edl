<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Lecteur de tableur minimaliste et sans dépendance, suffisant pour les
 * imports GESCOF : lit la première feuille d'un .xlsx (valeurs texte) ou un
 * fichier .csv, et renvoie chaque ligne sous forme de tableau associatif
 * indexé par l'en-tête (normalisé).
 */
class SpreadsheetReader
{
    /**
     * @return list<array<string, string>>
     */
    public static function read(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $rows = match ($extension) {
            'xlsx' => self::readXlsx($path),
            'csv', 'txt' => self::readCsv($path),
            default => throw new RuntimeException("Format non pris en charge : .{$extension}"),
        };

        if ($rows === []) {
            return [];
        }

        $entetes = array_map(self::normaliseEntete(...), array_shift($rows));

        return array_map(function (array $ligne) use ($entetes): array {
            $assoc = [];
            foreach ($entetes as $i => $entete) {
                if ($entete !== '') {
                    $assoc[$entete] = trim((string) ($ligne[$i] ?? ''));
                }
            }

            return $assoc;
        }, $rows);
    }

    public static function normaliseEntete(string $valeur): string
    {
        $valeur = self::sansAccents(strtolower(trim($valeur)));

        return preg_replace('/[^a-z0-9]+/', '', $valeur) ?? '';
    }

    public static function sansAccents(string $valeur): string
    {
        $remplacements = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y',
            'œ' => 'oe', 'æ' => 'ae',
        ];

        $valeur = strtr($valeur, $remplacements);

        // Filet de sécurité pour les caractères accentués restants.
        $translit = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valeur);

        return $translit !== false ? $translit : $valeur;
    }

    /**
     * @return list<list<string>>
     */
    private static function readCsv(string $path): array
    {
        $contenu = file_get_contents($path);
        if ($contenu === false) {
            throw new RuntimeException("Fichier illisible : {$path}");
        }

        // Détection simple du séparateur sur la première ligne.
        $premiereLigne = strtok($contenu, "\n") ?: '';
        $separateur = substr_count($premiereLigne, ';') > substr_count($premiereLigne, ',') ? ';' : ',';

        $rows = [];
        $handle = fopen($path, 'r');
        while (($cells = fgetcsv($handle, 0, $separateur, '"', '')) !== false) {
            if ($cells === [null] || $cells === false) {
                continue;
            }
            $rows[] = array_map(fn ($c) => (string) $c, $cells);
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @return list<list<string>>
     */
    private static function readXlsx(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException("Archive .xlsx illisible : {$path}");
        }

        $strings = self::sharedStrings($zip);
        $sheetXml = self::premiereFeuille($zip);
        $zip->close();

        $xml = simplexml_load_string($sheetXml);
        if ($xml === false) {
            throw new RuntimeException('Feuille .xlsx invalide.');
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            $colAttendue = 0;
            foreach ($row->c as $c) {
                $index = self::indexColonne((string) $c['r']);
                // Comble les colonnes vides intermédiaires.
                while ($colAttendue < $index) {
                    $cells[] = '';
                    $colAttendue++;
                }

                $valeur = (string) $c->v;
                if ((string) $c['t'] === 's') {
                    $valeur = $strings[(int) $valeur] ?? '';
                } elseif ((string) $c['t'] === 'inlineStr') {
                    $valeur = (string) $c->is->t;
                }

                $cells[] = $valeur;
                $colAttendue++;
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private static function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $sx = simplexml_load_string($xml);
        $strings = [];
        foreach ($sx->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;

                continue;
            }
            $texte = '';
            foreach ($si->r as $run) {
                $texte .= (string) $run->t;
            }
            $strings[] = $texte;
        }

        return $strings;
    }

    private static function premiereFeuille(ZipArchive $zip): string
    {
        foreach (['xl/worksheets/sheet1.xml', 'xl/worksheets/Sheet1.xml'] as $nom) {
            $xml = $zip->getFromName($nom);
            if ($xml !== false) {
                return $xml;
            }
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nom = $zip->getNameIndex($i);
            if ($nom !== false && str_starts_with($nom, 'xl/worksheets/') && str_ends_with($nom, '.xml')) {
                return (string) $zip->getFromName($nom);
            }
        }

        throw new RuntimeException('Aucune feuille trouvée dans le fichier .xlsx.');
    }

    private static function indexColonne(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $lettres = $m[1] ?? 'A';
        $index = 0;
        foreach (str_split($lettres) as $lettre) {
            $index = $index * 26 + (ord($lettre) - ord('A') + 1);
        }

        return $index - 1;
    }
}
