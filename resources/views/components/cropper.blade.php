@props([
    'name' => 'image',
    'aspectRatio' => null, // e.g. 1, 16/9, 4/3 or null for free crop
    'targetWidth' => null,
    'targetHeight' => null,
    'formats' => ['webp', 'jpg', 'png'],
    'folder' => null,
    'uploadUrl' => \Illuminate\Support\Facades\Route::has('image-resizer.upload') ? route('image-resizer.upload') : '',
    'buttonLabel' => 'Select Image',
    'modalTitle' => 'Adjust & Reposition Photo',
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
    {{ $attributes->merge(['class' => 'image-resizer-cropper-container']) }}>

    <!-- Trigger Button -->
    @if(isset($trigger))
        <div @click="$refs.fileInput.click()">
            {{ $trigger }}
            <input x-ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" aria-label="{{ $buttonLabel }}" @change="onFileSelected($event)">
        </div>
    @else
        <div class="flex items-center space-x-3">
            <label class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-neutral-700 text-sm font-medium rounded-xl shadow-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-700 cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ $buttonLabel }}</span>
                <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" aria-label="{{ $buttonLabel }}" @change="onFileSelected($event)">
            </label>
            <span x-text="fileName" class="text-sm text-gray-500 truncate max-w-xs"></span>
        </div>
    @endif

    <!-- Teleported Fullscreen Studio Modal -->
    @include('image-resizer::components.cropper.modal')
</div>

<!-- Component Assets and Alpine Controller Script -->
@include('image-resizer::components.cropper.scripts')