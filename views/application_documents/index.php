<?php 
/**
 * @var int $applicationId
 * @var array $documents
 * @var array $documentTypeLabels
 */
require_once __DIR__ . '/../partials/header.php'; 
?>
<div class="container mt-4">
    <h2>Application Documents - Application #<?= e($applicationId) ?></h2>
    
    <?php if (empty($documents)): ?>
        <div class="alert alert-info">
            No documents have been uploaded for this application yet.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Document Type</th>
                            <th>Uploaded At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><?= e($documentTypeLabels[$doc['document_type']] ?? $doc['document_type']) ?></td>
                                <td><?= e(date('Y-m-d H:i:s', strtotime($doc['uploaded_at']))) ?></td>
                                <td>
                                    <a href="<?= e($doc['file_url']) ?>" class="btn btn-sm btn-primary" target="_blank">
                                        View/Download
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="mt-3">
        <a href="<?= e(url('applications', 'index')) ?>" class="btn btn-secondary">Back to Applications</a>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
