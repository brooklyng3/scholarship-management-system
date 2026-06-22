<?php
/**
 * @var array $students List of all students
 * @var array $programs List of all scholarship programs
 * @var array $errors List of validation errors
 * @var array $old Previous form data
 */

$pageTitle = 'Create Application';
require_once __DIR__ . '/../partials/header.php';

$currentUser = current_user();
$isStudent = ($currentUser['role'] === 'student');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=applications&action=index">Applications</a></li>
        <li class="breadcrumb-item active">Create New</li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">➕ Create New Application</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?controller=applications&action=store" id="applicationForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="user_id" class="form-label">Student <span class="text-danger">*</span></label>
                <select name="user_id" id="user_id" class="form-select" required <?= $isStudent ? 'disabled' : '' ?>>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= htmlspecialchars($student['id']) ?>" 
                                <?= (isset($old['user_id']) && $old['user_id'] == $student['id']) ? 'selected' : '' ?>
                                <?= ($isStudent && $student['id'] == $currentUser['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isStudent): ?>
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($currentUser['id']) ?>">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="program_id" class="form-label">Scholarship Program <span class="text-danger">*</span></label>
                <select name="program_id" id="program_id" class="form-select" required>
                    <option value="">-- Select Program --</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= htmlspecialchars($program['id']) ?>" 
                                <?= (isset($old['program_id']) && $old['program_id'] == $program['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($program['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">
                    <span class="badge bg-info text-dark">🎯 Auto-Sorting Enabled</span>
                    You will be automatically assigned to Excellence Tier or Standard Tier based on your GPA and Training Score.
                </div>
            </div>

            <div class="mb-3">
                <label for="proof_documents" class="form-label">Supporting Documents <span class="text-danger">*</span></label>
                <input type="file" name="file_selector" id="file_selector" class="form-control" accept=".pdf,.jpg,.png,.jpeg" multiple>
                <div class="form-text">Upload one or more supporting documents (PDF, JPG, PNG, max 5MB each)</div>
                <div id="fileList" class="mt-2"></div>
                <input type="hidden" name="has_files" id="has_files" value="0">
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Submit Application</button>
                <a href="index.php?controller=applications&action=index" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
const fileSelector = document.getElementById('file_selector');
const fileListDiv = document.getElementById('fileList');
const applicationForm = document.getElementById('applicationForm');
const hasFilesInput = document.getElementById('has_files');

// Store selected files in a DataTransfer object to maintain the list
let selectedFiles = new DataTransfer();

// Handle file selection
fileSelector.addEventListener('change', function() {
    const newFiles = Array.from(this.files);
    
    // Validate and add each file
    newFiles.forEach(file => {
        // Check if file already exists
        const exists = Array.from(selectedFiles.files).some(f => 
            f.name === file.name && f.size === file.size
        );
        
        if (!exists) {
            // Validate file
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (file.size > maxSize) {
                alert('File "' + file.name + '" exceeds 5MB limit.');
                return;
            }
            
            if (!allowedExtensions.includes(fileExtension)) {
                alert('File "' + file.name + '" has invalid extension. Only PDF, JPG, and PNG files are allowed.');
                return;
            }
            
            // Add file to the DataTransfer object
            selectedFiles.items.add(file);
        }
    });
    
    // Update the display
    updateFileList();
    
    // Clear the file input so the same file can be added again if removed
    this.value = '';
});

function updateFileList() {
    fileListDiv.innerHTML = '';
    
    if (selectedFiles.files.length > 0) {
        hasFilesInput.value = '1';
        const ul = document.createElement('ul');
        ul.className = 'list-group';
        
        Array.from(selectedFiles.files).forEach((file, index) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            
            const fileInfo = document.createElement('span');
            fileInfo.textContent = file.name;
            
            const rightSide = document.createElement('div');
            rightSide.className = 'd-flex align-items-center gap-2';
            
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary rounded-pill';
            badge.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = () => removeFile(index);
            
            rightSide.appendChild(badge);
            rightSide.appendChild(removeBtn);
            
            li.appendChild(fileInfo);
            li.appendChild(rightSide);
            ul.appendChild(li);
        });
        
        fileListDiv.appendChild(ul);
    } else {
        hasFilesInput.value = '0';
    }
}

function removeFile(index) {
    // Create a new DataTransfer to rebuild the file list without the removed file
    const newFileList = new DataTransfer();
    
    Array.from(selectedFiles.files).forEach((file, i) => {
        if (i !== index) {
            newFileList.items.add(file);
        }
    });
    
    selectedFiles = newFileList;
    updateFileList();
}

// Before form submission, attach the files to a hidden file input
applicationForm.addEventListener('submit', function(e) {
    const userId = document.getElementById('user_id').value;
    const programId = document.getElementById('program_id').value;
    
    if (!userId || !programId) {
        e.preventDefault();
        alert('Please fill out all required fields.');
        return false;
    }
    
    if (selectedFiles.files.length === 0) {
        e.preventDefault();
        alert('Please upload at least one supporting document.');
        return false;
    }
    
    // Create a hidden file input with all selected files
    const hiddenFileInput = document.createElement('input');
    hiddenFileInput.type = 'file';
    hiddenFileInput.name = 'proof_documents[]';
    hiddenFileInput.multiple = true;
    hiddenFileInput.style.display = 'none';
    hiddenFileInput.files = selectedFiles.files;
    
    this.appendChild(hiddenFileInput);
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>