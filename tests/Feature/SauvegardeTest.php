<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class SauvegardeTest extends TestCase
{
    private string $dossier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dossier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'edl-sauvegarde-'.uniqid();
        File::ensureDirectoryExists($this->dossier);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dossier);

        parent::tearDown();
    }

    public function test_la_configuration_sauvegarde_les_donnees_sans_les_secrets_ni_le_code(): void
    {
        $inclus = collect(config('backup.backup.source.files.include'))->map(fn ($c) => str_replace('\\', '/', $c));
        $exclus = collect(config('backup.backup.source.files.exclude'))->map(fn ($c) => str_replace('\\', '/', $c));
        $stockage = str_replace('\\', '/', storage_path('app'));

        $this->assertEqualsCanonicalizing(["{$stockage}/private", "{$stockage}/public"], $inclus->all());
        $this->assertContains("{$stockage}/private/gescof", $exclus->all());
        $this->assertSame(storage_path('app'), config('backup.backup.source.files.relative_path'));
        $this->assertSame('default', config('backup.backup.encryption'));
        $this->assertContains('backups', config('backup.backup.destination.disks'));
    }

    public function test_les_archives_sont_conservees_au_plus_28_jours(): void
    {
        $strategie = config('backup.cleanup.default_strategy');

        $this->assertSame(28, $strategie['keep_all_backups_for_days'] + $strategie['keep_daily_backups_for_days']);
        $this->assertSame(0, $strategie['keep_weekly_backups_for_weeks']);
        $this->assertSame(0, $strategie['keep_monthly_backups_for_months']);
        $this->assertSame(0, $strategie['keep_yearly_backups_for_years']);
        $this->assertStringContainsString('28 jours', config('edl.legal.conservation_sauvegardes'));
    }

    public function test_la_sauvegarde_est_planifiee(): void
    {
        $commandes = collect(app(Schedule::class)->events())
            ->map(fn (Event $e) => $e->command.' @ '.$e->expression)
            ->implode("\n");

        $this->assertStringContainsString('backup:run', $commandes);
        $this->assertStringContainsString('backup:clean', $commandes);
        $this->assertStringContainsString('backup:monitor', $commandes);
        $this->assertMatchesRegularExpression('/backup:run.* 30 2 \* \* \*/', $commandes);
    }

    public function test_une_sauvegarde_est_chiffree_puis_restaurable(): void
    {
        $source = $this->dossier.DIRECTORY_SEPARATOR.'source';
        $depot = $this->dossier.DIRECTORY_SEPARATOR.'depot';
        File::ensureDirectoryExists($source.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'documents');
        File::put($source.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'documents'.DIRECTORY_SEPARATOR.'convention.pdf', 'contenu du document');

        config([
            'backup.backup.source.files.include' => [$source.DIRECTORY_SEPARATOR.'private'],
            'backup.backup.source.files.exclude' => [],
            'backup.backup.source.files.relative_path' => $source,
            'backup.backup.source.databases' => [],
            'backup.backup.destination.disks' => ['backups'],
            'backup.backup.temporary_directory' => $this->dossier.DIRECTORY_SEPARATOR.'temp',
            'backup.backup.password' => 'mot-de-passe-de-test',
            'filesystems.disks.backups.root' => $depot,
        ]);

        $this->artisan('backup:run', ['--only-files' => true, '--disable-notifications' => true])->assertSuccessful();

        $archives = glob($depot.DIRECTORY_SEPARATOR.config('backup.backup.name').DIRECTORY_SEPARATOR.'*.zip');
        $this->assertCount(1, $archives);

        $zip = new ZipArchive;
        $zip->open($archives[0]);

        // Sans le mot de passe, le contenu est illisible…
        $this->assertFalse($zip->getFromName('private\\documents\\convention.pdf'));
        $this->assertFalse($zip->getFromName('private/documents/convention.pdf'));

        // … avec, on retrouve le fichier à son chemin relatif.
        $zip->setPassword('mot-de-passe-de-test');
        $restaure = $zip->getFromName('private/documents/convention.pdf') ?: $zip->getFromName('private\\documents\\convention.pdf');
        $this->assertSame('contenu du document', $restaure);
        $zip->close();
    }
}
