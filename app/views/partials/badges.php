<?php
declare(strict_types=1);

/**
 * Status Badge Helper Functions (Section 17)
 */
if (!function_exists('renderStatusBadge')) {
    function renderStatusBadge(string $status, string $context = 'general'): string
    {
        $statusClean = strtolower(trim($status));
        $label = ucwords(str_replace(['_', '-'], ' ', $statusClean));
        $class = 'badge-neutral';

        // Map status to semantic color classes
        switch ($statusClean) {
            case 'active':
            case 'internship_active':
            case 'approved':
            case 'university_approved':
            case 'verified':
            case 'accepted':
            case 'published':
            case 'completed':
            case 'present':
            case 'sent':
                $class = 'badge-success';
                break;

            case 'pending':
            case 'placement_pending':
            case 'pending_verification':
            case 'submitted':
            case 'under_review':
            case 'shortlisted':
            case 'interview':
            case 'company_reviewed':
            case 'resubmitted':
            case 'leave':
            case 'logged':
                $class = 'badge-warning';
                break;

            case 'rejected':
            case 'flagged':
            case 'returned':
            case 'suspended':
            case 'deactivated':
            case 'absent':
            case 'failed':
            case 'removed':
                $class = 'badge-danger';
                break;

            case 'draft':
            case 'closed':
            case 'unpublished':
            case 'inactive':
            case 'withdrawn':
            default:
                $class = 'badge-neutral';
                break;
        }

        return '<span class="badge ' . $class . '">' . e($label) . '</span>';
    }
}
