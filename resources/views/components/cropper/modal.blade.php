<template x-teleport="body">
    <div x-show="isOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-label="{{ $modalTitle }}"
         class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/85 backdrop-blur-md p-4 sm:p-6 overflow-hidden"
         style="display: none;">

        <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden flex flex-col max-h-[92vh] border border-gray-200/50 dark:border-neutral-800 transform transition-all"
             @click.outside="closeModal()">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-100 dark:border-neutral-800 flex justify-between items-center bg-gray-50/75 dark:bg-neutral-900/75">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                        </svg>
                        {{ $modalTitle }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Drag to reposition, scroll or use slider to zoom.</p>
                </div>

                <div class="flex items-center gap-3">
                    <template x-if="cfg.targetWidth && cfg.targetHeight">
                        <span class="text-xs font-mono font-medium px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 rounded-lg">
                            <span x-text="cfg.targetWidth"></span> &times; <span x-text="cfg.targetHeight"></span> px
                        </span>
                    </template>
                    <button @click="closeModal()" type="button" aria-label="Close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-neutral-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Cropper Studio Canvas -->
            <div class="relative flex-1 bg-neutral-950 flex items-center justify-center h-[62vh] min-h-[440px] max-h-[70vh] w-full overflow-hidden select-none">
                <img x-ref="imageElement" alt="Image to crop" class="max-w-full max-h-full block object-contain" />
                <!-- Processing Overlay -->
                <div x-show="isUploading" class="absolute inset-0 bg-black/75 backdrop-blur-sm flex flex-col items-center justify-center text-white space-y-3 z-30">
                    <svg class="animate-spin h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-sm font-semibold tracking-wide">Processing &amp; Optimizing Image...</span>
                </div>
            </div>

            <!-- Error Notice -->
            <div x-show="errorMessage" x-text="errorMessage" class="px-6 py-2.5 text-xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/50 border-t border-rose-100 dark:border-rose-900/40" role="alert"></div>

            <!-- Modal Footer / Control Bar -->
            @include('image-resizer::components.cropper.controls')

        </div>
    </div>
</template>
