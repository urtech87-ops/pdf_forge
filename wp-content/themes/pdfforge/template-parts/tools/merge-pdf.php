<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="pdfforge-tool pdfforge-tool--merge-pdf">
    <p class="pdfforge-tool__intro">
        <?php esc_html_e( 'Combine multiple PDFs into one file. Drop your files below, reorder them, and merge — all in your browser. No uploads.', 'pdfforge' ); ?>
    </p>

    <div class="pdfforge-dropzone" id="pdfforge-dropzone" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Drop PDFs here or click to choose files', 'pdfforge' ); ?>">
        <span class="pdfforge-dropzone__icon" aria-hidden="true">📄</span>
        <span class="pdfforge-dropzone__text"><?php esc_html_e( 'Drop PDFs here, or click to choose files', 'pdfforge' ); ?></span>
        <input type="file" id="pdfforge-file-input" class="pdfforge-file-input" accept="application/pdf" multiple aria-hidden="true" tabindex="-1">
    </div>

    <div class="pdfforge-warning" id="pdfforge-warning" role="alert">
        ⚠️ <?php esc_html_e( 'Large files may slow down or freeze your browser. Browser-side processing has no upload — but it relies on this device\'s memory.', 'pdfforge' ); ?>
    </div>

    <ul class="pdfforge-file-list" id="pdfforge-file-list" aria-label="<?php esc_attr_e( 'Selected PDF files', 'pdfforge' ); ?>"></ul>

    <div id="pdfforge-inline-errors" aria-live="polite"></div>

    <div class="pdfforge-actions">
        <button type="button" class="pdfforge-btn" id="pdfforge-merge-btn" disabled>
            <?php esc_html_e( 'Merge PDFs', 'pdfforge' ); ?>
        </button>
    </div>

    <div class="pdfforge-error" id="pdfforge-merge-error" role="alert"></div>

    <div class="pdfforge-result" id="pdfforge-result">
        <p class="pdfforge-result__title"><?php esc_html_e( '✅ Your PDF is ready!', 'pdfforge' ); ?></p>
        <div class="pdfforge-result__actions">
            <a href="#" class="pdfforge-btn" id="pdfforge-download-btn" download="merged.pdf">
                <?php esc_html_e( 'Download merged.pdf', 'pdfforge' ); ?>
            </a>
            <button type="button" class="pdfforge-btn pdfforge-btn--secondary" id="pdfforge-reset-btn">
                <?php esc_html_e( 'Start over', 'pdfforge' ); ?>
            </button>
        </div>
    </div>
</div>
