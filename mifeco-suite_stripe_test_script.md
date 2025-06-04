# Stripe Integration Test Script

This test script provides a step-by-step process to validate the Stripe payment integration for the MIFECO Suite WordPress plugin. Run through these tests before deploying to production to ensure all payment functionalities work correctly.

## Prerequisites

Before starting the tests:

1. Ensure the MIFECO Suite plugin is installed and activated
2. Configure Stripe in test mode with valid test API keys
3. Set up webhook endpoints correctly
4. Create test products and prices in Stripe
5. Configure subscription plans in the plugin settings

## Test Environment Setup

1. **Enable Testing Mode**:
   - Go to **MIFECO Suite > Stripe Settings**
   - Ensure "Test Mode" is enabled
   - Verify test API keys are entered correctly
   - Save settings

2. **Enable Debug Logging**:
   - Add the following to your `wp-config.php`:
     ```php
     define('WP_DEBUG', true);
     define('WP_DEBUG_LOG', true);
     ```
   - This will log errors to `wp-content/debug.log`

## Test A: Subscription Checkout

### A1: Basic Subscription Signup

**Test Steps**:
1. Navigate to the subscription plans page
2. Click on the "Subscribe" button for the Basic plan
3. Complete the checkout form with test card `4242 4242 4242 4242`
4. Submit the form

**Expected Results**:
- Redirect to success page
- New subscription created in Stripe
- User roles updated appropriately
- Subscription confirmation email sent
- Subscription appears in user dashboard

### A2: Failed Payment Test

**Test Steps**:
1. Navigate to the subscription plans page
2. Click on the "Subscribe" button for any plan
3. Complete the checkout form with test card `4000 0000 0000 0002` (generic decline)
4. Submit the form

**Expected Results**:
- Payment error message displayed
- No subscription created in Stripe
- User roles unchanged
- Error handling works gracefully

### A3: 3D Secure Authentication Test

**Test Steps**:
1. Navigate to the subscription plans page
2. Click on the "Subscribe" button for any plan
3. Complete the checkout form with test card `4000 0000 0000 3220` (requires authentication)
4. Submit the form
5. Complete the 3D Secure authentication flow

**Expected Results**:
- 3D Secure authentication modal appears
- After authentication, payment succeeds
- Subscription created successfully
- User roles updated appropriately

## Test B: Subscription Management

### B1: Upgrade Subscription

**Test Steps**:
1. Create a test user with a Basic subscription
2. Log in as that user
3. Navigate to the user dashboard
4. Click "Upgrade" to Premium plan
5. Complete checkout with test card

**Expected Results**:
- Subscription in Stripe is updated
- User roles updated to reflect new plan
- Confirmation email sent
- Dashboard shows updated subscription

### B2: Downgrade Subscription

**Test Steps**:
1. Create a test user with a Premium subscription
2. Log in as that user
3. Navigate to the user dashboard
4. Click "Downgrade" to Basic plan
5. Confirm the action

**Expected Results**:
- Subscription in Stripe is updated
- User roles updated to reflect new plan
- Confirmation email sent
- Dashboard shows updated subscription

### B3: Cancel Subscription

**Test Steps**:
1. Create a test user with an active subscription
2. Log in as that user
3. Navigate to the user dashboard
4. Click "Cancel Subscription"
5. Confirm the action

**Expected Results**:
- Subscription marked as canceled in Stripe
- User notified of cancellation and end date
- User retains access until current period ends
- Dashboard shows subscription as canceled

## Test C: One-Time Payments

### C1: Consulting Service Purchase

**Test Steps**:
1. Navigate to consulting services page
2. Select a consulting service (e.g., Initial Consultation)
3. Click the "Purchase" button
4. Complete checkout with test card `4242 4242 4242 4242`
5. Submit the form

**Expected Results**:
- Payment succeeds
- Order created in database
- Confirmation email sent
- Receipt page displayed

### C2: Failed Consulting Payment

**Test Steps**:
1. Navigate to consulting services page
2. Select a consulting service
3. Click the "Purchase" button
4. Complete checkout with test card `4000 0000 0000 9995` (insufficient funds)
5. Submit the form

**Expected Results**:
- Payment error message displayed
- No order created
- Error handled gracefully

## Test D: Coupons and Discounts

### D1: Apply Valid Coupon

**Test Steps**:
1. Create a test coupon in admin (e.g., 25% off)
2. Navigate to subscription plans page
3. Start subscription checkout process
4. Enter coupon code in the coupon field
5. Apply coupon
6. Complete checkout

**Expected Results**:
- Coupon successfully applied
- Discount reflected in checkout amount
- Order created with discounted amount
- Coupon usage recorded in Stripe

