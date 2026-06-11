(function () {
    'use strict';

    // --- State ---
    var files = []; // Array of { id, file } where id is a unique incrementing integer

    // --- DOM refs (assigned in init) ---
    var dropzone, fileInput, fileList, warningBanner, mergeBtn, mergeError, resultArea, downloadBtn, resetBtn, inlineErrors;

    var nextId = 0;
    var sortable = null;

    // --- Helpers ---

    function formatSize(bytes) {
        if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
        return bytes + ' B';
    }

    function updateState() {
        // Update merge button
        mergeBtn.disabled = files.length < 2;

        // Size warnings: single file > 50MB OR total > 100MB
        var MB50 = 50 * 1024 * 1024;
        var MB100 = 100 * 1024 * 1024;
        var total = files.reduce(function (sum, f) { return sum + f.file.size; }, 0);
        var hasLargeFile = files.some(function (f) { return f.file.size > MB50; });
        if (hasLargeFile || total > MB100) {
            warningBanner.classList.add('is-visible');
        } else {
            warningBanner.classList.remove('is-visible');
        }
    }

    function renderFileList() {
        fileList.innerHTML = '';
        files.forEach(function (entry) {
            var li = document.createElement('li');
            li.className = 'pdfforge-file-item';
            li.dataset.id = entry.id;
            li.innerHTML =
                '<span class="pdfforge-file-item__handle" aria-hidden="true">⠿</span>' +
                '<span class="pdfforge-file-item__info">' +
                    '<span class="pdfforge-file-item__name">' + escapeHtml(entry.file.name) + '</span>' +
                    '<span class="pdfforge-file-item__size">' + formatSize(entry.file.size) + '</span>' +
                '</span>' +
                '<button type="button" class="pdfforge-file-item__remove" aria-label="Remove ' + escapeHtml(entry.file.name) + '">✕</button>';
            li.querySelector('.pdfforge-file-item__remove').addEventListener('click', function () {
                files = files.filter(function (f) { return f.id !== entry.id; });
                renderFileList();
                updateState();
                // Hide result if files changed after merge
                resultArea.classList.remove('is-visible');
                mergeError.classList.remove('is-visible');
            });
            fileList.appendChild(li);
        });

        // Re-init Sortable
        if (sortable) {
            sortable.destroy();
            sortable = null;
        }
        if (files.length > 0) {
            sortable = Sortable.create(fileList, {
                animation: 150,
                handle: '.pdfforge-file-item__handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function (evt) {
                    // Reorder files array to match DOM order
                    var newOrder = [];
                    fileList.querySelectorAll('.pdfforge-file-item').forEach(function (li) {
                        var id = parseInt(li.dataset.id, 10);
                        var found = files.find(function (f) { return f.id === id; });
                        if (found) newOrder.push(found);
                    });
                    files = newOrder;
                }
            });
        }
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showInlineError(msg) {
        var p = document.createElement('p');
        p.className = 'pdfforge-inline-error';
        p.textContent = msg;
        inlineErrors.appendChild(p);
        // Auto-dismiss after 5 seconds
        setTimeout(function () {
            if (p.parentNode) p.parentNode.removeChild(p);
        }, 5000);
    }

    function addFiles(newFiles) {
        Array.from(newFiles).forEach(function (file) {
            if (file.type !== 'application/pdf') {
                showInlineError(file.name + ' isn\'t a PDF — skipped');
                return;
            }
            // Avoid duplicates by name+size (best-effort)
            var duplicate = files.some(function (f) {
                return f.file.name === file.name && f.file.size === file.size;
            });
            if (!duplicate) {
                files.push({ id: nextId++, file: file });
            }
        });
        renderFileList();
        updateState();
    }

    // --- Merge ---

    async function doMerge() {
        mergeBtn.textContent = 'Merging…';
        mergeBtn.disabled = true;
        mergeError.classList.remove('is-visible');
        resultArea.classList.remove('is-visible');

        try {
            var orderedFiles = [];
            fileList.querySelectorAll('.pdfforge-file-item').forEach(function (li) {
                var id = parseInt(li.dataset.id, 10);
                var found = files.find(function (f) { return f.id === id; });
                if (found) orderedFiles.push(found);
            });

            var mergedPdf = await PDFLib.PDFDocument.create();

            for (var i = 0; i < orderedFiles.length; i++) {
                var arrayBuffer = await orderedFiles[i].file.arrayBuffer();
                var pdf = await PDFLib.PDFDocument.load(arrayBuffer);
                var pages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                pages.forEach(function (page) { mergedPdf.addPage(page); });
            }

            var mergedBytes = await mergedPdf.save();
            var blob = new Blob([mergedBytes], { type: 'application/pdf' });
            var url = URL.createObjectURL(blob);

            downloadBtn.href = url;
            resultArea.classList.add('is-visible');
        } catch (err) {
            mergeError.textContent = 'Couldn\'t merge these PDFs. They may be encrypted or corrupted. Try a different file.';
            mergeError.classList.add('is-visible');
        }

        mergeBtn.textContent = 'Merge PDFs';
        mergeBtn.disabled = files.length < 2;
    }

    // --- Event wiring ---

    function init() {
        dropzone     = document.getElementById('pdfforge-dropzone');
        fileInput    = document.getElementById('pdfforge-file-input');
        fileList     = document.getElementById('pdfforge-file-list');
        warningBanner= document.getElementById('pdfforge-warning');
        mergeBtn     = document.getElementById('pdfforge-merge-btn');
        mergeError   = document.getElementById('pdfforge-merge-error');
        resultArea   = document.getElementById('pdfforge-result');
        downloadBtn  = document.getElementById('pdfforge-download-btn');
        resetBtn     = document.getElementById('pdfforge-reset-btn');
        inlineErrors = document.getElementById('pdfforge-inline-errors');

        if (!dropzone) return; // Not on merge-pdf page

        // Click dropzone → open file picker
        dropzone.addEventListener('click', function (e) {
            if (e.target !== fileInput) fileInput.click();
        });
        dropzone.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
        });

        // File input change
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length) {
                addFiles(fileInput.files);
                fileInput.value = '';
            }
        });

        // Drag and drop
        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
        dropzone.addEventListener('dragleave', function (e) {
            if (!dropzone.contains(e.relatedTarget)) {
                dropzone.classList.remove('is-dragover');
            }
        });
        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
            addFiles(e.dataTransfer.files);
        });

        // Merge button
        mergeBtn.addEventListener('click', doMerge);

        // Reset
        resetBtn.addEventListener('click', function () {
            files = [];
            renderFileList();
            updateState();
            resultArea.classList.remove('is-visible');
            mergeError.classList.remove('is-visible');
            inlineErrors.innerHTML = '';
            if (downloadBtn.href && downloadBtn.href !== '#') {
                URL.revokeObjectURL(downloadBtn.href);
                downloadBtn.href = '#';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', init);
})();
