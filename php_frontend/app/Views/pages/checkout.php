<?php
/**
 * Checkout page view.
 *
 * This file provides the HTML structure for the checkout page,
 * displaying plan details and a payment button.
 * It expects data to be passed from a controller.
 */

// Example: Data that would be passed by a controller
$plan_name_from_request = $plan_name_from_request ?? 'basic'; // Example: from URL parameter or route
$button_text_override = $button_text_override ?? null; // Example: from controller
$show_description_override = $show_description_override ?? true; // Example: from controller

// --- Hypothetical data passed from a controller ---
$plan_data = []; // This would be populated by the controller based on $plan_name_from_request
$all_plans_data = [ // Example structure for plan details
    'basic' => [
        'name' => 'Basic Plan',
        'price' => '$99',
        'period' => 'per month',
        'description' => 'Essential tools for business research',
        'price_id' => 'price_basic_monthly_123' // Example Stripe Price ID
    ],
    'premium' => [
        'name' => 'Premium Plan',
        'price' => '$199',
        'period' => 'per month',
        'description' => 'Advanced tools for business optimization',
        'price_id' => 'price_premium_monthly_456'
    ],
    'enterprise' => [
        'name' => 'Enterprise Plan',
        'price' => '$499',
        'period' => 'per month',
        'description' => 'Complete solution for enterprise needs',
        'price_id' => 'price_enterprise_monthly_789'
    ]
];

if (isset($all_plans_data[$plan_name_from_request])) {
    $plan_data = $all_plans_data[$plan_name_from_request];
} else {
    echo '<div class="mifeco-error-message">Invalid plan specified.</div>';
    return;
}

$button_text = $button_text_override ?: 'Subscribe Now';
$show_description = $show_description_override;

// --- User and System Status (Example data from controller) ---
$stripe_configured = true; // Example: Assume Stripe is configured
$price_id_configured = !empty($plan_data['price_id']);

$user_logged_in = true; // Example: Assume user is logged in
$user_email = 'user@example.com'; // Example user email
$current_user_plan_name = 'basic'; // Example: User's current plan, if any
$current_user_plan_status = 'active'; // Example: User's current plan status

// --- URLs (Example data from controller) ---
$login_url = '/login'; // Example login URL
$registration_url = '/register'; // Example registration URL
$dashboard_url = '/dashboard'; // Example dashboard URL

// --- Helper function (could be in a separate helpers file) ---
function get_display_plan_name($internal_plan_name, $all_plans) {
    return $all_plans[$internal_plan_name]['name'] ?? ucfirst($internal_plan_name);
}
// --- End of hypothetical data and helpers ---


// Check if Stripe is configured
if (!$stripe_configured) {
    // For a real application, you might show a user-friendly message
    // or only an admin-facing one based on user roles.
    echo '<div class="mifeco-error-message">Payment system is currently unavailable. Please try again later. (Admin: Stripe not configured)</div>';
    return;
}

// Check if price ID is set for the plan
if (!$price_id_configured) {
    echo '<div class="mifeco-error-message">This subscription plan is currently unavailable. Please try again later. (Admin: Price ID not configured for this plan)</div>';
    return;
}

// If user already has this plan and it's active, show a message
if ($user_logged_in && $current_user_plan_name === $plan_name_from_request && $current_user_plan_status === 'active') {
    echo '<div class="mifeco-info-message">You are already subscribed to this plan.</div>';
    // Optionally, provide a link to their dashboard or subscription management
    echo '<p><a href="' . htmlspecialchars($dashboard_url) . '">Go to Dashboard</a></p>';
    return;
}
?>

