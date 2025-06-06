<?php
/**
 * Stripe Client Helper
 *
 * This class can be used to provide configuration or minor helper functions
 * related to Stripe interactions that are initiated from the PHP frontend
 * but processed by the Node.js backend.
 *
 * For instance, it could provide the Stripe Publishable Key to views
 * for client-side JavaScript (Stripe.js) initialization.
 */

class StripeClientHelper {

    private $publishable_key;
    // private $secret_key; // Secret key would NOT be exposed here; managed by Node.js backend

    /**
     * Constructor.
     *
     * @param array $config Configuration array, typically loaded from a non-WordPress config file.
     *                      Example: ['publishable_key' => 'pk_test_yourkey']
     */
    public function __construct(array $config = []) {
        // In a real application, $config would come from a global config loader.
        // For now, we can use a sensible default or passed-in value.
        $this->publishable_key = $config['publishable_key'] ?? null;

        if (empty($this->publishable_key)) {
            // Fallback or error for missing configuration
            // error_log('Stripe Publishable Key is not configured.');
            // In a real app, you might throw an exception or handle this more gracefully.
        }
    }

    /**
     * Get the Stripe Publishable Key.
     *
     * @return string|null The Stripe Publishable Key.
     */
    public function getPublishableKey(): ?string {
        return $this->publishable_key;
    }

    /**
     * Example: A helper to format amount for display (though often done in views or JS)
     * This is just a conceptual placeholder for any minor, non-sensitive Stripe-related utilities
     * that might be useful on the PHP side before calling the Node.js backend.
     */
    public static function formatAmountForDisplay($amount_in_cents, $currency = 'USD'): string {
        // Basic formatting, a more robust solution would use IntlMoneyFormatter
        $amount = $amount_in_cents / 100;
        return strtoupper($currency) . ' ' . number_format($amount, 2);
    }

    // Other potential helper methods (if any emerge as truly necessary for PHP):
    // - Validating input data before sending to the Node.js API? (Often better handled by the API itself)
    // - Structuring data payloads for the Node.js API? (Usually simple enough to do in controllers)

}

/*
Note on the original MIFECO_Stripe class:
The vast majority of the original class's functionality was tightly coupled with:
1. WordPress hooks, functions (get_option, $wpdb, AJAX handlers, etc.), and database interactions.
2. Direct Stripe PHP SDK calls for creating charges, subscriptions, customers, and handling webhooks.

Given the new architecture where the Node.js backend will handle all direct Stripe API interactions
and webhook processing, those parts of the original class are no longer applicable to the PHP frontend.
The PHP frontend's role shifts to:
- Displaying checkout forms.
- Collecting user input.
- Using Stripe.js (client-side) to tokenize card details (or using Stripe Checkout).
- Making API calls to the Node.js backend with the token/payment method ID and relevant order/user details.
- Receiving responses from the Node.js backend.

This simplified StripeClientHelper reflects that shift, primarily offering a way to manage client-side
configuration (like the publishable key) and potentially some very light utility functions if needed.
Most complex logic is now deferred to the Node.js service.
*/
