@props([
    'name' => 'image',
    'aspectRatio' => null, // e.g. 1, 16/9, 4/3, or null for free crop
    'targetWidth' => null,
    'targetHeight' => null,
    'formats' => ['webp', 'jpg', 'png'],
    'folder' => null,
    'uploadUrl' => route('image-resizer.upload'),
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
    class="image-resizer-cropper-container">

    <!-- Trigger Button -->
    <div class="flex items-center space-x-3">
        <label class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span>{{ $buttonLabel }}</span>
            <input type="file" accept="image/*" class="hidden" @change="onFileSelected($event)">
        </label>
        <span x-text="fileName" class="text-sm text-gray-500 truncate max-w-xs"></span>
    </div>

    <!-- Modal Dialog -->
    <div x-show="isOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 overflow-y-auto"
         style="display: none;">
        
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col max-h-[92vh] border border-gray-100" @click.outside="closeModal()">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/75">
                <h3 class="text-base font-semibold text-gray-900">{{ $modalTitle }}</h3>
                <button @click="closeModal()" type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 text-xl font-bold leading-none">&times;</button>
            </div>

            <!-- Canvas Area -->
            <div class="p-4 flex-1 overflow-hidden bg-slate-900 flex items-center justify-center min-h-[350px] relative">
                <img x-ref="imageElement" class="max-w-full max-h-[50vh] block" />
                <div x-show="isUploading" class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white space-y-2">
                    <svg class="animate-spin h-8 w-8 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">Processing & Converting formats...</span>
                </div>
            </div>

            <!-- Controls Toolbar -->
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center space-x-1.5">
                    <button type="button" @click="cropper && cropper.zoom(0.1)" title="Zoom In" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">ðŸ” +</button>
                    <button type="button" @click="cropper && cropper.zoom(-0.1)" title="Zoom Out" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">ðŸ” -</button>
                    <button type="button" @click="cropper && cropper.rotate(-90)" title="Rotate Left" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">âŸ² 90Â°</button>
                    <button type="button" @click="cropper && cropper.rotate(90)" title="Rotate Right" class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-100">âŸ³ 90Â°</button>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('imageCropperComponent', (cfg) => ({
        isOpen: false,
        isUploading: false,
        fileName: '',
        fileObject: null,
        cropper: null,

        onFileSelected(e) {
            const file = e.target.files[0];
            if (!file) return;

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
            this.isUploading = true;

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
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Image processing failed');
                }

                this.isUploading = false;
                this.closeModal();

                // Dispatch event with processed format paths and full urls
                this.$dispatch('image-cropped', {
                    name: cfg.name,
                    paths: data.paths,
                    urls: data.urls,
                    base: data.base
                });
            } catch (err) {
                alert('Error: ' + err.message);
                this.isUploading = false;
            }
        }
    }));
});
</script>
@endonce
