<?php
return [
    'title'              => 'Notifications',
    'clear_all'          => 'Clear All',
    'no_notifications'   => 'No notifications',
    'view_all'           => 'View All',

    // Evaluator
    'appraisal_rejected'      => 'Appraisal Returned',
    'appraisal_rejected_body' => 'Appraisal for :name (:period) was returned for revision.',

    // User II
    'pending_approval'      => 'Awaiting Your Approval',
    'pending_approval_body' => 'Appraisal for :name (:period) is awaiting your approval.',

    // CFO / CEO
    'pending_final'      => 'Awaiting Final Approval',
    'pending_final_body' => 'Appraisal for :name (:period) requires your final decision.',

    // Admin — appraisals in progress
    'appraisal_pending_admin'      => 'Appraisal Awaiting Process',
    'appraisal_pending_admin_body' => 'Appraisal for :name (:period) is still in approval process.',

    // Admin — reimbursement
    'reimb_pending'      => 'Reimbursement Awaiting Approval',
    'reimb_pending_body' => ':name submitted :number, awaiting your approval.',

    // Admin — whistleblower
    'whistleblower_new'      => 'New Whistleblower Report',
    'whistleblower_new_body' => 'Report :ticket has been submitted and not yet reviewed.',

    // Karyawan — their own reimbursement status
    'reimb_approved'      => 'Reimbursement Approved',
    'reimb_approved_body' => 'Your submission :number has been approved.',
    'reimb_rejected'      => 'Reimbursement Rejected',
    'reimb_rejected_body' => 'Your submission :number was rejected. Please check the rejection reason.',
];
