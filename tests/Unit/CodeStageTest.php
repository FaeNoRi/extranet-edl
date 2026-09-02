<?php

namespace Tests\Unit;

use App\Enums\CodeProduit;
use App\Support\CodeStage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CodeStageTest extends TestCase
{
    #[DataProvider('cas')]
    public function test_analyse(string $code, ?string $langue, ?CodeProduit $produit, bool $eligible): void
    {
        $c = CodeStage::analyser($code);

        $this->assertSame($langue, $c->langue);
        $this->assertSame($produit, $c->produit);
        $this->assertSame($eligible, $c->eligiblePlateforme());
    }

    public static function cas(): array
    {
        return [
            'OP anglais enfants' => ['AN-OP-8-9', 'Anglais', CodeProduit::Op, true],
            'FPC espagnol' => ['ES-FPC-AIS', 'Espagnol', CodeProduit::Fpc, true],
            'OP mandarin' => ['MA-OP-6-10', 'Mandarin', CodeProduit::Op, true],
            'LSF OP' => ['LSF-OP-B', 'Langue des signes française', CodeProduit::Op, true],
            'stage OP-ST exclu' => ['AN-OP-ST', 'Anglais', CodeProduit::Op, false],
            'LSF stage exclu' => ['LSF-OP-ST', 'Langue des signes française', CodeProduit::Op, false],
            'CLSH hors périmètre' => ['AN-CLSH', 'Anglais', null, false],
            'immersion scolaire hors périmètre' => ['AN-ES-JI', 'Anglais', null, false],
            'RE-F indéterminé' => ['RE-F', null, null, false],
        ];
    }
}
