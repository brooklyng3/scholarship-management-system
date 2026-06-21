<?php
/**
 * Reusable detail row component for displaying label-value pairs
 * @var string $label - The label to display (e.g., "ID:", "Name:")
 * @var mixed $value - The value to display (can be string, number, or HTML)
 * @var bool $escape - Whether to escape the value (default: true)
 */
$escape = $escape ?? true;
?>
<div class="row mb-3">
    <div class="col-md-3 fw-bold"><?= e($label) ?></div>
    <div class="col-md-9"><?= $escape ? e($value) : $value ?></div>
</div>
