# StayHub RBAC Technical Manifest 🛡️🔑

This document serves as the **definitive reference** for the Role-Based Access Control (RBAC) architecture. It maps system-wide sidebar links to functional permission categories and provide a guide for privilege orchestration.

---

## 🏗️ Category-to-Sidebar Mapping

When defining new permissions, use these **Category Slugs** to ensure they appear under the correct module in the "Define Role" matrix.

| CATEGORY       | SIDEBAR MODULE         | KEY LINKS / FEATURES                                  |
| :------------- | :--------------------- | :---------------------------------------------------- |
| **`core`**     | **Core System**        | Dashboard, Notifications, Analytics, Profile          |
| **`users`**    | **Identity Mgmt**      | User Mgmt, Roles & Permissions, Owners/Tenants        |
| **`assets`**   | **Property Portfolio** | Boarding Houses, Listings, Bookings, Rooms            |
| **`finance`**  | **Finance & Growth**   | Transactions, Payments, Pricing Plans, Subscriptions  |
| **`config`**   | **System Admin**       | General Settings, Feature Flags, Maintenance Controls |
| **`insights`** | **Insights**           | Reviews, Customer Feedback, Revenue Reports           |

---

## 🛠️ Seeding Your Permissions

I have created a dedicated engine at `rbac_manifest.php`. You can execute it to automatically populate your database with all essential tokens and synchronize them with the **Administrator** role.

### **How to Sync:**

1. Open your terminal in the project root.
2. Run: `php rbac_manifest.php`
3. All permissions will be categorized and mapped instantly.

---

## 💻 Developer Implementation Guide

To enforce these permissions in your code, use the following syntax:

### **1. Logic Guard (Controller)**

```php
if (!$this->hasPermission('audit_finances')) {
    $this->redirect('admin/dashboard', 'error', 'Unauthorized Access.');
}
```

### **2. UI Guard (View)**

```php
<?php if ($currentUser->hasPermission('view_revenue')): ?>
    <div class="revenue-widget"> ... </div>
<?php endif; ?>
```

---

## 📝 Manifest Architecture Example

Here are some pre-defined tokens ready for your system:

- **`list_users`** (`users`): View the complete identity directory.
- **`approve_houses`** (`assets`): Verify and activate pending property listings.
- **`audit_finances`** (`finance`): Access and verify sensitive payment transactions.
- **`system_settings`** (`config`): Modify global platform configurations.

---

**RBAC Governance Hub** | _Architecture by Antigravity_ 🚀🎨✨
