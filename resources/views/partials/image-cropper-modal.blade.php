{{-- Cropper.js CDN + modal global (sertakan sekali di layout admin) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div id="imageCropModal"
     class="fixed inset-0 z-[100] hidden items-center justify-center p-3 sm:p-4 bg-black/70 backdrop-blur-sm"
     role="dialog"
     aria-modal="true"
     aria-labelledby="imageCropModalTitle">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[96vh] flex flex-col overflow-hidden border border-gray-200 dark:border-slate-700">
        {{-- Header Modal --}}
        <div class="px-5 py-3.5 border-b border-gray-200 dark:border-slate-700 flex items-start justify-between gap-4 bg-white dark:bg-slate-900">
            <div>
                <h2 id="imageCropModalTitle" class="text-lg font-bold text-gray-900 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Sesuaikan Gambar
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-slate-400 mt-0.5">
                    Geser/zoom gambar, lalu atur kotak crop. Rasio aktif: <span id="imageCropAspectLabel" class="font-semibold text-emerald-700 dark:text-emerald-400">—</span>
                </p>
            </div>
            <button type="button" id="imageCropCancel" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Toolbar Interaktif: Rasio & Transformasi --}}
        <div class="px-4 py-2.5 bg-gray-50 dark:bg-slate-800/90 border-b border-gray-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs">
            {{-- Pilihan Rasio --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-gray-600 dark:text-slate-400 font-medium mr-1 text-[11px] uppercase tracking-wider">Rasio:</span>
                <button type="button" id="cropRatioFree" class="crop-ratio-btn px-2.5 py-1 rounded-md font-medium border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/60 transition" title="Bebas tarik & perbesar tanpa batasan rasio">
                    🔓 Bebas
                </button>
                <button type="button" id="cropRatioTarget" class="crop-ratio-btn px-2.5 py-1 rounded-md font-medium bg-primary-600 text-white shadow-sm border border-primary-600 transition">
                    🔒 <span id="cropRatioTargetLabel">Disarankan</span>
                </button>
                <button type="button" id="cropRatioSquare" class="crop-ratio-btn px-2.5 py-1 rounded-md font-medium border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-700 transition">
                    1:1
                </button>
                <button type="button" id="cropRatioWide" class="crop-ratio-btn px-2.5 py-1 rounded-md font-medium border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-700 transition">
                    16:9
                </button>
                <button type="button" id="cropSelectAll" class="px-2.5 py-1 rounded-md font-medium bg-emerald-100/70 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-200/60 dark:hover:bg-emerald-900/70 transition flex items-center gap-1" title="Pilih seluruh area gambar tanpa terpotong">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    Pilih Seluruh Gambar
                </button>
            </div>

            {{-- Zoom & Rotasi --}}
            <div class="flex items-center gap-1 flex-wrap">
                <button type="button" id="cropZoomIn" class="p-1.5 rounded-md text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-700 border border-gray-300 dark:border-slate-600 transition" title="Zoom In (+)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <button type="button" id="cropZoomOut" class="p-1.5 rounded-md text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-700 border border-gray-300 dark:border-slate-600 transition" title="Zoom Out (-)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </button>
                <button type="button" id="cropRotateLeft" class="p-1.5 rounded-md text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-700 border border-gray-300 dark:border-slate-600 transition" title="Putar Kiri 90°">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-3-3m3 3l3-3M3 10l3-3m-3 3l3 3"/></svg>
                </button>
                <button type="button" id="cropRotateRight" class="p-1.5 rounded-md text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-slate-700 border border-gray-300 dark:border-slate-600 transition" title="Putar Kanan 90°">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m0 0l3-3m-3 3l-3-3m16-4l-3-3m3 3l-3 3"/></svg>
                </button>
            </div>
        </div>

        {{-- Area Preview / Canvas --}}
        <div class="flex-1 overflow-auto p-4 bg-gray-100 dark:bg-slate-950 flex flex-col items-center justify-center">
            <div class="w-full h-[52vh] min-h-[340px] max-h-[520px] bg-black/10 dark:bg-black/50 rounded-xl overflow-hidden flex items-center justify-center shadow-inner">
                <img id="imageCropPreview" src="" alt="Pratinjau crop" class="block max-w-full">
            </div>
            <p class="text-xs text-gray-600 dark:text-slate-400 mt-2.5 text-center sm:text-left w-full flex items-center gap-1.5">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">💡 Tips:</span>
                <span>Tarik sudut atau garis biru untuk memperbesar/memperkecil area crop. Gunakan tombol <strong>🔓 Bebas</strong> jika ingin memperbesar tanpa batasan rasio, atau <strong>⛶ Pilih Seluruh Gambar</strong> untuk memilih seluruh foto.</span>
            </p>
        </div>

        {{-- Footer Modal --}}
        <div class="px-5 py-3.5 border-t border-gray-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 bg-gray-50 dark:bg-slate-900">
            <button type="button" id="imageCropReset" class="px-3.5 py-2 text-xs sm:text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-800 rounded-lg transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset Posisi
            </button>
            <div class="flex items-center gap-2">
                <button type="button" id="imageCropCancelFooter" class="px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 dark:text-slate-300 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="button" id="imageCropApply" class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-emerald-700 rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Terapkan Crop
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('imageCropModal');
    const previewImg = document.getElementById('imageCropPreview');
    const aspectLabelEl = document.getElementById('imageCropAspectLabel');
    const targetRatioLabelEl = document.getElementById('cropRatioTargetLabel');

    const btnRatioFree = document.getElementById('cropRatioFree');
    const btnRatioTarget = document.getElementById('cropRatioTarget');
    const btnRatioSquare = document.getElementById('cropRatioSquare');
    const btnRatioWide = document.getElementById('cropRatioWide');
    const btnSelectAll = document.getElementById('cropSelectAll');

    const btnZoomIn = document.getElementById('cropZoomIn');
    const btnZoomOut = document.getElementById('cropZoomOut');
    const btnRotateLeft = document.getElementById('cropRotateLeft');
    const btnRotateRight = document.getElementById('cropRotateRight');

    let cropper = null;
    let activeInput = null;
    let objectUrl = null;
    let currentTargetRatio = NaN;
    let currentTargetLabel = 'Bebas';

    const activeBtnClass = ['bg-primary-600', 'text-white', 'border-primary-600', 'shadow-sm'];
    const inactiveBtnClass = ['border-gray-300', 'dark:border-slate-600', 'text-gray-700', 'dark:text-slate-200', 'bg-transparent'];

    function updateRatioButtonStyles(activeBtn) {
        [btnRatioFree, btnRatioTarget, btnRatioSquare, btnRatioWide].forEach(function (btn) {
            if (!btn) return;
            btn.classList.remove(...activeBtnClass);
            btn.classList.add(...inactiveBtnClass);
        });
        if (activeBtn) {
            activeBtn.classList.remove(...inactiveBtnClass);
            activeBtn.classList.add(...activeBtnClass);
        }
    }

    function setCropRatio(ratio, label, activeBtn) {
        if (!cropper) return;
        cropper.setAspectRatio(ratio);
        aspectLabelEl.textContent = label;
        updateRatioButtonStyles(activeBtn);
    }

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
            if (h.name.endsWith('_crop_x') || h.name.endsWith('_crop_y') || h.name.endsWith('_crop_width') || h.name.endsWith('_crop_height') || h.name.endsWith('_cropped_data') || h.name.endsWith('_base64')) {
                h.value = '';
            }
        });
        const status = wrapper.querySelector('.image-crop-status');
        if (status) status.classList.add('hidden');
    }

    // Ratio Switcher Handlers
    btnRatioFree.addEventListener('click', function () {
        setCropRatio(NaN, 'Bebas (bebas tarik & perbesar tanpa batas)', btnRatioFree);
    });

    btnRatioTarget.addEventListener('click', function () {
        if (!isNaN(currentTargetRatio) && currentTargetRatio > 0) {
            setCropRatio(currentTargetRatio, currentTargetLabel, btnRatioTarget);
        } else {
            setCropRatio(NaN, 'Bebas', btnRatioFree);
        }
    });

    btnRatioSquare.addEventListener('click', function () {
        setCropRatio(1, '1:1 (Persegi)', btnRatioSquare);
    });

    btnRatioWide.addEventListener('click', function () {
        setCropRatio(16 / 9, '16:9 (Landscape)', btnRatioWide);
    });

    btnSelectAll.addEventListener('click', function () {
        if (!cropper) return;
        setCropRatio(NaN, 'Seluruh Gambar (Bebas)', btnRatioFree);
        const canvasData = cropper.getCanvasData();
        cropper.setCropBoxData({
            left: canvasData.left,
            top: canvasData.top,
            width: canvasData.width,
            height: canvasData.height,
        });
    });

    // Zoom & Rotate Handlers
    btnZoomIn.addEventListener('click', function () {
        if (cropper) cropper.zoom(0.1);
    });

    btnZoomOut.addEventListener('click', function () {
        if (cropper) cropper.zoom(-0.1);
    });

    btnRotateLeft.addEventListener('click', function () {
        if (cropper) cropper.rotate(-90);
    });

    btnRotateRight.addEventListener('click', function () {
        if (cropper) cropper.rotate(90);
    });

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
            currentTargetRatio = (ratioRaw !== '' && ratioRaw !== undefined) ? parseFloat(ratioRaw) : NaN;
            currentTargetLabel = input.dataset.aspectLabel || 'Bebas';

            targetRatioLabelEl.textContent = currentTargetLabel;
            aspectLabelEl.textContent = currentTargetLabel;

            if (isNaN(currentTargetRatio) || currentTargetRatio <= 0) {
                btnRatioTarget.classList.add('hidden');
                updateRatioButtonStyles(btnRatioFree);
            } else {
                btnRatioTarget.classList.remove('hidden');
                updateRatioButtonStyles(btnRatioTarget);
            }

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);

            previewImg.onload = function () {
                if (cropper) cropper.destroy();

                const options = {
                    viewMode: 0, // viewMode 0 memungkinkan drag & resize leluasa tanpa stuck di batas kanvas
                    dragMode: 'move',
                    autoCropArea: 0.85, // 85% memberi ruang agar handle crop langsung bisa ditarik membesar ke segala arah
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                };

                if (!isNaN(currentTargetRatio) && currentTargetRatio > 0) {
                    options.aspectRatio = currentTargetRatio;
                }

                cropper = new Cropper(previewImg, options);
            };

            openModal();
            previewImg.src = objectUrl;
        });
    });

    function applyCrop() {
        if (!cropper || !activeInput) return;

        const data = cropper.getData(true);
        const prefix = activeInput.name;
        const wrapper = activeInput.closest('.image-crop-wrapper');

        // Set hidden input coordinates
        ['_crop_x', '_crop_y', '_crop_width', '_crop_height'].forEach(function (suffix) {
            const key = prefix + suffix;
            const field = wrapper ? wrapper.querySelector('input[name="' + key + '"]') : null;
            if (!field) return;
            if (suffix === '_crop_x') field.value = Math.round(data.x);
            if (suffix === '_crop_y') field.value = Math.round(data.y);
            if (suffix === '_crop_width') field.value = Math.round(data.width);
            if (suffix === '_crop_height') field.value = Math.round(data.height);
        });

        // Generate client-side cropped canvas dengan kualitas tinggi
        const file = activeInput.files && activeInput.files[0];
        const isPng = file && (file.type === 'image/png' || (file.name && file.name.toLowerCase().endsWith('.png')));
        const isWebp = file && (file.type === 'image/webp' || (file.name && file.name.toLowerCase().endsWith('.webp')));

        const canvas = cropper.getCroppedCanvas({
            maxWidth: 1920,
            maxHeight: 1920,
            imageSmoothingQuality: 'high',
            fillColor: (isPng || isWebp) ? 'transparent' : '#ffffff',
        });

        if (canvas && wrapper) {
            const mimeType = isPng ? 'image/png' : (isWebp ? 'image/webp' : 'image/jpeg');
            const base64Data = canvas.toDataURL(mimeType, 0.92);

            // Simpan Base64 ke hidden field _cropped_data untuk diproses backend secara presisi
            let croppedDataField = wrapper.querySelector('input[name="' + prefix + '_cropped_data"]');
            if (!croppedDataField) {
                croppedDataField = document.createElement('input');
                croppedDataField.type = 'hidden';
                croppedDataField.name = prefix + '_cropped_data';
                wrapper.appendChild(croppedDataField);
            }
            croppedDataField.value = base64Data;

            // Simpan juga ke format legacy base64 jika ada (seperti student_photo_base64 pada PPDB)
            const legacyBase64Field = wrapper.querySelector('input[name="' + prefix + '_base64"]') || document.getElementById(prefix + '_base64');
            if (legacyBase64Field) {
                legacyBase64Field.value = base64Data;
            }

            const status = wrapper.querySelector('.image-crop-status');
            const resultPreview = wrapper.querySelector('.image-crop-result-preview');
            if (status) {
                if (resultPreview) {
                    resultPreview.src = base64Data;
                    resultPreview.classList.remove('hidden');
                }
                const msg = status.querySelector('p');
                if (msg) msg.textContent = '✓ Area crop berhasil disesuaikan & siap disimpan';
                status.classList.remove('hidden');
            }
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

    window.addEventListener('resize', function () {
        if (cropper) cropper.resize();
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
                const croppedData = wrapper && wrapper.querySelector('input[name="' + input.name + '_cropped_data"]');
                const base64 = wrapper && wrapper.querySelector('input[name="' + input.name + '_base64"]');

                const hasValidCrop = (w && w.value && parseInt(w.value) > 0) ||
                                     (croppedData && croppedData.value) ||
                                     (base64 && base64.value);

                if (!hasValidCrop) {
                    e.preventDefault();
                    alert('Silakan selesaikan crop untuk "' + (input.labels && input.labels[0] ? input.labels[0].textContent.trim() : input.name) + '" — pilih gambar, atur area, lalu klik Terapkan crop.');
                    input.focus();
                }
            });
        });
    });
});
</script>
