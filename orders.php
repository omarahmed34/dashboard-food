<?php
require_once 'db.php';
// This page provides a standalone view of orders, matching the premium dashboard style.
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلبات العملاء | BiteSight</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css?v=3.0" />
    <link rel="stylesheet" href="luxury.css?v=1.0" />
    <style>
        body {
            background-color: var(--main-bg);
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .page-header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-Pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-Completed { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-Cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 style="font-weight:900; color:var(--purple);">📦 سجل الطلبات</h1>
                <p style="color:var(--text-muted);">عرض وإدارة طلبات الطعام الواردة من العملاء</p>
            </div>
            <a href="dashboard.html" class="btn-primary" style="text-decoration:none; padding:12px 24px;">العودة للوحة التحكم</a>
        </div>

        <div class="panel">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>التواصل</th>
                            <th>العنوان</th>
                            <th>الموعد</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody id="ordersBody">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        async function fetchOrders() {
            try {
                const res = await fetch('api.php?action=getOrders');
                const json = await res.json();
                if (json.status === 'success') {
                    renderOrders(json.data);
                } else {
                    console.error('Error:', json.message);
                }
            } catch (err) {
                console.error('Fetch error:', err);
            }
        }

        function renderOrders(orders) {
            const tbody = document.getElementById('ordersBody');
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:40px;">لا توجد طلبات مسجلة بعد</td></tr>';
                return;
            }
            tbody.innerHTML = orders.map(o => `
                <tr>
                    <td>${o.id}</td>
                    <td>
                        <div style="font-weight:700;">${o.full_name}</div>
                        <small style="color:var(--text-muted);">${o.emall || o.email || '-'}</small>
                    </td>
                    <td>
                        <div>📞 ${o.phone}</div>
                        ${o.location_link ? `<a href="${o.location_link}" target="_blank" style="font-size:11px; color:var(--purple);">📍 رابط الموقع</a>` : ''}
                    </td>
                    <td><div style="max-width:250px; white-space:normal; font-size:13px;">${o.address}</div></td>
                    <td>
                        <div style="font-weight:600;">📅 ${o.delivery_date}</div>
                        <small>🕒 ${o.delivery_time}</small>
                    </td>
                    <td>
                        <span class="status-badge status-${o.status}">${o.status}</span>
                    </td>
                </tr>
            `).join('');
        }

        fetchOrders();
    </script>
</body>
</html>
