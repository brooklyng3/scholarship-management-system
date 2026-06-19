<?php
/**
 * @var array $students List of all students
 * @var array $tiers List of all scholarship tiers
 * @var array $errors List of validation errors
 * @var array $old Previous form data
 */

$pageTitle = 'Create Application';
require_once __DIR__ . '/../partials/header.php';
?>

<div style="margin-bottom: 1rem;">
    <a href="index.php?controller=applications&action=index" style="color: #3498db; text-decoration: none;">
        ← Back to Applications
    </a>
</div>

<div class="card" style="max-width: 600px;">
    <h2>➕ Create New Application</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error-list">
            <ul style="margin: 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?controller=applications&action=store" id="applicationForm">
        <div class="form-group">
            <label for="user_id">Student <span style="color: red;">*</span></label>
            <select name="user_id" id="user_id" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['id']) ?>" 
                            <?= (isset($old['user_id']) && $old['user_id'] == $student['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="tier_id">Scholarship Tier <span style="color: red;">*</span></label>
            <select name="tier_id" id="tier_id" required>
                <option value="">-- Select Tier --</option>
                <?php foreach ($tiers as $tier): ?>
                    <option value="<?= htmlspecialchars($tier['id']) ?>" 
                            <?= (isset($old['tier_id']) && $old['tier_id'] == $tier['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tier['tier_name']) ?> - <?= htmlspecialchars($tier['program_title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status <span style="color: red;">*</span></label>
            <select name="status" id="status" required>
                <option value="pending" <?= (!isset($old['status']) || $old['status'] === 'pending') ? 'selected' : '' ?>>
                    Pending
                </option>
                <option value="reviewing" <?= (isset($old['status']) && $old['status'] === 'reviewing') ? 'selected' : '' ?>>
                    Reviewing
                </option>
                <option value="approved" <?= (isset($old['status']) && $old['status'] === 'approved') ? 'selected' : '' ?>>
                    Approved
                </option>
                <option value="rejected" <?= (isset($old['status']) && $old['status'] === 'rejected') ? 'selected' : '' ?>>
                    Rejected
                </option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Save Application</button>
            <a href="index.php?controller=applications&action=index" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Client-side validation
document.getElementById('applicationForm').addEventListener('submit', function(e) {
    const userId = document.getElementById('user_id').value;
    const tierId = document.getElementById('tier_id').value;
    const status = document.getElementById('status').value;
    
    if (!userId || userId === '') {
        e.preventDefault();
        alert('Please select a student.');
        return false;
    }
    
    if (!tierId || tierId === '') {
        e.preventDefault();
        alert('Please select a scholarship tier.');
        return false;
    }
    
    if (!status || status === '') {
        e.preventDefault();
        alert('Please select a status.');
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
