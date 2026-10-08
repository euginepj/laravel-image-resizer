@once
<style>
    [x-cloak] { display: none !important; }
    .cropper-view-box {
        outline: 2px solid #059e0b !important;
        outline-color: #f59e0b !important;
        border-radius: 4px;
    }
    .cropper-line, .cropper-point {
        background-color: #f59e0b !important;
    }
    .cropper-point.point-se {
        width: 8px !important;
        height: 8px !important;
    }
</style>
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
        cfg: cfg,
        isOpen: false,
        isUploading: false,
        fileName: '',
        fileObject: null,
        cropper: null,
        errorMessage: '',
        zoomLevel: 1,
        scaleX: 1,
        scaleY: 1,

        onFileSelected(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Reset file input so selecting the same file consecutively triggers @change
            e.target.value = '';

            this.errorMessage = '';
            this.fileObject = file;
            this.fileName = file.name;
            this.zoomLevel = 1;
            this.scaleX = 1;
            this.scaleY = 1;
            this.isOpen = true;

            const reader = new FileReader();
            reader.onload = (event) => {
                const img = this.$refs.imageElement;
                img.onload = () => {
                    this.initCropper();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        initCropper() {
            if (this.cropper) {
                this.cropper.destroy();
            }
            this.cropper = new Cropper(this.$refs.imageElement, {
                aspectRatio: cfg.aspectRatio,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.95,
                responsive: true,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                background: false,
                ready: () => {
                    // Recalculate canvas fit when modal becomes visible
                    this.cropper.crop();
                },
                zoom: (e) => {
                    this.zoomLevel = parseFloat(e.detail.ratio).toFixed(2);
                }
            });
        }, 

        onZoomSlider() {
            if (this.cropper) {
                this.cropper.zoomTo(parseFloat(this.zoomLevel));
            }
        },

        toggleFlipX() {
            if (!this.cropper) return;
            this.scaleX = this.scaleX === 1 ? -1 : 1;
            this.cropper.scaleX(this.scaleX);
        },

        toggleFlipY() {
            if (!this.cropper) return;
            this.scaleY = this.scaleY === 1 ? -1 : 1;
            this.cropper.scaleY(this.scaleY);
        },

        resetTransforms() {
            if (!this.cropper) return;
            this.cropper.reset();
            this.zoomLevel = 1;
            this.scaleX = 1;
            this.scaleY = 1;
        },

        closeModal() {
            this.isOpen = false;
            this.scaleX = 1;
            this.scaleY = 1;
            this.zoomLevel = 1;
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
