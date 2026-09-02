<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-transparent bg-edl-bleu px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-edl-vert-fonce focus:bg-edl-vert-fonce focus:outline-none focus:ring-2 focus:ring-edl-bleu focus:ring-offset-2 active:bg-edl-vert-fonce']) }}>
    {{ $slot }}
</button>
