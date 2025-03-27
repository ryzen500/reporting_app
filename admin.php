<!DOCTYPE html>
<html lang="en">
<?php
require_once 'backend/config.php'; // Pastikan koneksi ke database ada di file ini

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

// Query untuk mengambil data dari database
$baseQuery = "SELECT t.lookup_value 
               FROM lookup_m t  
               WHERE t.lookup_type = $1";

// Eksekusi query dengan parameter
$lookup = pg_query_params($conn, $baseQuery, ['insertketerangan']);
$lookupResults = pg_fetch_all($lookup);
if ($lookupResults) {
  $values = array_column($lookupResults, 'lookup_value'); // Extract lookup_value column
  $lookupString = implode(',', $values); // Convert array to a comma-separated string
  $allow_add_temp = explode(',', $lookupString);
} else {
  $allow_add_temp = [];
}
$allow_add = json_encode($allow_add_temp); // Convert PHP array to JSON
$base_url = get_base_url();
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Laporan </title>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      background-color: #e4e4e4;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice {

    color: black;

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
              <h1>Data Laporan Respon Time KRS</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                <li class="breadcrumb-item active">Data Laporan Respon Time KRS</li>
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
                <!-- <div class="card-header">
                  <h3 class="card-title">Data Laporan Respon Time KRS</h3>
                  <div class="card-tools">
                  </div>
                </div> -->
                <div class="card p-3" style="background-color:rgb(255, 255, 255);">
                  <h3 class="mb-3">Filter Laporan</h3>
                  <div class="row mb-2">
                      <div class="col-md-3">
                          <label class="form-label">Periode</label>
                          <select class="form-control" id="periode">
                            <option>--Pilih--</option>
                            <option value="KRS" selected>Tanggal KRS</option>
                            <option value="Pembayaran">Tanggal Pembayaran</option>
                            <option value="Advis">Tgl Advis KRS</option>
                            <option value="Admisi">Tanggal Admisi</option>
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
                  </div>
                  <div class="row">

                      <div class="col-md-3">
                          <label class="form-label">No RM</label>
                          <input type="text" id="no_rekam_medik" class="form-control" placeholder="No RM">
                      </div>
                      
                      <div class="col-md-3">
                        <label class="form-label">No Pendaftaran</label>
                        <input type="text" id="no_pendaftaran" class="form-control" placeholder="No Pendaftaran">
                      </div>

                      <div class="col-md-6">
                          <label class="form-label">Ruangan</label>
                          <select id="ruanganSelect" class="form-control">
                              <option value="">--Pilih--</option>
                          </select>
                      </div>
                      <div class="col-md-6 d-flex align-items-end">
                          <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="sudahKRS" checked>
                              <label class="form-check-label" for="sudahKRS">Sudah KRS</label>
                          </div>
                          <div class="form-check ml-2">
                              <input class="form-check-input" type="checkbox" id="pasienBpjs" checked>
                              <label class="form-check-label" for="pasienBpjs">Pasien BPJS</label>
                          </div>
                      </div>

                  </div>
          
                  <div class="col-md-12 mt-3">
                    <button id="searchBtn1" onclick="cari();" class="btn btn-primary">Cari </button>
                    <button id="searchBtn2" class="btn btn-secondary">Batal</button>
                  </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>No.</th>
                        <th>Ruangan</th>
                        <th>No. Rekam Medik  / <br> Nama Pasien  / <br> No Pendaftaran </th>
                        <th>Advis KRS</th>
                        <th>SK Ke Farmasi</th>
                        <th>SK Ke Farmasi Selesai</th>
                        <th>Entry Resep</th>
                        <th>Jam Pembayaran</th>
                        <th>Jam Pasien Pulang</th>
                        <th>Total Waktu</th>
                        <th>Keterangan</th>
                      </tr>
                    </thead>
                    <tbody id="dataPasien">
                      <!-- Data rows will be inserted here -->
                    </tbody>
                  </table>
                </div>
                <!-- Modal -->
                <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitle">Keterangan</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modalBody">
                                <!-- Data dari AJAX akan ditampilkan di sini -->

                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">Tutup</span>
                                </button>
                                <button type="button" class="btn btn-primary" id="simpanKeterangan" onclick="simpanKeterangan()">Simpan</button>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php // include 'footer.php'; ?>
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
    function cari() {
        const filters = {
            periode: $("#periode").val() || "",
            nama_pasien: $("#nama_pasien").val() || "",
            no_rekam_medik: $("#no_rekam_medik").val() || "",
            ruanganSelect: $("#ruanganSelect").val() || "",
            pasienBpjs: $("#pasienBpjs").prop("checked") ? "1" : "0",
            sudahKRS: $("#sudahKRS").prop("checked") ? "1" : "0",
            no_pendaftaran: $("#no_pendaftaran").val() || "",        
            dateRangePicker: $("#dateRangePicker").val() || ""
        };
        if ($.fn.DataTable.isDataTable("#example1")) {
          $('#example1').DataTable().clear().destroy();
        }
        loadData(filters);
    }
    function checkSession() {
      return $.ajax({
        url: 'backend/sessionData.php', // The PHP file we created to return session data
        type: 'GET',
        dataType: 'json',
      });
    }
 
    function loadData(filters = {}) {
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
                  if (Object.keys(filters).length === 0) {
                      let filters_temp = {
                          periode: $("#periode").val() || "",
                          nama_pasien: $("#nama_pasien").val() || "",
                          no_rekam_medik: $("#no_rekam_medik").val() || "",
                          ruanganSelect: $("#ruanganSelect").val() || "",
                          pasienBpjs: $("#pasienBpjs").prop("checked") ? "1" : "0",
                          sudahKRS: $("#sudahKRS").prop("checked") ? "1" : "0",
                          no_pendaftaran: $("#no_pendaftaran").val() || "",        
                          dateRangePicker: $("#dateRangePicker").val() || ""
                      };
                      filters=filters_temp;
                    }
                    return {
                        draw:d.draw,
                        limit: d.length, // Menggunakan length sebagai limit
                        offset: d.start, // Menggunakan start sebagai offset
                        searchValue: d.search.value || "", // Kirim parameter pencarian jika ada
                        ...filters
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
                { data: null, render: data => `${data.no_rekam_medik} / <br> ${data.nama_pasien} / <br> ${data.no_pendaftaran}` || '-' },
                {
                    data: null,
                    render: function (data,type,row) {
                      const tgl_adviskrs = (row.tgl_adviskrs === ''||row.tgl_adviskrs ===null) ? '-' : row.tgl_adviskrs;
                      const pegawai_adviskrs = (row.pegawai_adviskrs === ''||row.pegawai_adviskrs ===null) ? '-' : row.pegawai_adviskrs;
                      const instalasi_id = <?php echo $_SESSION['instalasi_id']?> 
                      const instalasi_allow = [4,76];
                      if( (row.tgl_adviskrs === ''||row.tgl_adviskrs ===null) &&(row.pegawai_adviskrs === ''||row.pegawai_adviskrs ===null) && instalasi_allow.includes(instalasi_id)){
                        return renderButtons(row.pasienadmisi_id);
                      }else{
                        if(instalasi_allow.includes(instalasi_id)){
                          return `${tgl_adviskrs} / ${pegawai_adviskrs}`;
                        }else{
                          if(tgl_adviskrs!='-' || pegawai_adviskrs!='-'){
                            return `${tgl_adviskrs} / ${pegawai_adviskrs}`;
                          }else{
                            return '-'
                          }
                        }
                      }
                    }
                },
                {
                    data: null,
                    render: function (data,type,row) {
                      const tgl_skfarmasi = (row.tgl_skfarmasi === ''||row.tgl_skfarmasi ===null) ? '-' : row.tgl_skfarmasi;
                      const  pegawai_skfarmasi= (row.pegawai_skfarmasi === ''||row.pegawai_skfarmasi ===null) ? '-' : row.pegawai_skfarmasi;
                      const instalasi_id = <?php echo $_SESSION['instalasi_id']?> 
                      const instalasi_allow = [4,76];
                      if( (tgl_skfarmasi==='-') && (pegawai_skfarmasi==='-')  && instalasi_allow.includes(instalasi_id)){
                        return renderButtonsk(row.pasienadmisi_id);
                      }else{
                        if(instalasi_allow.includes(instalasi_id)){
                          return `${tgl_skfarmasi} / ${pegawai_skfarmasi}`;
                        }else{
                          if(tgl_skfarmasi!='-' || pegawai_skfarmasi!='-'){
                            return `${tgl_skfarmasi} / ${pegawai_skfarmasi}`;
                          }else{
                            return '-'
                          }
                        }
                      }
                    }
                },
                {
                    data: null,
                    render: function (data,type,row) {
                      const tgl_verifikasifarmasi = (row.tgl_verifikasifarmasi === ''||row.tgl_verifikasifarmasi ===null) ? '-' : row.tgl_verifikasifarmasi;
                      return `${tgl_verifikasifarmasi}`;
                    }
                },
                { data: 'tglreseptur' },
                { data: 'tglpembayaran' },
                {data: 'tglpulang' },
                {
                    data: null,
                    render: function (data,type,row) {

                      return `${data.totalWaktu}<br><span style="color:${data.color}">${data.keteranganTotal}</span>`;
                    }
                },
                {
                    data: null,
                    render: function (data,type,row) {
                      // if (Array.isArray(row.loopKeterangan) && row.loopKeterangan.length > 0) {
                      //   let result = ''; // Variabel untuk menyimpan hasil looping
                      //   row.loopKeterangan.forEach(item => {
                      //       result += (item.nama_keterangan || 'No Data') + ', ';
                      //   });
                      //   return result; // Menghapus koma dan spasi terakhir                  
                      // }else{
                      //   return "-";
                      // }   
                      const instalasi_id = "<?php echo $_SESSION['instalasi_id']?>";
                      const instalasi_allow = <?php echo $allow_add ?>;  
                      // const instalasi_allow = [4,76];  
                      if (Array.isArray(row.loopKeterangan) && row.loopKeterangan.length > 0) {
                        let result = ''; // Variabel untuk menyimpan hasil looping
                        row.loopKeterangan.forEach(item => {
                          let nama_pegawai = '';
                            if(item.update_loginpemakai_id != null && (item.update_loginpemakai_id != '') ){
                              nama_pegawai = item.nama_update +' / '+item.update_time ;
                            }else{
                              nama_pegawai = item.nama_create +' / '+item.create_time ;
                            }
                            // result += (item.keterangan || '-') + '<br> ';
                            if(item.ruangan_id == <?php echo $_SESSION['ruangan_id']?>){
                              result += `<b>${item.ruangan_nama} (${nama_pegawai})</b>` +' : <br>'+ item.keterangan;
                              result += ` <a href="#" class="openDialogUpdate" onclick="openDialogUpdate(${item.keteranganrespontime_id})" data-toggle="tooltip" title="Klik untuk merubah keterangan"><i class="fa fa-pencil-alt text-primary"></i></a>`;
                              result += ` <a href="#" class="openDialogDelete" onclick="openDialogDelete(${item.keteranganrespontime_id})" data-toggle="tooltip" title="Klik untuk menghapus keterangan"><i class="fa fa-trash text-danger"></i></a>`;
                              result +=  ' <br>';

                            }else{
                              result +=`<b>${item.ruangan_nama} (${nama_pegawai})</b>`  +' : <br>'+ item.keterangan+ ' <br>';
                            }
                        });

                        if( instalasi_allow.includes(instalasi_id)){
                          result += `<div class="col-sm-12 text-center"><a href="#" class="openDialogAdd" data-id="${row.pasienadmisi_id}" onclick="openDialogAdd(${row.pasienadmisi_id},${row.pendaftaran_id})" style="color:green" data-toggle="tooltip" title="Klik untuk menambahkan keterangan"><i class="fa-solid fa-circle-plus"></i></a></div>`;
                        }
                        return result; // Menghapus koma dan spasi terakhir    
                        // return `<a href="#" class="openDialog" data-id="${row.pasienadmisi_id}">Tambah Keterangan</a>`;

                      } else {
                        if( instalasi_allow.includes(instalasi_id)){
                          return `<div class="col-sm-12 text-center"><a href="#" class="openDialogAdd" data-id="${row.pasienadmisi_id}" onclick="openDialogAdd(${row.pasienadmisi_id},${row.pendaftaran_id})" style="color:green" data-toggle="tooltip" title="Klik untuk menambahkan keterangan"><i class="fa-solid fa-circle-plus"></i></a></div>`;
                        }else{
                          return '-';
                        }
                      }
                    }
                },
                
            ],
            pageLength: 10,
            buttons: [
                { extend: 'colvis', columns: ':not(.noVis)' },
                {
                  text: 'Download Excel',
                  action: function (e, dt, node, config) {
                    downloadExcel(filters);
                    // console.log("applyFilter", applyFilter);

                  }
                },
                { extend: 'copy', exportOptions: { columns: ':visible' } }
            ],

            initComplete: function () {
                dataTable.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
            }
        });
      // Saat proses loading data dimulai (disable button)
      $('#example1').on('processing.dt', function (e, settings, processing) {
          $("#searchBtn1").prop("disabled", processing);
      });

    }
    function downloadExcel(filters = {}) {
      // Construct the query string from filters
      const params = new URLSearchParams(filters).toString();
      // Redirect to the backend script with filter parameters
      window.location.href = `backend/LoadDataFormLaporanExcelKrs.php?action=export_excel&${params}`;
    }
    function simpanKeterangan() {
        let keterangan = $("#keterangan").val(); // Ambil nilai dari textarea
        let pasienadmisi_id = $("#pasienadmisi_id").val(); // Ambil ID pasien
        let pendaftaran_id = $("#pendaftaran_id").val(); // Ambil ID pasien
        let keteranganrespontime_id = $("#keteranganrespontime_id").val(); // Ambil ID pasien
        if (keteranganrespontime_id === null || keteranganrespontime_id === undefined || keteranganrespontime_id === "") {
            keteranganrespontime_id =null;
        }
        if (!keterangan.trim()) {
            Swal.fire('Error', 'Data Keterangan tidak boleh kosong!', 'error');
        }else{
          $.ajax({
              url: "backend/simpanKeterangan.php",
              type: "POST",
              data: {
                  pasienadmisi_id: pasienadmisi_id,
                  pendaftaran_id: pendaftaran_id,
                  jenis: 'Respon Time KRS',
                  keteranganrespontime_id:keteranganrespontime_id,
                  keterangan: keterangan
              },
              success: function (response) {
                  // let res = JSON.parse(response);
                  let res = response;
                  if (res.status === "success") {
                      Swal.fire('Tersimpan!', 'Data berhasil disimpan.', 'success');
                      $("#myModal").modal("hide"); // Tutup modal
                      $('#example1').DataTable().clear().destroy();
                      loadData();
                  } else {
                    Swal.fire('Error', 'Gagal Simpan Data', 'error');
                  }
              },
              error: function () {
                Swal.fire('Error', 'Gagal Simpan Data', 'error');
              }
          });

        }

    }

    function renderButtons(id) {
      let buttons = '';
      buttons += `<button type="button" class="btn btn-success btn-sm" onclick="advisKrs(${id});" data-toggle="tooltip" title="Klik untuk update jam advis krs">Masukkan Jam Advis KRS</button>`;
      return buttons;
    }
    function renderButtonsk(id) {
      let buttons = '';
      buttons += `<button type="button" class="btn btn-success btn-sm" onclick="skFarmasi(${id});" data-toggle="tooltip" title="Klik untuk update jam sk farmas" >Masukkan Jam SK Farmasi</button>`;
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

    function advisKrs(id) {
      Swal.fire({
        title: 'Anda yakin?',
        text: "Masukkan Jam Advis KRS",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'backend/updateJamAdvis.php',
            type: 'POST',
            data: { id },
            success: function (response) {
              Swal.fire('Tersimpan!', 'Data berhasil disimpan.', 'success');
              $('#example1').DataTable().clear().destroy();
              loadData();
            },
            error: function (error) {
              console.error('Error Save data:', error);
              Swal.fire('Error!', 'Terjadi kesalahan saat simpan data.', 'error');
            }
          });
        }
      });
    }
    function skFarmasi(id) {
      Swal.fire({
        title: 'Anda yakin?',
        text: "Masukkan Jam SK Farmasi",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'backend/updateJamKrs.php',
            type: 'POST',
            data: { id },
            success: function (response) {
              Swal.fire('Tersimpan!', 'Data berhasil disimpan.', 'success');
              $('#example1').DataTable().clear().destroy();
              loadData();
            },
            error: function (error) {
              console.error('Error Save data:', error);
              Swal.fire('Error!', 'Terjadi kesalahan saat simpan data.', 'error');
            }
          });
        }
      });
    }
    function openDialogDelete(keteranganrespontime_id) {
      Swal.fire({
        title: 'Anda yakin?',
        text: "Hapus Keterangan",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'backend/deleteKeterangan.php',
            type: 'POST',
            data: { keteranganrespontime_id },
            success: function (response) {
              Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
              $('#example1').DataTable().clear().destroy();
              loadData();
            },
            error: function (error) {
              console.error('Error Save data:', error);
              Swal.fire('Error!', 'Terjadi kesalahan saat dihapus data.', 'error');
            }
          });
        }
      });
    }
    function openDialogUpdate(keteranganrespontime_id){
          // Buat AJAX request untuk mengambil data tambahan (jika diperlukan)
          $.ajax({
            url: 'backend/GetEditDetailKeterangan.php',
            type: 'POST',
            data: { keteranganrespontime_id: keteranganrespontime_id },
            success: function (response) {
                $('#modalBody').html(response); // Masukkan data ke dalam modal
                $('#myModal').modal('show');   // Tampilkan modal
            },
            error: function () {
                alert('Gagal mengambil data.');
            }
        });
    }
    function openDialogAdd(pasienadmisi_id,pendaftaran_id){
          // Buat AJAX request untuk mengambil data tambahan (jika diperlukan)
          $.ajax({
            url: 'backend/GetDetailKeterangan.php',
            type: 'POST',
            data: { pasienadmisi_id: pasienadmisi_id, pendaftaran_id:pendaftaran_id},
            success: function (response) {
                $('#modalBody').html(response); // Masukkan data ke dalam modal
                $('#myModal').modal('show');   // Tampilkan modal
            },
            error: function () {
                alert('Gagal mengambil data.');
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
                  // placeholder: "--Pilih--",
                  allowClear: true,
                  multiple: true, // Mengaktifkan multi-select
                  width: '100%' // Membuat Select2 responsif
              });
          });
      } else {
          $("#ruanganSelect").select2({
              // placeholder: "--Pilih--",
              allowClear: true,
              width: '100%', // Membuat Select2 responsif
              multiple: true // Mengaktifkan multi-select

          });
      }

      const URL_API = "backend/LoadRuanganKrs.php";
      // Mengambil data dari API menggunakan Axios
      axios.get(URL_API)
          .then(response => {
              const data = response.data.options; // Sesuaikan dengan struktur data dari API
              
              // console.log("data ", response);
              // Hapus opsi default jika perlu
              // $("#ruanganSelect").html('<option value="">--Pilih--</option>');
              $("#ruanganSelect").html('<option value=""></option>');
              let selectedValues = [];
              const instalasi_id = <?php echo $_SESSION['instalasi_id']?> 
              const ruangan_id = <?php echo $_SESSION['ruangan_id']?> 

              const instalasi_allow = [4,76];

              // Looping data dan menambahkan option ke dalam select
              $.each(data, function(index, item) {
                // console.log("Item ", item);

                if(instalasi_allow.includes(instalasi_id)){
                  if(ruangan_id == item.ruangan_id){
                    selectedValues.push(item.ruangan_id); // Tambahkan ke array yang akan dipilih
                  }
                }
                  $("#ruanganSelect").append(
                      `<option value="${item.ruangan_id}">${item.ruangan_nama}</option>`
                  );
              });
            // Setelah semua data ditambahkan, set nilai terpilih
            $("#ruanganSelect").val(selectedValues).trigger("change");
          })
          .catch(error => {
              console.error("Error fetching data: ", error);
          });

    }
    $(document).ready(function () {
      // Load data into DataTable
      loadData();
      loadDataRuangan();
    });

    // Initialize the date range picker
    let isRangeMode = true; // Default mode adalah range

    document.addEventListener("DOMContentLoaded", function () {
      const today = new Date();
      const yesterday = new Date();
      yesterday.setDate(today.getDate() - 1);

      const formattedToday = today.toISOString().split('T')[0]; // Format YYYY-MM-DD
      const formattedYesterday = today.toISOString().split('T')[0];
        flatpickr("#dateRangePicker", {
            mode: "range",
            dateFormat: "Y-m-d", // Format tanggal
            defaultDate: [formattedYesterday, formattedToday], // Set default range to yesterday and today
            onClose: function(selectedDates, dateStr, instance) {
                console.log("Selected range:", dateStr);
            }
        });
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