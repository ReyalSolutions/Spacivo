<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Account - <?= $pageTitle ?? 'Resident Statement' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: white;
            color: #1e293b;
            line-height: 1.5;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; }
            .container { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body class="py-5">
    <div class="container no-print mb-4 text-end">
        <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-bold rounded-pill">
            <i class="fa-solid fa-print me-2"></i>Print Statement
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-4 py-2 fw-bold rounded-pill ms-2">
            Close Window
        </button>
    </div>

    <div class="container shadow-sm border p-5 bg-white rounded-4">
        <?php echo $content; ?>
    </div>

    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
