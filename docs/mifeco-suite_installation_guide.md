# MIFECO WordPress Plugin Installation Guide

This guide provides step-by-step instructions for installing and setting up the MIFECO Suite WordPress plugin on your Dreamhost WordPress site.

## Prerequisites

- A WordPress site hosted on Dreamhost
- Admin access to your WordPress site
- Stripe account (for payment processing)

## Installation Steps

### 1. Download the Plugin

1. Download the MIFECO Suite plugin zip file from the provided source.

### 2. Install the Plugin via WordPress Admin

1. Log in to your WordPress admin dashboard.
2. Navigate to **Plugins > Add New**.
3. Click the **Upload Plugin** button at the top of the page.
4. Click **Choose File** and select the `mifeco-suite.zip` file you downloaded.
5. Click **Install Now**.
6. After installation completes, click **Activate Plugin**.

### 3. Configure Plugin Settings

#### General Settings

1. Navigate to **MIFECO Suite > Settings** in your WordPress admin menu.
2. Configure the general plugin settings:
   - Set your business information
   - Configure email notification settings
   - Set up lead generation form defaults

#### Stripe Integration

1. Navigate to **MIFECO Suite > Subscriptions > Settings**.
2. Enter your Stripe API keys:
   - **Stripe Public Key**: Your Stripe publishable key (starts with `pk_`)
   - **Stripe Secret Key**: Your Stripe secret key (starts with `sk_`)
3. Configure subscription plans:
   - **Basic Plan**: Set price, Stripe Price ID, and description
   - **Premium Plan**: Set price, Stripe Price ID, and description
   - **Enterprise Plan**: Set price, Stripe Price ID, and description

> **Note**: To get your Stripe API keys, log in to your [Stripe Dashboard](https://dashboard.stripe.com/), go to **Developers > API keys**.

#### Create Stripe Products and Prices

1. Log in to your [Stripe Dashboard](https://dashboard.stripe.com/).
2. Navigate to **Products > Add Product** for each subscription tier:
   - Create a "Basic Plan" product
   - Create a "Premium Plan" product
   - Create a "Enterprise Plan" product
3. For each product, create a recurring price:
   - Set the appropriate amount ($99 for Basic, $199 for Premium, $499 for Enterprise)
   - Set billing period to monthly
   - Copy the "Price ID" (starts with `price_`) for each plan
4. Enter these Price IDs in the plugin settings (MIFECO Suite > Subscriptions > Settings)

### 4. Set Up Pages

Create the following pages in WordPress to use with the plugin:

#### Subscriptions Page

1. Create a new page titled "Subscriptions"
2. Add the following shortcode to the page content:
```
[mifeco_saas_subscription]
```

#### Products Showcase Page

1. Create a new page titled "Products"
2. Add the following shortcode to the page content:
```
[mifeco_saas_products]
```

#### My Account Page

1. Create a new page titled "My Account"
2. Add the following shortcode to the page content:
```
[mifeco_saas_account]
```

#### Individual Product Pages (Optional)

Create individual pages for each product using these shortcodes:

1. Advanced Research Tool:
```
[mifeco_research_tool]
```

2. Ultimate Business Problem Solver:
```
[mifeco_problem_solver]
```

3. Proposal Evaluation Tool:
```
[mifeco_proposal_tool]
```

### 5. Configure WordPress Settings

#### Permalinks

1. Navigate to **Settings > Permalinks**.
2. Select "Post name" as the permalink structure.
3. Click **Save Changes**.

#### User Registration

1. Navigate to **Settings > General**.
2. Check the box for "Anyone can register".
3. Set "New User Default Role" to "Subscriber".
4. Click **Save Changes**.

### 6. Test the Installation

1. Visit your site's frontend and navigate to the Products page.
2. Test user registration and login functionality.
3. Test the subscription process with Stripe's test mode:
   - Use test card number: 4242 4242 4242 4242
   - Use any future expiration date and any 3-digit CVC
   - Use any billing address

### 7. Go Live

Once testing is complete:

1. Update your Stripe API keys to production keys.
2. Remove any test data or accounts.
3. Ensure all pages and forms are working correctly.

## Troubleshooting

### Plugin Activation Errors

If you encounter errors during plugin activation:

1. Check PHP version requirements (PHP 7.4+ recommended).
2. Ensure all required PHP extensions are enabled.
3. Check WordPress version compatibility (WordPress 5.6+ recommended).

### Stripe Integration Issues

If Stripe integration isn't working:

1. Verify API keys are entered correctly.
2. Check that Stripe webhook is configured properly.
3. Ensure the correct Price IDs are entered for each plan.

### Page Display Problems

If shortcodes aren't displaying correctly:

1. Ensure the plugin is activated.
2. Try switching to a default WordPress theme to rule out theme conflicts.
3. Disable other plugins to check for plugin conflicts.

## Support

For additional help or support:

- Visit our support website: [MIFECO Support](https://mifeco.com/support)
- Email: support@mifeco.com

## Updates

The plugin will notify you when updates are available. Always backup your site before updating.

---

Thank you for choosing MIFECO Suite for your business needs!