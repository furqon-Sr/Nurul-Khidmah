{{-- Cropper.js CDN + modal global (sertakan sekali di layout admin) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div id="imageCropModal"
     class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60"
     role="dialog"
     aria-modal="true"
     aria-labelledby="imageCropModalTitle">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[95vh] flex flex-col overflow-hidden border border-gray-200 dark:border-slate-700">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 flex items-start justify-between gap-4">
            <div>
                <h2 id="imageCropModalTitle" class="text-lg font-bold text-gray-900 dark:text-slate-100">Sesuaikan gambar</h2>
                <p class="text-sm text-gray-600 dark:text-slate-400 mt-0.5">
                    Geser dan zoom gambar, lalu atur area crop. Rasio: <span id="imageCropAspectLabel" class="font-semibold text-emerald-700 dark:text-emerald-400">—</span>
                </p>
            </div>
            <button type="button" id="imageCropCancel" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-1" aria-label="Tutup">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-auto p-4 bg-gray-100 dark:bg-slate-950">
            <div class="max-h-[min(60vh,480px)] w-full bg-black/5 dark:bg-black/30 rounded-lg overflow-hidden">
                <img id="imageCropPreview" src="" alt="Pratinjau crop" class="block max-w-full">
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-3">
                Tips: gunakan scroll mouse untuk zoom. Area di dalam kotak crop adalah bagian yang akan disimpan ke server.
            </p>
        </div>

        <div class="px-5 py-4 border-t border-gray-200 dark:border-slate-700 flex flex-wrap items-center justify-end gap-2 bg-gray-50 dark:bg-slate-900">
            <button type="button" id="imageCropReset" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-800 rounded-lg transition">
                Reset area
            </button>
            <button type="button" id="imageCropCancelFooter" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                Batal
            </button>
            <button type="button" id="imageCropApply" class="px-5 py-2 text-sm font-semibold text-white bg-[#0D6B3A] hover:bg-emerald-800 rounded-lg shadow transition">
                Terapkan crop
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('imageCropModal');
    const previewImg = document.getElementById('imageCropPreview');
    const aspectLabelEl = document.getElementById('imageCropAspectLabel');
    let cropper = null;
    let activeInput = null;
    let objectUrl = null;

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        previewImg.removeAttribute('src');
    }

    function clearCropState(input) {
        const wrapper = input.closest('.image-crop-wrapper');
        if (!wrapper) return;
        wrapper.querySelectorAll('input[type=hidden]').forEach(function (h) {
            if (h.name.endsWith('_crop_x') || h.name.endsWith('_crop_y') || h.name.endsWith('_crop_width') || h.name.endsWith('_crop_height')) {
                h.value = '';
            }
        });
        const status = wrapper.querySelector('.image-crop-status');
        if (status) status.classList.add('hidden');
    }

    document.querySelectorAll('.image-crop-input').forEach(function (input) {
        input.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const wrapper = input.closest('.image-crop-wrapper');
            if (['svg', 'ico'].includes(ext)) {
                let skip = wrapper && wrapper.querySelector('input[name="' + input.name + '_crop_skip"]');
                if (!skip && wrapper) {
                    skip = document.createElement('input');
                    skip.type = 'hidden';
                    skip.name = input.name + '_crop_skip';
                    wrapper.appendChild(skip);
                }
                if (skip) skip.value = '1';
                const status = wrapper && wrapper.querySelector('.image-crop-status');
                if (status) {
                    status.querySelector('p').textContent = '✓ File ' + ext.toUpperCase() + ' siap diunggah (tanpa crop)';
                    const preview = status.querySelector('.image-crop-result-preview');
                    if (preview) preview.classList.add('hidden');
                    status.classList.remove('hidden');
                }
                return;
            } else if (wrapper) {
                const skip = wrapper.querySelector('input[name="' + input.name + '_crop_skip"]');
                if (skip) skip.value = '';
            }

            activeInput = input;
            clearCropState(input);

            const ratioRaw = input.dataset.aspectRatio;
            const ratio = ratioRaw !== '' && ratioRaw !== undefined ? parseFloat(ratioRaw) : NaN;
            aspectLabelEl.textContent = input.dataset.aspectLabel || 'Bebas';

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            previewImg.src = objectUrl;

            openModal();

            previewImg.onload = function () {
                if (cropper) cropper.destroy();
                const options = {
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.92,
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                };
                if (!isNaN(ratio) && ratio > 0) {
                    options.aspectRatio = ratio;
                }
                cropper = new Cropper(previewImg, options);
            };
        });
    });

    function applyCrop() {
        if (!cropper || !activeInput) return;

        const data = cropper.getData(true);
        const prefix = activeInput.name;
        const wrapper = activeInput.closest('.image-crop-wrapper');

        ['_crop_x', '_crop_y', '_crop_width', '_crop_height'].forEach(function (suffix) {
            const key = prefix + suffix;
            const field = wrapper ? wrapper.querySelector('input[name="' + key + '"]') : null;
            if (!field) return;
            if (suffix === '_crop_x') field.value = Math.round(data.x);
            if (suffix === '_crop_y') field.value = Math.round(data.y);
            if (suffix === '_crop_width') field.value = Math.round(data.width);
            if (suffix === '_crop_height') field.value = Math.round(data.height);
        });

        const canvas = cropper.getCroppedCanvas({ maxWidth: 960, maxHeight: 960, imageSmoothingQuality: 'high' });
        const status = wrapper && wrapper.querySelector('.image-crop-status');
        const resultPreview = wrapper && wrapper.querySelector('.image-crop-result-preview');
        if (canvas && resultPreview && status) {
            resultPreview.src = canvas.toDataURL('image/jpeg', 0.92);
            status.classList.remove('hidden');
        }

        closeModal();
        activeInput = null;
    }

    function cancelCrop() {
        if (activeInput) {
            activeInput.value = '';
            clearCropState(activeInput);
        }
        closeModal();
        activeInput = null;
    }

    document.getElementById('imageCropApply').addEventListener('click', applyCrop);
    document.getElementById('imageCropCancel').addEventListener('click', cancelCrop);
    document.getElementById('imageCropCancelFooter').addEventListener('click', cancelCrop);
    document.getElementById('imageCropReset').addEventListener('click', function () {
        if (cropper) cropper.reset();
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) cancelCrop();
    });

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const pending = form.querySelector('.image-crop-input');
            if (!pending) return;

            form.querySelectorAll('.image-crop-input').forEach(function (input) {
                if (!input.files || !input.files.length) return;
                const wrapper = input.closest('.image-crop-wrapper');
                const skip = wrapper && wrapper.querySelector('input[name="' + input.name + '_crop_skip"]');
                if (skip && skip.value === '1') return;
                const w = wrapper && wrapper.querySelector('input[name="' + input.name + '_crop_width"]');
                if (w && !w.value) {
                    e.preventDefault();
                    alert('Silakan selesaikan crop untuk "' + (input.labels && input.labels[0] ? input.labels[0].textContent.trim() : input.name) + '" — pilih gambar, atur area, lalu klik Terapkan crop.');
                    input.focus();
                }
            });
        });
    });
});
</script>
