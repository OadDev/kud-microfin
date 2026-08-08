<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * Default email/push copy for every automated customer notification. Admin
 * can edit the subject/body/title text afterwards from the Notification
 * Manager -- this just seeds sensible starting copy so nothing is blank.
 * Used both by local `db:seed` and by the production installer wizard.
 */
class NotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            NotificationTemplate::query()->firstOrCreate(['key' => $template['key']], $template);
        }
    }

    protected function templates(): array
    {
        return [
            [
                'key' => 'order_placed',
                'name' => 'Order Placed',
                'description' => 'Sent to the customer right after they place an order. Placeholders: {{customer_name}}, {{order_no}}, {{amount}}, {{payment_method}}',
                'email_subject' => 'Your order {{order_no}} has been placed',
                'email_body' => "Hi {{customer_name}},\n\nThank you for your order! We've received order {{order_no}} for ₹{{amount}} ({{payment_method}}).\n\nWe'll keep you updated as it progresses.\n\n— BluePeak Fintech",
                'push_title' => 'Order Placed',
                'push_body' => 'Your order {{order_no}} for ₹{{amount}} has been placed.',
            ],
            [
                'key' => 'order_status_changed',
                'name' => 'Order Status Changed',
                'description' => 'Sent whenever Admin updates an order\'s status. Placeholders: {{customer_name}}, {{order_no}}, {{status}}',
                'email_subject' => 'Update on your order {{order_no}}',
                'email_body' => "Hi {{customer_name}},\n\nYour order {{order_no}} status has been updated to: {{status}}.\n\n— BluePeak Fintech",
                'push_title' => 'Order Update',
                'push_body' => 'Order {{order_no}} is now {{status}}.',
            ],
            [
                'key' => 'loan_approved',
                'name' => 'Loan Approved',
                'description' => 'Sent when Admin approves a loan application. Placeholders: {{customer_name}}, {{loan_account_no}}, {{emi_amount}}, {{num_emis}}',
                'email_subject' => 'Your loan {{loan_account_no}} has been approved',
                'email_body' => "Hi {{customer_name}},\n\nGreat news — your loan {{loan_account_no}} has been approved! Your EMI is ₹{{emi_amount}} for {{num_emis}} installments.\n\n— BluePeak Fintech",
                'push_title' => 'Loan Approved',
                'push_body' => 'Your loan {{loan_account_no}} has been approved.',
            ],
            [
                'key' => 'loan_rejected',
                'name' => 'Loan Rejected',
                'description' => 'Sent when Admin rejects a loan application. Placeholders: {{customer_name}}, {{loan_account_no}}, {{reason}}',
                'email_subject' => 'Update on your loan application {{loan_account_no}}',
                'email_body' => "Hi {{customer_name}},\n\nWe're sorry to inform you that your loan application {{loan_account_no}} was not approved.\n\nReason: {{reason}}\n\n— BluePeak Fintech",
                'push_title' => 'Loan Application Update',
                'push_body' => 'Your loan application {{loan_account_no}} was not approved.',
            ],
            [
                'key' => 'payment_approved',
                'name' => 'EMI Payment Approved',
                'description' => 'Sent when Admin approves a submitted EMI payment. Placeholders: {{customer_name}}, {{emi_number}}, {{amount}}',
                'email_subject' => 'Your EMI payment has been verified',
                'email_body' => "Hi {{customer_name}},\n\nYour payment of ₹{{amount}} for EMI #{{emi_number}} has been verified and marked as paid.\n\n— BluePeak Fintech",
                'push_title' => 'Payment Verified',
                'push_body' => 'Your payment of ₹{{amount}} for EMI #{{emi_number}} has been verified.',
            ],
            [
                'key' => 'payment_rejected',
                'name' => 'EMI Payment Rejected',
                'description' => 'Sent when Admin rejects a submitted EMI payment. Placeholders: {{customer_name}}, {{emi_number}}, {{reason}}',
                'email_subject' => 'Your EMI payment submission was rejected',
                'email_body' => "Hi {{customer_name}},\n\nYour payment submission for EMI #{{emi_number}} could not be verified.\n\nReason: {{reason}}\n\nPlease resubmit with correct details.\n\n— BluePeak Fintech",
                'push_title' => 'Payment Rejected',
                'push_body' => 'Your payment for EMI #{{emi_number}} was rejected. Please resubmit.',
            ],
            [
                'key' => 'foreclosure_approved',
                'name' => 'Loan Foreclosure Approved',
                'description' => 'Sent when Admin approves a loan foreclosure payment. Placeholders: {{customer_name}}, {{loan_account_no}}, {{amount}}',
                'email_subject' => 'Your loan {{loan_account_no}} has been closed',
                'email_body' => "Hi {{customer_name}},\n\nYour foreclosure payment of ₹{{amount}} has been verified. Loan {{loan_account_no}} is now fully closed. Thank you for banking with us!\n\n— BluePeak Fintech",
                'push_title' => 'Loan Closed',
                'push_body' => 'Your loan {{loan_account_no}} has been foreclosed and fully closed.',
            ],
            [
                'key' => 'emi_reminder',
                'name' => 'EMI Due Reminder',
                'description' => 'Automated reminder sent a few days before an EMI is due. Placeholders: {{customer_name}}, {{emi_number}}, {{due_date}}, {{amount}}',
                'email_subject' => 'Your EMI is due soon',
                'email_body' => "Hi {{customer_name}},\n\nThis is a reminder that EMI #{{emi_number}} of ₹{{amount}} is due on {{due_date}}. Please make your payment on time to avoid late fees.\n\n— BluePeak Fintech",
                'push_title' => 'EMI Due Soon',
                'push_body' => 'EMI #{{emi_number}} of ₹{{amount}} is due on {{due_date}}.',
            ],
            [
                'key' => 'password_reset_otp',
                'name' => 'Password Reset OTP',
                'description' => 'Sent when a customer requests a password reset code. Placeholders: {{customer_name}}, {{otp_code}}',
                'push_enabled' => false,
                'email_subject' => 'Your BluePeak Fintech password reset code',
                'email_body' => "Hi {{customer_name}},\n\nUse this code to reset your password: {{otp_code}}\n\nThis code expires in 10 minutes. If you didn't request this, you can safely ignore this email.\n\n— BluePeak Fintech",
                'push_title' => null,
                'push_body' => null,
            ],
        ];
    }
}
