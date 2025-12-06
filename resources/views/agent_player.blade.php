@extends('admin.body.adminmaster')

@section('admin')

<!DOCTYPE html>
<html>
<head>
    <title>Agent User Dashboard</title>

    <!-- DataTable CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <style>
        body { background: #eef3f9; font-family: 'Poppins', sans-serif; }

        .wrapper { width: 100%; display: flex; }
        .content-wrapper-fixed { width: 100%; padding: 25px; }

        /* 🔵 Top Welcome Header */
        .welcome-header {
            background: linear-gradient(135deg, #00539c, #007dc5);
            padding: 22px;
            color: #fff;
            text-align: center;
            border-radius: 14px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            font-size: 26px;
            font-weight: 600;
        }

        /* ⭐ Summary 3 per row */
        .summary-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-box {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(10px);
            padding: 22px;
            border-radius: 14px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.4);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transition: 0.3s;
        }

        .summary-box:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.25);
        }

        .summary-box h4 { font-size: 16px; opacity: .8; margin-bottom: 5px }
        .summary-box h2 { font-size: 30px; font-weight: 700; color: #003f6b; margin: 0 }

        /* Modern Card */
        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,.08);
            margin-bottom: 30px;
        }

        .card h3 {
            font-size: 20px;
            font-weight: 600;
            color: #004c7a;
            margin-bottom: 15px;
            border-left: 6px solid #007dc5;
            padding-left: 10px;
        }

        /* Modern Table Header */
        table.dataTable thead th {
            background: #00539c;
            color: #fff;
            text-align: center;
        }

        table.dataTable tbody tr:hover {
            background: #d9edff !important;
        }

        .status-pill {
            padding: 4px 10px;
            border-radius: 5px;
            color: #fff;
            font-size: 13px;
        }
        .pending { background: #ffcc00; color: #000; }
        .success { background: #28a745; }
        .rejected { background: #dc3545; }
        .win { background: #28a745; }
        .loss { background: #dc3545; }

    </style>
</head>

<body>

<div class="wrapper">
<div class="content-wrapper-fixed">

   

    


    {{-- USERS LIST --}}
    <div class="card">
        <h3>USERS LIST</h3>
        <table id="usersTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Mobile</th><th>Join Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->mobile }}</td>
                    <td>{{ $u->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


   
</div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $('#usersTable, #depositsTable, #withdrawsTable, #wingoTable, #dragonTable, #aviatorTable').DataTable({
        pageLength: 10,
        responsive: true
    });
</script>

</body>
</html>

@endsection
