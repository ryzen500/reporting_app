<!DOCTYPE html>
<html lang="en">
<?php

session_start();
if (!isset( $_SESSION['nama_pemakai'])) {
  header("Location: login.php");
}

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
  <title>Data Laporan MRS dari IGD </title>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="stylesheet" href="./plugins/overlayScrollbars/css/OverlayScrollbars.min.css">


  <!-- jQuery (harus dimuat dulu) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>



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
              <h1>Data Laporan MRS dari IGD</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                <li class="breadcrumb-item active">Data Laporan MRS dari IGD</li>
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
            <div class="card p-3" style="background-color:rgb(255, 255, 255);">
            <h3 class="mb-3">Filter Laporan</h3>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label class="form-label">Periode</label>
                    <select class="form-control" id="periode">
                        <option>--Pilih--</option>
                        <option value="Pendaftaran">Tanggal Pendaftaran</option>
                        <option value="Admisi">Tanggal Admisi</option>
                        <option value="Terima">Jam Timbang Terima</option>
                        <option value="Advis">Tgl Advis MRS</option>
                      </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tgl </label>
                    <input type="text" id="dateRangePicker" class="form-control" placeholder="Select Date Range">

                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" id="nama_pasien" class="form-control" placeholder="Nama">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sudahMRS">
                        <label class="form-check-label" for="sudahMRS">Sudah MRS</label>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-md-6">
                    <label class="form-label">No RM</label>
                    <input type="text" id="no_rekam_medik" class="form-control" placeholder="No RM">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ruangan</label>
                    <select id="ruanganSelect" class="form-control">
                        <option>--Pilih--</option>
                    </select>
                </div>


            </div>
        
            <div class="col-md-12 mt-3">
            <button id="searchBtn1" onclick="cari();" class="btn btn-primary">Cari </button>
            <button id="searchBtn2" class="btn btn-secondary">Batal</button>
            </div>
        </div>
              <!-- Table -->
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Data Laporan MRS dari IGD</h3>
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
                        <th>Jam Advice KRS</th>
                        <th>Terbit SPRI</th>
                        <th>Selesai Pendaftaran</th>
                        <th>Jam Timbang Terima</th>
                        <th>Total Waktu</th>
                        <th>Keterangan</th>
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

    // function checkSession() {
    //   return $.ajax({
    //     url: 'backend/sessionData.php', // The PHP file we created to return session data
    //     type: 'GET',
    //     dataType: 'json',
    //   });
    // }


    function cari(){
      
      const periode = $("#periode").val();
      const nama_pasien = $("#nama_pasien").val();
      const dateRangePicker = $("#dateRangePicker").val();
      const no_rekam_medik = $("#no_rekam_medik").val();
      const ruanganSelect = $("#ruanganSelect").val();

      console.log("No Rekam MEdik ", no_rekam_medik);
      if ($.fn.DataTable.isDataTable("#example1")) {
            $("#example1").DataTable().destroy();
        }
      const dataTable = $('#example1').DataTable({
        serverSide: true,
        processing: true,
        responsive: true,
        lengthChange: false,
        paging: true, // Aktifkan paging
        autoWidth: false,
        ajax: {
            url: `backend/LoadDataMRSBPJS.php`,
            type: 'GET',
            data: function (d) {
                return {
                    draw:d.draw,
                    limit: d.length, // Menggunakan length sebagai limit
                    offset: d.start, // Menggunakan start sebagai offset
                    searchValue: d.search.value || "",  // Kirim parameter pencarian jika ada,
                    periode : periode || "",
                    nama_pasien : nama_pasien || "",
                    no_rekam_medik: no_rekam_medik  || "",
                    ruanganSelect : ruanganSelect || "",
                    dateRangePicker : dateRangePicker || ""
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
            { data: 'nama_pasien' },
            {
                    data: null,
                    render: function (data) {
                        return data.tgl_advismrs !== null 
                            ? `${data.tgl_advismrs} <br> ${data.pegawai_advismrs}`
                            : `<button class="btn btn-success" onclick="handleClickAdvis(${data.pendaftaran_id},${data.tgl_advismrs})">Masukkan Jam Advis MRS</button>`;
                    }
                },

            {
                data: null,
                render: function (data) {
                  return data.tgl_suratperintahranap !== null ? data.tgl_suratperintahranap : `-`;
                }
            },


            {
                data: null,
                render: function (data) {
                  return data.tgladmisi !== null ? data.tgladmisi : `-`;
                }
            },


            {
                data: null,
                render: function (data) {
                  return data.tgl_timbangterima !== null 
    ? `${data.tgl_timbangterima} <br> ${data.pegawai_timbangterima}` 
    : `<button class="btn btn-success" onclick="handleClick(${data.pendaftaran_id}, ${data.tgl_timbangterima})"> Masukkan Jam Timbang Terima </button>`;
                
  }
            },
        
                      {
                    data: null,
                    render: function (data) {
                        if (data.tgl_timbangterima && data.tgl_advismrs) {
                            const startTime = new Date(data.tgl_timbangterima);
                            const endTime = new Date(data.tgl_advismrs);
                            const diffMs = endTime - startTime;
                            const diffHrs = Math.floor(diffMs / 3600000);
                            const diffMins = Math.floor((diffMs % 3600000) / 60000);
                            const diffSecs = Math.floor((diffMs % 60000) / 1000);
                            // return `-`;
                            return ` Selisih: ${diffHrs} jam, ${diffMins} menit, ${diffSecs} detik`;
                        }
                        return `-`;
                    }
                },
        

        
        

            {
                data: null,
                render: function (data) {
                  return data.tgl_timbangterima !== null ? `${data.tgl_timbangterima} <br> ${data.pegawai_timbangterima}` : ` - `;
                }
            },
        

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
    function loadDataRuangan(){

    // Tambahkan Select2 CDN
    const select2CDN = "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js";
    const select2CSS = "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css";
    
    // Tambahkan CSS Select2 jika belum ada
    if (!$('link[href="' + select2CSS + '"]').length) {
        $('head').append(`<link href="${select2CSS}" rel="stylesheet">`);
    }
    
    // Tambahkan JS Select2 jika belum ada
    if (!$('script[src="' + select2CDN + '"]').length) {
        $.getScript(select2CDN, function () {
            $("#ruanganSelect").select2({
                placeholder: "--Pilih--",
                allowClear: true,
                multiple: true // Mengaktifkan multi-select
            });
        });
    } else {
        $("#ruanganSelect").select2({
            placeholder: "--Pilih--",
            allowClear: true,
            multiple: true // Mengaktifkan multi-select
        });
    }
    
    const URL_API = "backend/LoadRuangan.php";
    
    // Mengambil data dari API menggunakan Axios
    axios.get(URL_API)
        .then(response => {
            const data = response.data.options; // Sesuaikan dengan struktur data dari API
            
            // console.log("data ", response);
            // Hapus opsi default jika perlu
            $("#ruanganSelect").html('<option value="">--Pilih--</option>');
            
            // Looping data dan menambahkan option ke dalam select
            $.each(data, function(index, item) {
              // console.log("Item ", item);
                $("#ruanganSelect").append(
                    `<option value="${item.ruangan_id}">${item.ruangan_nama}</option>`
                );
            });
        })
        .catch(error => {
            console.error("Error fetching data: ", error);
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
            url: `backend/LoadDataMRSBPJS.php`,
            type: 'GET',
            data: function (d) {
                return {
                    draw:d.draw,
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
            { data: 'nama_pasien' },
            {
                data: null,
                render: function (data) {
                  return data.tgl_advismrs !== null ? data.tgl_advismrs : `<button class="btn btn-success"> Masukkan Jam Advis MRS </button>`;
                }
            },

            {
                data: null,
                render: function (data) {
                  return data.tgl_suratperintahranap !== null ? data.tgl_suratperintahranap : `-`;
                }
            },


            {
                data: null,
                render: function (data) {
                  return data.tgladmisi !== null ? data.tgladmisi : `-`;
                }
            },


            {
                data: null,
                render: function (data) {
                  // return `Demos`;
                  return data.tgl_timbangterima !== null 
    ? `${data.tgl_timbangterima} <br> ${data.pegawai_timbangterima}` 
    : `<button class="btn btn-success" onclick="handleClick(${data.pendaftaran_id}, ${data.tgl_timbangterima})"> Masukkan Jam Timbang Terima </button>`;
                }
            },
        

            {
                data: null,
                render: function (data) {
                  return data.tgl_timbangterima !== null ? `Demo` : ` - `;
                }
            },
        

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



    function handleClick(pendaftaran_id, tgl_timbangterima) {
      Swal.fire({
      title: "Apakah anda yakin ?",
      text: "Anda yakin untuk menginput data ?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Ya , Saya Yakin"
    }).then((result) => {
      if (result.isConfirmed) {
        let today = new Date();

      // Mendapatkan bagian tahun, bulan, hari, jam, menit, dan detik
      let year = today.getFullYear();
      let month = String(today.getMonth() + 1).padStart(2, '0'); // Bulan dimulai dari 0, jadi perlu menambah 1
      let day = String(today.getDate()).padStart(2, '0');
      let hours = String(today.getHours()).padStart(2, '0');
      let minutes = String(today.getMinutes()).padStart(2, '0');
      let seconds = String(today.getSeconds()).padStart(2, '0');

      // Formatkan tanggal dan waktu menjadi yyyy-mm-dd H:i:s
      let formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

      let pegawai_timbangterima = '<?php echo  $_SESSION['nama_pegawai']; ?>';
        tgl_timbangterima = formattedDateTime;

        $.ajax({
            url: 'backend/UpdateDataMRS.php',
            type: 'POST',
            data: { pendaftaran_id: pendaftaran_id, tgl_timbangterima : tgl_timbangterima , pegawai_timbangterima : pegawai_timbangterima },
            success: function (response) {
              Swal.fire('Berhasil Update!', 'Data berhasil disimpan.', 'success');
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


    function handleClickAdvis(pendaftaran_id, tgl_advismrs) {
      Swal.fire({
      title: "Apakah anda yakin ?",
      text: "Anda yakin untuk menginput data ?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Ya , Saya Yakin"
    }).then((result) => {
      if (result.isConfirmed) {
        let today = new Date();

      // Mendapatkan bagian tahun, bulan, hari, jam, menit, dan detik
      let year = today.getFullYear();
      let month = String(today.getMonth() + 1).padStart(2, '0'); // Bulan dimulai dari 0, jadi perlu menambah 1
      let day = String(today.getDate()).padStart(2, '0');
      let hours = String(today.getHours()).padStart(2, '0');
      let minutes = String(today.getMinutes()).padStart(2, '0');
      let seconds = String(today.getSeconds()).padStart(2, '0');

      // Formatkan tanggal dan waktu menjadi yyyy-mm-dd H:i:s
      let formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

      let pegawai_advismrs = '<?php echo  $_SESSION['nama_pegawai']; ?>';
      tgl_advismrs = formattedDateTime;

        $.ajax({
            url: 'backend/UpdateDataMRSAdvis.php',
            type: 'POST',
            data: { pendaftaran_id: pendaftaran_id, tgl_advismrs : tgl_advismrs , pegawai_advismrs : pegawai_advismrs },
            success: function (response) {
              Swal.fire('Berhasil Update!', 'Data berhasil disimpan.', 'success');
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

    document.addEventListener("DOMContentLoaded", function () {
    flatpickr("#dateRangePicker", {
        mode: "range",
        dateFormat: "Y-m-d", // Format tanggal
        onClose: function(selectedDates, dateStr, instance) {
            console.log("Selected range:", dateStr);
        }
    });
});

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
      loadDataRuangan();
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