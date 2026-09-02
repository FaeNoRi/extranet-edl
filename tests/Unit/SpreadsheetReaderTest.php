<?php

namespace Tests\Unit;

use App\Support\SpreadsheetReader;
use PHPUnit\Framework\TestCase;

class SpreadsheetReaderTest extends TestCase
{
    public function test_lecture_csv_point_virgule(): void
    {
        $csv = tempnam(sys_get_temp_dir(), 'sr').'.csv';
        file_put_contents($csv, "Nom;Prénom;Email\nDURAND;Alice;a@b.fr\nMARTIN;Bob;\n");

        $rows = SpreadsheetReader::read($csv);

        $this->assertCount(2, $rows);
        $this->assertSame(['nom' => 'DURAND', 'prenom' => 'Alice', 'email' => 'a@b.fr'], $rows[0]);
        $this->assertSame('', $rows[1]['email']);
        unlink($csv);
    }

    public function test_lecture_xlsx_reel_gescof(): void
    {
        $fichier = dirname(__DIR__, 2).'/CLAUDE/edl_plus_gescof.xlsx';

        if (! is_file($fichier)) {
            $this->markTestSkipped('Fichier GESCOF de référence absent.');
        }

        $rows = SpreadsheetReader::read($fichier);

        $this->assertGreaterThan(20, count($rows));
        $this->assertArrayHasKey('numsession', $rows[0]);
        $this->assertArrayHasKey('accesplatforme', $rows[0]); // en-tête réelle (faute d'origine)
        $this->assertNotSame('', $rows[0]['numsession']);
    }

    public function test_entetes_normalisees(): void
    {
        $this->assertSame('accesplateforme', SpreadsheetReader::normaliseEntete('Accès Plateforme'));
        $this->assertSame('numsession', SpreadsheetReader::normaliseEntete(' NumSession '));
    }
}
