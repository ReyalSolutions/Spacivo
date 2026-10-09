// A decision comes before leaving the current management page.
window.showUpgradeRequired = function (subscriptionId, cycle) {
  return Feedback.fire({
    icon: 'info', title: 'Upgrade required',
    text: 'Choose a plan with more properties, rooms and features to continue.',
    showCancelButton: true, confirmButtonText: 'View plans', cancelButtonText: 'Not now',
    confirmButtonColor: '#18181b', cancelButtonColor: '#71717a'
  }).then(function (result) {
    if (!result.isConfirmed) return;
    var target = '/tenant/?url=admin/upgrade';
    if (Number(subscriptionId) > 0) target += '&subscription_id=' + encodeURIComponent(subscriptionId);
    if (cycle === 'yearly' || cycle === 'monthly') target += '&cycle=' + cycle;
    window.location.href = target;
  });
};
