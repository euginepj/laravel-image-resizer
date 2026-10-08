@props([
    'name' => 'image',
    'aspectRatio' => null, // e.g. 1, 16/9, 4/3 or null for free crop
    'targetWidth' => null,
    'targetHeight' => null,
    'formats' => ['webp', 'jpg', 'png'],
    'folder' => null,
    'uploadUrl' => \Illuminate\Support\Facades\Route::has('image-resizer.upload') ? route('image-resizer.upload') : '',
    'buttonLabel' => 'Select Image',
    'modalTitle' => 'Adjust & Crop Image',
])

<div x-data="imageCropperComponent({
        aspectRatio: {{ $aspectRatio ? json_encode($aspectRatio) : 'NaN' }},
        targetWidth: {{ $targetWidth ? json_encode($targetWidth) : 'null' }},
        targetHeight: {{ $targetHeight ? json_encode($targetHeight) : 'null' }},
        formats: {{ json_encode($formats) }},
        folder: {{ json_encode($folder) }},
        uploadUrl: {{ json_encode($uploadUrl) }},
        name: {{ json_encode($name) }}
    })"
    @keydown.escape.window="isOpen && closeModal()"
    class="image-resizer-cropper-container">

    <div class="flex items-center space-x-3">
        <label class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span>{{ $buttonLabel }}</span>
            <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" aria-label="{{ $buttonLabel }}" @change="onFileSelected($event)">
        </label>
        <span x-text="fileName" class="text-sm text-gray-500 truncate max-w-xs"></span>
    </div>

    <div x-show="isOpen"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-label="{{ $modalTitle }}"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 overflow-y-auto"
         style="display: none;">

        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col max-h-[92vh] border border-gray-100" @click.outside="closeModal()">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/75">
                <h3 class="text-base font-semibold text-gray-900">{{ $modalTitle }}</h3>
                <button @click="closeModal()" type="button" aria-label="Close" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 text-xl font-bold leading-none">&times;</button>
            </div>

            <div class="p-4 flex-1 overflow-hidden bg-slate-900 flex items-center justify-center min-h-[350px] relative">
                <img x-ref="imageElement" alt="Image to crop" class="max-w-full max-h-[50vh] block" />
                <div x-show="isUploading" class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white space-y-2">
                    <span class="text-sm font-medium">Processing &amp; converting formats...</span>
                </div>
            </div>

            <p x-show="errorMessage" x-text="errorMessage" class="px-6 py-2 text-sm text-red-600 bg-red-50" role="alert"></p>

            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center space-x-1.5">
                    <button type="button" @click="cropper && cropper.zoom(0.1)" title="Zoom in" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">Zoom +</button>
                    <button type="button" @click="cropper && cropper.zoom(-0.1)" title="Zoom out" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">Zoom -</button>
                    <button type="button" @click="cropper && cropper.rotate(-90)" title="Rotate left" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">&#x27F2; 90&deg;</button>
                    <button type="button" @click="cropper && cropper.rotate(90)" title="Rotate right" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">&#x27F3; 90&deg;</button>
                    <button type="button" @click="cropper && cropper.reset()" title="Reset" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">Reset</button>
                </div>

                <div class="flex items-center space-x-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Cancel</button>
                    <button type="button" @click="uploadCropped()" :disabled="isUploading" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm disabled:opacity-50">
                        <span x-text="isUploading ? 'Processing...' : 'Apply & Save'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@once
<style>[x-cloak]{display:none !important;}</style>
<link rel="stylesheet" href="{{ config('image-resizer.assets.cropper_css') }}" />
<script src="{{ config('image-resizer.assets.cropper_js') }}"></script>
<script>
window.addEventListener('load', () => {
    if (!window.Alpine) {
        console.warn('[image-resizer] Alpine.js was not found. The cropper component requires Alpine.js v3.');
    }
});

document.addEventListener('alpine:init', () => {
    Alpine.data('imageCropperComponent', (cfg) => ({
        isOpen: false,
        isUploading: false,
        fileName: '',
        fileObject: null,
        cropper: null,
        errorMessage: '',

        onFileSelected(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.errorMessage = '';
            this.fileObject = file;
            this.fileName = file.name;
            this.isOpen = true;

            const reader = new FileReader();
            reader.onload = (event) => {
                this.$refs.imageElement.src = event.target.result;
                this.$nextTick(() => this.initCropper());
            };
            reader.readAsDataURL(file);
        },

        initCropper() {
            if (this.cropper) {
                this.cropper.destroy();
            }
            this.cropper = new Cropper(this.$refs.imageElement, {
                aspectRatio: cfg.aspectRatio,
                viewMode: 2,
                autoCropArea: 1,
                responsive: true,
            });
        },

        closeModal() {
            this.isOpen = false;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },

        async uploadCropped() {
            if (!this.cropper || !this.fileObject) return;
            if (!cfg.uploadUrl) {
                this.errorMessage = 'Upload route is disabled. Pass an upload-url to the component.';
                return;
            }
            this.isUploading = true;
            this.errorMessage = '';

            const cropData = this.cropper.getData(true);
            const formData = new FormData();
            formData.append('image', this.fileObject);
            formData.append('crop_x', cropData.x);
            formData.append('crop_y', cropData.y);
            formData.append('crop_width', cropData.width);
            formData.append('crop_height', cropData.height);
            formData.append('crop_rotate', cropData.rotate);

            if (cfg.targetWidth) formData.append('target_width', cfg.targetWidth);
            if (cfg.targetHeight) formData.append('target_height', cfg.targetHeight);
            if (cfg.folder) formData.append('folder', cfg.folder);
            cfg.formats.forEach((fmt) => formData.append('formats[]', fmt));

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            formData.append('_token', csrfToken);

            try {
                const response = await fetch(cfg.uploadUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                    throw new Error(firstError || data.message || 'Image processing failed');
                }

                this.isUploading = false;
                this.closeModal();

                this.$dispatch('image-cropped', {
                    name: cfg.name,
                    paths: data.paths,
                    urls: data.urls,
                    base: data.base
                });
            } catch (err) {
                this.errorMessage = err.message;
                this.isUploading = false;
            }
        }
    }));
});
</script>
@endonce