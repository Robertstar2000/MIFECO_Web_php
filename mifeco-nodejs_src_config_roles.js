/**
 * User roles and permissions configuration
 */

// Define all available roles
const roles = ['user', 'admin', 'consultant'];

// Define role rights for each route
const roleRights = new Map();

// User role - basic authenticated access
roleRights.set('user', [
  'getMe',
  'updateMe',
  'deleteMe',
  'viewConsultants',
  'bookConsultation',
  'getProducts',
  'getSingleProduct',
  'createLead',
  'getMySubscriptions',
  'cancelSubscription',
  'updatePaymentMethod',
  'createCheckoutSession',
  'createSubscription'
]);

// Admin role - full system access
roleRights.set('admin', [
  'getUsers',
  'manageUsers',
  'getMe',
  'updateMe',
  'deleteMe',
  'viewConsultants',
  'manageConsultants',
  'bookConsultation',
  'manageConsultations',
  'getProducts',
  'getSingleProduct',
  'manageProducts',
  'getLeads',
  'manageLeads',
  'createLead',
  'getSubscriptions',
  'manageSubscriptions',
  'getMySubscriptions',
  'cancelSubscription',
  'updatePaymentMethod',
  'createCheckoutSession',
  'createSubscription',
  'viewReports',
  'exportData'
]);

// Consultant role - consultant-specific access
roleRights.set('consultant', [
  'getMe',
  'updateMe',
  'deleteMe',
  'viewConsultants',
  'getMyConsultations',
  'manageMyConsultations',
  'getProducts',
  'getSingleProduct',
  'getMyLeads',
  'updateLeadStatus',
  'createLead',
  'getMySubscriptions',
  'cancelSubscription',
  'updatePaymentMethod',
  'createCheckoutSession',
  'createSubscription'
]);

module.exports = {
  roles,
  roleRights
};