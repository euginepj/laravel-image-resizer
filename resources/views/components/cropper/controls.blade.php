<div class="px-6 py-4 bg-gray-50/80 dark:bg-neutral-900 border-t border-gray-100 dark:border-neutral-800 flex flex-wrap items-center justify-between gap-4">
    <!-- Zoom & Transform Controls -->
    <div class="flex flex-wrap items-center gap-3">
        <!-- Zoom Slider -->
        <div class="flex items-center gap-2 bg-white dark:bg-neutral-800 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
            <button type="button" @click="cropper && cropper.zoom(-0.1)" class="text-gray-500 hover:text-gray-900 dark:hover:text-white" title="Zoom Out">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            </button>
            <input type="range" min="0.1" max="3" step="0.05" x-model="zoomLevel" @input="onZoomSlider()" class="w-24 h-1.5 bg-gray-200 dark:bg-neutral-700 rounded-lg appearance-none cursor-pointer accent-amber-500">
            <button type="button" @click="cropper && cropper.zoom(0.1)" class="text-gray-500 hover:text-gray-900 dark:hover:text-white" title="Zoom In">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
        </div>

        <!-- Rotation & Flip -->
        <div class="flex items-center gap-1 bg-white dark:bg-neutral-800 p-1 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
            <button type="button" @click="cropper && cropper.rotate(-90)" title="Rotate Left (90°)" class="p-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-3-3m3 3l3-3M3 10l3 3M3 10l3-3"/></svg>
            </button>
            <button type="button" @click="cropper && cropper.rotate(90)" title="Rotate Right (90°)" class="p-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m0 0l3-3m-3 3l-3-3m15-4l-3-3m3 3l-3 3"/></svg>
            </button>
            <button type="button" @click="toggleFlipX()" title="Flip Horizontal" class="p-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </button>
            <button type="button" @click="toggleFlipY()" title="Flip Vertical" class="p-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8v12m0 0l-4-4m4 4l4-4m6 0V4m0 0l4 4m-4-4l-4 4"/></svg>
            </button>
            <button type="button" @click="resetTransforms()" title="Reset Crop & Position" class="p-1.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition px-2">
                Reset
            </button>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-2.5">
        <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-neutral-800 rounded-xl transition">
            Cancel
        </button>
        <button type="button" @click="uploadCropped()" :disabled="isUploading" class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg disabled:opacity-50 transition transform active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span x-text="isUploading ? 'Applying...' : 'Apply & Save'"></span>
        </button>
    </div>
</div>