<div class="mifeco-checkout-container">
    <?php if ($show_description): ?>
        <div class="mifeco-plan-details">
            <h3 class="mifeco-plan-name"><?php echo htmlspecialchars($plan_data['name']); ?></h3>

            <div class="mifeco-plan-price">
                <span class="mifeco-price-amount"><?php echo htmlspecialchars($plan_data['price']); ?></span>
                <span class="mifeco-price-period"><?php echo htmlspecialchars($plan_data['period']); ?></span>
            </div>

            <div class="mifeco-plan-description">
                <?php echo htmlspecialchars($plan_data['description']); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$user_logged_in): ?>
        <div class="mifeco-login-required">
            <p>Please log in or create an account to subscribe.</p>
            <div class="mifeco-login-buttons">
                <a href="<?php echo htmlspecialchars($login_url); ?>" class="mifeco-btn mifeco-btn-secondary">Log In</a>
                <a href="<?php echo htmlspecialchars($registration_url); ?>" class="mifeco-btn mifeco-btn-outline">Create Account</a>
            </div>
        </div>
    <?php else: ?>
        <div class="mifeco-checkout-button">
            <button class="mifeco-btn mifeco-btn-primary mifeco-subscribe-btn"
                    data-plan="<?php echo htmlspecialchars($plan_name_from_request); ?>"
                    data-price-id="<?php echo htmlspecialchars($plan_data['price_id']); ?>"
                    data-user-email="<?php echo htmlspecialchars($user_email); // Pass user email for Stripe session ?>">
                <?php echo htmlspecialchars($button_text); ?>
            </button>

            <?php if (!empty($current_user_plan_name)): ?>
                <div class="mifeco-current-plan-note">
                    <?php
                        printf('You are currently subscribed to the %s.',
                               htmlspecialchars(get_display_plan_name($current_user_plan_name, $all_plans_data)));
                    ?>

                    <?php if ($current_user_plan_name !== $plan_name_from_request): ?>
                        <?php
                        // Simplified upgrade/downgrade logic display
                        // A more robust solution would compare plan tiers/values
                        $action_text = 'Changing'; // Default
                        $plan_tiers = ['basic' => 1, 'premium' => 2, 'enterprise' => 3];

                        if (isset($plan_tiers[$current_user_plan_name]) && isset($plan_tiers[$plan_name_from_request])) {
                            if ($plan_tiers[$plan_name_from_request] > $plan_tiers[$current_user_plan_name]) {
                                $action_text = 'Upgrading';
                            } elseif ($plan_tiers[$plan_name_from_request] < $plan_tiers[$current_user_plan_name]) {
                                $action_text = 'Downgrading';
                            }
                        }
                        ?>
                        <div class="mifeco-plan-change-note">
                            <?php
                                printf('%s to the %s will change your current subscription immediately.',
                                       htmlspecialchars($action_text),
                                       htmlspecialchars(get_display_plan_name($plan_name_from_request, $all_plans_data)));
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Stripe payment processing modal (structure kept, JS might need API endpoint changes later) -->
<div class="mifeco-payment-modal" id="mifeco-payment-modal">
    <div class="mifeco-payment-modal-content">
        <span class="mifeco-payment-modal-close">&times;</span>
        <div class="mifeco-payment-modal-header">
            <h3>Complete Your Subscription</h3>
        </div>
        <div class="mifeco-payment-modal-body">
            <div id="mifeco-checkout-message" class="mifeco-checkout-message"></div>
            <!-- Example: Add a placeholder for Stripe Elements if using client-side tokenization -->
            <!-- <div id="card-element"></div> -->
            <div id="mifeco-checkout-loading" class="mifeco-checkout-loading" style="display:none;">
                <div class="mifeco-spinner"></div>
                <p>Preparing secure checkout...</p>
            </div>
        </div>
    </div>
</div>

<script type_text_javascript>
// Basic JavaScript for modal interaction and button click (to be adapted for API calls)
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('mifeco-payment-modal');
    const closeButton = modal.querySelector('.mifeco-payment-modal-close');
    const subscribeButtons = document.querySelectorAll('.mifeco-subscribe-btn');
    const checkoutMessage = document.getElementById('mifeco-checkout-message');
    const checkoutLoading = document.getElementById('mifeco-checkout-loading');

    subscribeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const plan = this.dataset.plan;
            const priceId = this.dataset.priceId;
            const userEmail = this.dataset.userEmail; // Get user email

            checkoutMessage.innerHTML = ''; // Clear previous messages
            checkoutLoading.style.display = 'block'; // Show loading indicator
            modal.style.display = 'block';

            // TODO: Replace with actual API call to Node.js backend to create Stripe Checkout Session
            // Example:
            // fetch('/api/stripe/create-checkout-session', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ priceId: priceId, userEmail: userEmail, plan: plan })
            // })
            // .then(response => response.json())
            // .then(data => {
            //     checkoutLoading.style.display = 'none';
            //     if (data.sessionId) {
            //         // Redirect to Stripe Checkout
            //         const stripe = Stripe('YOUR_STRIPE_PUBLISHABLE_KEY'); // Replace with actual key
            //         stripe.redirectToCheckout({ sessionId: data.sessionId });
            //     } else {
            //         checkoutMessage.innerHTML = '<p class="mifeco-error">Error creating checkout session.</p>';
            //     }
            // })
            // .catch(error => {
            //     checkoutLoading.style.display = 'none';
            //     checkoutMessage.innerHTML = '<p class="mifeco-error">Request failed: ' + error + '</p>';
            // });

            // For now, simulate loading and a message
            setTimeout(() => {
                checkoutLoading.style.display = 'none';
                checkoutMessage.innerHTML = '<p>Simulating API call for plan: ' + plan + ' (Price ID: ' + priceId + '). User: ' + userEmail + '</p><p>Next step: Integrate with Stripe Checkout via Node.js backend.</p>';
            }, 2000);
        });
    });

    if(closeButton) {
        closeButton.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
<style>
/* Basic styling for the modal and checkout elements (can be moved to a CSS file) */
.mifeco-checkout-container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; }
.mifeco-plan-details h3 { margin-top: 0; }
.mifeco-plan-price { font-size: 1.5em; margin-bottom: 10px; }
.mifeco-login-required, .mifeco-checkout-button { margin-top: 20px; text-align: center; }
.mifeco-btn { padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
.mifeco-btn-primary { background-color: #007bff; color: white; border: 1px solid #007bff; }
.mifeco-btn-secondary { background-color: #6c757d; color: white; border: 1px solid #6c757d; }
.mifeco-btn-outline { background-color: transparent; color: #007bff; border: 1px solid #007bff; }
.mifeco-current-plan-note, .mifeco-plan-change-note { font-size: 0.9em; color: #555; margin-top: 10px; }
.mifeco-error-message { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
.mifeco-info-message { color: #0c5460; background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; border-radius: 4px; margin-bottom: 20px; }

.mifeco-payment-modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
.mifeco-payment-modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 5px; position: relative; }
.mifeco-payment-modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
.mifeco-payment-modal-close:hover, .mifeco-payment-modal-close:focus { color: black; text-decoration: none; cursor: pointer; }
.mifeco-payment-modal-header h3 { margin-top: 0; }
.mifeco-checkout-loading .mifeco-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 0 auto 10px auto; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
