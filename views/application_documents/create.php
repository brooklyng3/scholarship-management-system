<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-4">
    <h2>Upload Supporting Document</h2>
    
    <!-- Display validation errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= e(url('application_documents', 'store')) ?>" enctype="multipart/form-data" id="uploadForm">
                <?= csrf_field() ?>
                <input type="hidden" name="application_id" value="<?= e($applicationId) ?>">
                
                <div class="mb-3">
                    <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                    <select name="document_type" id="document_type" class="form-select" required>
                        <option value="">Select document type...</option>
                        <?php foreach ($documentTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="document" class="form-label">Document File <span class="text-danger">*</span></label>
                    <input type="file" name="document" id="document" class="form-control" accept=".pdf,.jpg,.png" required>
                    <div class="form-text">Allowed formats: PDF, JPG, PNG. Maximum size: 5MB.</div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                    <a href="<?= e(url('applications', 'index')) ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Client-side validation (vanilla JavaScript)
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('document');
    const file = fileInput.files[0];
    
    if (!file) {
        e.preventDefault();
        alert('Please select a file to upload.');
        return;
    }
    
    // Validate file extension
    const allowedExtensions = ['pdf', 'jpg', 'png'];
    const fileName = file.name.toLowerCase();
    const extension = fileName.substring(fileName.lastIndexOf('.') + 1);
    
    if (!allowedExtensions.includes(extension)) {
        e.preventDefault();
        alert('Invalid file type. Please upload PDF, JPG, or PNG files only.');
        return;
    }
    
    // Validate file size (5MB = 5242880 bytes)
    if (file.size > 5242880) {
        e.preventDefault();
        alert('File size exceeds 5MB limit. Please choose a smaller file.');
        return;
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
