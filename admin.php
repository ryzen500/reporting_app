<!DOCTYPE html>
<html lang="en">
<?php


function get_base_url()
{
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'];
  $script = $_SERVER['SCRIPT_NAME'];
  $path = dirname($script); // Get the path without the script name
  $base_url = $protocol . '://' . $host . $path;

  return rtrim($base_url, '/') . '/';
}

$base_url = get_base_url();
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Laporan </title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="./plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="./plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="./plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="./plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="./dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.2/dist/sweetalert2.min.css">
  <style>
    .container {
      padding-top: 50px;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <!-- Navbar -->

    <?php include 'navbar.php'; ?>
    <!-- /.navbar -->

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Data Laporan</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                <li class="breadcrumb-item active">Data Laporan</li>
              </ol>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <!-- Table -->
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Data Laporan</h3>
                  <div class="card-tools">
                  </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>No.</th>
                        <th>Ruangan</th>
                        <th>No. Rekam Medik </th>
                        <th>Nama  Pasien</th>
                        <!-- <th>Jam Advice KRS</th>
                        <th>Jam SK Ke Farmasi</th>
                        <th>Jam SK Ke Selesai</th>
                        <th>Jam Pasien RPP</th>
                        <th>Jam SK Ke Kasir</th>
                        <th>Total Waktu</th>
                        <th>Kesimpulan</th> -->
                      </tr>
                    </thead>
                    <tbody id="dataPasien">
                      <!-- Data rows will be inserted here -->
                    </tbody>
                  </table>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include 'footer.php'; ?>
  </div>

  <!-- External Scripts -->
  <script src="./plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables  & Plugins -->

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

  <script src="./plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="./plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="./plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="./plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  <script src="./plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="./plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
  <script src="./plugins/jszip/jszip.min.js"></script>
  <script src="./plugins/pdfmake/pdfmake.min.js"></script>
  <script src="./plugins/pdfmake/vfs_fonts.js"></script>
  <script src="./plugins/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="./plugins/datatables-buttons/js/buttons.print.min.js"></script>
  <script src="./plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <script src="./plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.2/dist/sweetalert2.all.min.js"></script>

  <script>
    $.widget.bridge('uibutton', $.ui.button)
  </script>
  <script>
    var base_url = "<?php echo $base_url ?>";

    function checkSession() {
      return $.ajax({
        url: 'backend/sessionData.php', // The PHP file we created to return session data
        type: 'GET',
        dataType: 'json',
      });
    }
 
    function loadData() {
    const dataTable = $('#example1').DataTable({
        serverSide: true,
        processing: true,
        responsive: true,
        lengthChange: false,
        paging: true, // Aktifkan paging
        autoWidth: false,
        ajax: {
            url: `backend/LoadDataKRSBPJS.php`,
            type: 'GET',
            data: function (d) {
                return {
                    limit: d.length, // Menggunakan length sebagai limit
                    offset: d.start, // Menggunakan start sebagai offset
                    searchValue: d.search.value || "" // Kirim parameter pencarian jika ada
                };
            },
            error: function (xhr, error, thrown) {
                console.error("Error loading data:", error, thrown);
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'ruangan_nama' },
            {
                data: 'no_rekam_medik',
                render: function (data) {
                    return data ? data : '-';
                }
            },
            { data: 'nama_pasien' }
        ],
        pageLength: 10,
        buttons: [
            { extend: 'colvis', columns: ':not(.noVis)' },
            { extend: 'excel', exportOptions: { columns: ':visible' } },
            { extend: 'csv', exportOptions: { columns: ':visible' } },
            { extend: 'pdf', exportOptions: { columns: ':visible' } },
            { extend: 'copy', exportOptions: { columns: ':visible' } },
            { extend: 'print', exportOptions: { columns: ':visible' } }
        ],
        initComplete: function () {
            dataTable.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        }
    });
}





    function renderButtons(id, sessionData) {
      let buttons = '';
      if (sessionData.update === "1") {
        buttons += `<button type="button" class="btn btn-info btn-sm" onclick="editData(${id})">Edit</button> `;
      }
      if (sessionData.delete === "1") {
        buttons += `<button type="button" class="btn btn-danger btn-sm" onclick="deleteData(${id})">Delete</button> `;
      }
      buttons += `<button type="button" class="btn btn-primary btn-sm" onclick="printData(${id});">Print</button>`;
      return buttons;
    }

    function editData(id) {
      console.log('Edit data with ID:', id);
      location.href = base_url + `edit.php?id=${id}`;
    }

    function printData(id) {
      console.log('Edit data with ID:', id);
      window.open(base_url + `mpdf?id=${id}`, "_blank");
      // location.href = base_url + `mpdf.php?id=${id}`;
    }

    function deleteData(id) {
      Swal.fire({
        title: 'Anda yakin?',
        text: "Data akan dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'backend/deleteData.php',
            type: 'POST',
            data: { id },
            success: function (response) {
              Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
              $('#example1').DataTable().clear().destroy();
              loadData();
            },
            error: function (error) {
              console.error('Error deleting data:', error);
              Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data.', 'error');
            }
          });
        }
      });
    }

    $(document).ready(function () {
      // Load data into DataTable
      loadData();
    });
  </script>

  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- ChartJS -->
  <script src="plugins/chart.js/Chart.min.js"></script>
  <!-- Sparkline -->
  <script src="plugins/sparklines/sparkline.js"></script>
  <!-- JQVMap -->
  <script src="plugins/jqvmap/jquery.vmap.min.js"></script>
  <script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
  <!-- jQuery Knob Chart -->
  <script src="plugins/jquery-knob/jquery.knob.min.js"></script>
  <!-- daterangepicker -->
  <script src="plugins/moment/moment.min.js"></script>
  <script src="plugins/daterangepicker/daterangepicker.js"></script>
  <!-- Tempusdominus Bootstrap 4 -->
  <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
  <!-- Summernote -->
  <script src="plugins/summernote/summernote-bs4.min.js"></script>
  <!-- overlayScrollbars -->
  <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.js"></script>
  <!-- AdminLTE for demo purposes -->
  <!-- <script src="dist/js/demo.js"></script> -->
  <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
  <!-- External Scripts -->

  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


</body>

</html>