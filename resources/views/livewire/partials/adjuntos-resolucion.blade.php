{{-- Adjuntar PDFs a la resolución y verlos en un modal. Usa $archivosPDF / tempArchivos de CrearResolucion. --}}
<div x-data="{ mostrarModalPdf: false, pdfIndex: null }">
            <!-- Sección de PDFs -->
            <div class="mt-6 px-4">
                <div class="flex items-center gap-4 mb-4">
                    <label class="flex items-center gap-2 rounded-lg bg-ipv-blue px-4 py-2 text-white transition-colors hover:bg-ipv-blue-dark cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar PDF
                        <input type="file" wire:model="tempArchivos" multiple accept=".pdf" class="hidden" />
                    </label>
                    <span class="text-sm text-ipv-ink/60 dark:text-ipv-ink-dark/60">
                        {{ count($archivosPDF) }} archivo(s) cargado(s)
                    </span>
                </div>

                @if(count($archivosPDF) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($archivosPDF as $index => $archivo)
                            <div wire:key="pdf-{{ $index }}-{{ $archivo->getFilename() }}" @click="mostrarModalPdf = true; pdfIndex = {{ $index }}" class="sirex-glass-card group flex cursor-pointer items-center justify-between rounded-[14px] p-4 transition-all hover:border-ipv-blue dark:hover:border-ipv-blue-light">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="bg-red-100 dark:bg-red-900 rounded p-2 flex-shrink-0 group-hover:bg-red-200 dark:group-hover:bg-red-800 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="truncate text-sm text-ipv-ink/80 dark:text-ipv-ink-dark/85" title="{{ $archivo->getClientOriginalName() }}">
                                        {{ $archivo->getClientOriginalName() }}
                                    </span>
                                </div>
                                <button type="button" wire:click.stop="removeArchivo({{ $index }})" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Modal para ver PDF -->
            <div x-show="mostrarModalPdf" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="sirex-modal-fondo"
                 style="display: none;"
                 @click.self="mostrarModalPdf = false"
                 @keydown.escape.window="mostrarModalPdf = false">
                <div class="sirex-modal sirex-modal-xl" style="height: 80vh;" role="dialog" aria-modal="true" aria-labelledby="titulo-pdf">
                    <div class="sirex-modal-cabeza" style="padding-bottom: 0.875rem;">
                        <div class="sirex-modal-icono sirex-modal-icono-peligro">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="titulo-pdf" class="sirex-modal-titulo">Documento PDF</h3>
                        </div>
                        <button type="button" @click="mostrarModalPdf = false" class="sirex-modal-cerrar" aria-label="Cerrar">
                            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-hidden border-t border-ipv-blue/10 bg-black/[0.03] dark:border-white/10 dark:bg-white/[0.03]">
                        <iframe
                            :src="pdfIndex !== null ? '/resoluciones/descargar-pdf/' + pdfIndex : ''"
                            class="h-full w-full"
                            frameborder="0"
                        ></iframe>
                    </div>
                </div>
            </div>
</div>
