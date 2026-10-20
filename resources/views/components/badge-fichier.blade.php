{{-- Étiquette du type de fichier (PDF, MP3…) devant un nom de document ou de ressource. --}}
@props(['fichier'])

@php
    $extension = strtoupper(pathinfo((string) $fichier, PATHINFO_EXTENSION));
    $teinte = match (true) {
        $extension === 'PDF' => 'bg-edl-rose/10 text-edl-rose-fonce',
        in_array($extension, ['MP3', 'WAV', 'M4A', 'OGG'], true) => 'bg-edl-violet/10 text-edl-violet-fonce',
        in_array($extension, ['MP4', 'MOV', 'WEBM', 'AVI'], true) => 'bg-edl-orange/15 text-edl-orange-fonce',
        in_array($extension, ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP'], true) => 'bg-edl-vert/15 text-edl-vert-fonce',
        in_array($extension, ['DOC', 'DOCX', 'ODT', 'PPT', 'PPTX', 'ODP', 'XLS', 'XLSX', 'ODS', 'TXT'], true) => 'bg-edl-bleu/10 text-edl-bleu-fonce',
        default => 'bg-edl-marron/10 text-edl-marron',
    };
@endphp

@if ($extension !== '')
    <span {{ $attributes->class(['inline-block min-w-[2.75rem] flex-none rounded-md px-1.5 py-0.5 text-center text-xs font-semibold tracking-wide', $teinte]) }}>{{ $extension }}</span>
@endif
