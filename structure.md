1. SYSTEM ARCHITECTURE (YOUR STACK)

You’ll follow a Custom PHP MVC Architecture (No Framework)

🔷 Structure Overview
/app
  /controllers
  /models
  /views
  /core
  /helpers

/public
  /assets (css, js, images)
  index.php (entry point)

/config
  database.php

/routes
  web.php
🔹 MVC Breakdown
🧠 Model (Business Logic + DB)

Handles MySQLi queries

OOP-based classes

Example:

class BoardingHouse {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM boarding_houses");
        $stmt->execute();
        return $stmt->get_result();
    }
}
🎮 Controller (Flow Control)

Handles request logic

Connects Model → View

class BoardingHouseController {
    public function index() {
        $model = new BoardingHouse($GLOBALS['db']);
        $data = $model->getAll();
        require '../app/views/boarding/index.php';
    }
}
🎨 View (UI)

Pure HTML + CSS + JS

Use jQuery + AJAX for dynamic UI

🔄 2. SYSTEM FLOW (AJAX-BASED)

Your system will be SPA-like (without React) using jQuery AJAX.

🔹 Example: Booking Flow
User clicks "Book"
→ AJAX request (POST /booking/create)
→ Controller receives request
→ Model saves booking
→ Return JSON response
→ SweetAlert success popup
→ UI updates without reload
🔹 Example AJAX
$.ajax({
    url: '/booking/store',
    type: 'POST',
    data: {
        room_id: 1,
        start_date: '2026-03-20'
    },
    success: function(res) {
        Swal.fire('Success', 'Booking created!', 'success');
    }
});
🗄️ 3. DATABASE DESIGN (OPTIMIZED FOR YOUR STACK)
🔑 Key Improvement: Add constraints & indexing
users
id INT AUTO_INCREMENT PRIMARY KEY
name VARCHAR(100)
email VARCHAR(100) UNIQUE
password VARCHAR(255)
role ENUM('admin','owner','tenant')
created_at TIMESTAMP
boarding_houses
id INT AUTO_INCREMENT PRIMARY KEY
owner_id INT
name VARCHAR(150)
description TEXT
address TEXT
latitude DECIMAL(10,8)
longitude DECIMAL(11,8)
status ENUM('pending','approved')
created_at TIMESTAMP

INDEX(owner_id)
rooms
id INT AUTO_INCREMENT PRIMARY KEY
boarding_house_id INT
room_name VARCHAR(100)
price DECIMAL(10,2)
capacity INT
available_slots INT
created_at TIMESTAMP

INDEX(boarding_house_id)
bookings
id INT AUTO_INCREMENT PRIMARY KEY
user_id INT
room_id INT
start_date DATE
end_date DATE
status ENUM('pending','approved','rejected','cancelled')
total_amount DECIMAL(10,2)
created_at TIMESTAMP

INDEX(user_id, room_id)
payments
id INT AUTO_INCREMENT PRIMARY KEY
user_id INT
booking_id INT
amount DECIMAL(10,2)
payment_method VARCHAR(50)
status ENUM('pending','paid','failed')
transaction_ref VARCHAR(100)
created_at TIMESTAMP
plans
id INT AUTO_INCREMENT PRIMARY KEY
name VARCHAR(50)
price_monthly DECIMAL(10,2)
price_yearly DECIMAL(10,2)
room_limit INT
features JSON
subscriptions
id INT AUTO_INCREMENT PRIMARY KEY
owner_id INT
plan_id INT
start_date DATE
end_date DATE
status ENUM('active','expired')
⚙️ 4. CORE SYSTEM LOGIC (IMPORTANT)
🔐 Role-Based Access (RBAC)
if ($_SESSION['role'] !== 'admin') {
    header('Location: /unauthorized');
}
📦 Plan Limit Enforcement

Before inserting room:

$currentRooms = $this->countRooms($owner_id);
$limit = $this->getPlanLimit($owner_id);

if ($currentRooms >= $limit) {
    return ['status' => 'error', 'message' => 'Room limit reached'];
}
💳 Payment Flow (PayMongo Style)
User books room
→ Create booking (pending)
→ Redirect to payment gateway
→ Payment success callback
→ Update booking status to approved
→ Save transaction
🧩 5. ROUTING SYSTEM (IMPORTANT)

Create a simple router:

$url = $_GET['url'] ?? 'home/index';
$url = explode('/', $url);

$controller = ucfirst($url[0]) . 'Controller';
$method = $url[1] ?? 'index';

require "../app/controllers/$controller.php";

(new $controller)->$method();
🎨 6. UI/UX STRUCTURE
Pages
Public

Home

Boarding listings (with map)

View details

Tenant Dashboard

My bookings

Payments

Favorites

Owner Dashboard

Boarding houses

Rooms

Bookings

Earnings

Admin Panel

Users

Plans

Reports

🔔 7. NOTIFICATIONS SYSTEM

Use:

AJAX polling (simple)

OR setInterval()

setInterval(function(){
    $.get('/notifications', function(data){
        $('#notif-count').text(data.count);
    });
}, 5000);
🧠 8. SECURITY (VERY IMPORTANT)

Password hashing:

password_hash($password, PASSWORD_BCRYPT);

Prepared statements (✅ you're using MySQLi)

CSRF tokens

Input validation (server-side ALWAYS)

File upload validation (images)

🚀 9. PERFORMANCE TIPS

Use pagination (LIMIT)

Lazy load images

Cache queries if needed

Optimize indexes

🔥 10. ADVANCED FEATURES (FITTING YOUR STACK)

Google Maps API (pin location)

Image upload with preview (jQuery)

Calendar booking UI

Real-time chat (AJAX polling)

SweetAlert confirmations

Export reports (PDF)

💡 CTO ADVICE (IMPORTANT)

With your stack:

You can build a clean SaaS platform

Focus on:

Clean MVC separation

Reusable models

API-like controllers (return JSON)

👉 Treat your backend like an API even if it's PHP.