### D2: Apply Invalid Coupon

**Test Steps**:
1. Navigate to subscription plans page
2. Start subscription checkout process
3. Enter invalid coupon code
4. Attempt to apply coupon

**Expected Results**:
- Error message indicating invalid coupon
- No discount applied
- Checkout can still proceed at full price

## Test E: Webhook Functionality

### E1: Subscription Update via Stripe Dashboard

**Test Steps**:
1. Create a test subscription through the website
2. Log in to Stripe Dashboard
3. Find the subscription and update it (e.g., change plan)
4. Wait for webhook delivery (usually immediate)

**Expected Results**:
- Changes from Stripe Dashboard reflected in WordPress
- User roles updated accordingly
- Dashboard shows updated subscription details

### E2: Subscription Cancellation via Stripe Dashboard

**Test Steps**:
1. Create a test subscription through the website
2. Log in to Stripe Dashboard
3. Find the subscription and cancel it
4. Wait for webhook delivery

**Expected Results**:
- Cancellation reflected in WordPress
- User notified of cancellation
- Dashboard shows subscription as canceled

### E3: Failed Payment Handling

**Test Steps**:
1. Create a test subscription with the card `4000 0000 0000 3063` (will decline after successful charge)
2. Wait for renewal attempt
3. Check webhook logs

**Expected Results**:
- Failed payment webhook received
- User notified of payment failure
- Subscription marked as past due
- User prompted to update payment method

## Test F: Customer Portal Integration

### F1: Access Customer Portal

**Test Steps**:
1. Create a test user with an active subscription
2. Log in as that user
3. Navigate to the user dashboard
4. Click "Manage Subscription" or "Billing Portal"
5. Should redirect to Stripe Customer Portal

**Expected Results**:
- Successful redirect to Stripe Customer Portal
- User can view invoices, update payment method, etc.
- Changes made in portal reflected in WordPress after webhook delivery

## Test G: Security Testing

### G1: API Key Security

**Test Steps**:
1. View page source of checkout page
2. Search for "sk_test_" or "sk_live_"

**Expected Results**:
- No secret keys visible in page source
- Only publishable keys (pk_test_ or pk_live_) should be present

### G2: AJAX Nonce Verification

**Test Steps**:
1. Open browser developer tools
2. Attempt to submit AJAX request without nonce

**Expected Results**:
- Request should fail with security error
- No database changes should occur

## Test H: Email Notifications

### H1: Subscription Confirmation Email

**Test Steps**:
1. Create a new subscription
2. Check email for subscription confirmation

**Expected Results**:
- Email received with correct subscription details
- Links in email work correctly
- Formatting is proper

### H2: Payment Failed Email

**Test Steps**:
1. Create a subscription with card `4000 0000 0000 0341` (requires authentication for future payments)
2. Wait for renewal attempt or simulate failed payment
3. Check email

**Expected Results**:
- Email notification about failed payment
- Clear instructions for updating payment method
- Links in email work correctly

## Test I: Error Handling

### I1: Network Error During Checkout

**Test Steps**:
1. Start checkout process
2. Using browser developer tools, simulate offline mode before final submission
3. Submit payment form

**Expected Results**:
- Graceful error handling
- User informed of connection issues
- No duplicate charges

### I2: Server Error Response

**Test Steps**:
1. Temporarily modify webhook handler to throw an exception
2. Create a test subscription
3. Check error logs

**Expected Results**:
- Error logged properly
- No critical system failure
- Ability to recover and process webhook later

## Test J: Mobile Responsiveness

### J1: Mobile Checkout Experience

**Test Steps**:
1. Access checkout page on mobile device or using responsive mode in browser developer tools
2. Complete checkout process

**Expected Results**:
- All elements properly sized and positioned
- Form fields easily accessible
- Checkout completes successfully

## Post-Testing Tasks

After completing all tests:

1. **Review Error Logs**: Check for any unexpected errors or warnings
2. **Review Stripe Dashboard**: Verify all test transactions and subscriptions look correct
3. **Clean Up Test Data**: Cancel test subscriptions and refund test payments
4. **Document Issues**: Document any issues or unexpected behaviors for fixing
5. **Prepare for Production**: If all tests pass, prepare for switching to production mode

## Production Launch Checklist

Before going live with real payments:

1. **Switch API Keys**: Change to production API keys
2. **Disable Test Mode**: Turn off test mode in plugin settings
3. **Update Webhook Endpoints**: Ensure webhook endpoints are set up for production
4. **Test with Real Card**: Make a small real purchase to verify everything works
5. **Monitor Transactions**: Closely monitor initial transactions for any issues

By thoroughly testing each aspect of the Stripe integration using this script, you can ensure a smooth, secure payment experience for your users.