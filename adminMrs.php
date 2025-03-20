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
  <!-- <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
    -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
    
            </div>
            
            
            <div class="row">

                <div class="col-md-6">
                    <label class="form-label">No RM</label>
                    <input type="text" id="no_rekam_medik" class="form-control" placeholder="No RM">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sudahMRS">
                        <label class="form-check-label" for="sudahMRS">Sudah MRS</label>
                    </div>
                </div>

                
           


                <div class="col-md-6">
                          <label class="form-label">Ruangan</label>
                          <select id="ruanganSelect" class="form-control">
                              <option value="">--Pilih--</option>
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
                        <th>No. Rekam Medik  / <br> Nama Pasien </th>
                        <th>Jam Advice MRS</th>
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

                <!-- Dialog -->
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
                <!-- Dialog Close -->
              
              
                <!-- Dialog Edit  -->
  <!-- Modal -->
        <div class="modal fade" id="myModalEdit" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitle">Keterangan</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modalBodyEdit">
                                <!-- Data dari AJAX akan ditampilkan di sini -->

                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">Tutup</span>
                                </button>
                                <button type="button" class="btn btn-primary" id="updateKeterangan" onclick="updateKeterangan()">Update</button>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- CLose DIalog Edit -->
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

    function initDataTable(filters = {}) {
      if(filters.length == 0 ){
        const filters = {
        periode: $("#periode").val() || "",
        nama_pasien: $("#nama_pasien").val() || "",
        no_rekam_medik: $("#no_rekam_medik").val() || "",
        ruanganSelect: $("#ruanganSelect").val() || "",
        dateRangePicker: $("#dateRangePicker").val() || "",
        sudahMRS: $("#sudahMRS").prop("checked") ? true : false // Cek checkbox
    };
    }
      if ($.fn.DataTable.isDataTable("#example1")) {
        $('#example1').DataTable().clear().destroy();
      }

    $("#example1").DataTable({
        serverSide: true,
        processing: true,
        responsive: true,
        lengthChange: false,
        paging: true,
        autoWidth: false,
        ajax: {
            url: "backend/LoadDataMRSBPJS.php",
            type: "GET",
            data: d => ({
                draw: d.draw,
                limit: d.length,
                offset: d.start,
                searchValue: d.search.value || "",
                ...filters
            }),
            error: (xhr, error, thrown) => console.error("Error loading data:", error, thrown)
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'ruangan_nama' },
            { data: null, render: data => `${data.no_rekam_medik} / <br> ${data.nama_pasien}` || '-' },
            { 
                data: null, 
                render: data => {
                    const ruanganId = "<?php echo $_SESSION['ruangan_id'] ?? ''; ?>";
                    const instalasiId = "<?php echo $_SESSION['instalasi_id'] ?? ''; ?>";

                    if (instalasiId ==  2  && !data.tgl_advismrs|| instalasiId == 8 && !data.tgl_advismrs || instalasiId == 3 && !data.tgl_advismrs ||  instalasiId == 73 && !data.tgl_advismrs) {
                        return `<button class="btn btn-success btn-sm" 
                                    onclick="handleClickAdvis(${data.pendaftaran_id})" 
                                    data-toggle="tooltip" title="Klik untuk update jam advis mrs">
                                    <span>Masukkan Jam Advis MRS</span>
                                </button>`;
                    }
                    return data.tgl_advismrs ? `${data.tgl_advismrs} <br> ${data.pegawai_advismrs}` : '-';
                }
            },
            { data: null, render: data => data.tgl_suratperintahranap || '-' },
            { data: null, render: data => data.tgladmisi || '-' },
            { 
                data: null, 
                render: data => {
                    const instalasiId = "<?php echo $_SESSION['instalasi_id'] ?? ''; ?>";

                    console.log("instalasiId ", instalasiId);
                    let  displayTanggalTimbang = "";
                            if (instalasiId == 4 && !data.tgl_timbangterima || instalasiId == 76  && !data.tgl_timbangterima) {
                          displayTanggalTimbang =  `<button class="btn btn-success btn-sm" 
                            onclick="handleClick(${data.pendaftaran_id})" 
                            data-toggle="tooltip" title="Klik untuk update jam timbang terima">
                            <span>Masukkan Jam Timbang Terima</span>
                                      </button>`;
                          }else{
                            
                              displayTanggalTimbang =data.tgl_timbangterima ? `${data.tgl_timbangterima} <br> ${data.pegawai_timbangterima}` : '-'; 
                          
                            }

                        return  displayTanggalTimbang;
                }
            },
            { data: null, render: data => `${data.totalWaktu}<br><span style="color:${data.color}">${data.keteranganTotal}</span>` },
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
                      const lookupArray = row.lookupInsertKeterangan[0].split(",").map(Number);
                      console.log("Row ", row.loopKeterangan.length > 0);
                      // Cek apakah session.instalasi_id ada dalam lookupArray
                      const isAllowed = lookupArray.includes(Number(<?php echo $_SESSION['instalasi_id']?>));

                      if ( row.loopKeterangan.length > 0) {
                        let result = ''; // Variabel untuk menyimpan hasil looping
                        row.loopKeterangan.forEach(item => {
                            // result += (item.keterangan || '-') + '<br> ';
                            console.log("Item Ruangan ",  <?php echo $_SESSION['ruangan_id']?>);
                            console.log("Item Ruangan ",  parseInt(item.ruangan_id) );

                            if(parseInt(item.ruangan_id) == <?php echo $_SESSION['ruangan_id']?>){
                              console.log("Kick Ruangan");

                              result += `<b>${item.ruangan_nama}</b>` +' : '+ item.keterangan;
                              result += `  <a href="#" class="editKeterangan" data-id="${item.keteranganrespontime_id}" data-toggle="tooltip" title="Klik untuk merubah keterangan">
                                        <i class="fa fa-pencil-alt text-primary"></i>
                                    </a>`;
                              result += `  <a href="#" class="deleteKeterangan" data-id="${item.keteranganrespontime_id}" data-toggle="tooltip" title="Klik untuk menghapus keterangan">
                                        <i class="fa fa-trash text-danger"></i>
                                    </a>`;
                              result +=  ' <br>';

                            }else{

                              
                              result += item.ruangan_nama +' : '+ item.keterangan+ ' <br>';

                            }
                        });

                        result += `<div class="col-sm-12 text-center"> 
                                <a href="#" class="openDialogAdd" style="text-align:center" data-id="${row.pendaftaran_id}" data-toggle="tooltip" title="Klik untuk menambahkan keterangan">
                                    <i class="fa fa-plus-circle text-success"></i> 
                                </a></div>`;
                        return  isAllowed ? result : '<div class="col-sm-12 text-center"> - </div>' ; // Menghapus koma dan spasi terakhir    
                        // return `<a href="#" class="openDialog" data-id="${row.pasienadmisi_id}">Tambah Keterangan</a>`;

                      } else {
                        return  isAllowed ?  `<div class="col-sm-12 text-center"><a href="#" class="openDialogAdd" style="text-align:center" data-id="${row.pendaftaran_id}" data-toggle="tooltip" title="Klik untuk menambahkan keterangan">
                                    <i class="fa fa-plus-circle text-success"></i> 
                                </a></div>` : '<div class="col-sm-12 text-center"> - </div>' ; // Menghapus koma dan spasi terakhir    
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
            this.api().buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        }
    });

  }

  function updateKeterangan() {
        let keterangan = $("#keteranganMRS").val(); // Ambil nilai dari textarea
        let keteranganrespontime_id = $("#keteranganrespontime_id").val(); // Ambil ID pasien

        if (!keterangan.trim()) {
            Swal.fire('Error', 'Data Keterangan tidak boleh kosong!', 'error');
        }else{
          $.ajax({
              url: "backend/updateKeteranganMRS.php",
              type: "POST",
              data: {
                keteranganrespontime_id: keteranganrespontime_id,
                  keterangan: keterangan
              },
              success: function (response) {
          
                    Swal.fire('Berhasil Update!', 'Data berhasil disimpan.', 'success');
                    $("#myModalEdit").modal("hide"); // Tutup modal
                    cari();
                  },
              error: function () {
                  alert("Terjadi kesalahan saat menyimpan data.");
              }
          });

        }

    }


    function simpanKeterangan() {
        let keterangan = $("#keterangan").val(); // Ambil nilai dari textarea
        let pendaftaran_id = $("#pendaftaran_id").val(); // Ambil ID pasien

        if (!keterangan.trim()) {
            Swal.fire('Error', 'Data Keterangan tidak boleh kosong!', 'error');
        }else{
          $.ajax({
              url: "backend/simpanKeteranganMRS.php",
              type: "POST",
              data: {
                pendaftaran_id: pendaftaran_id,
                  keterangan: keterangan
              },
              success: function (response) {
                
                // let res = JSON.parse(response);
                let res = response;
                if (res.status === "success") {
                      // alert("Keterangan berhasil disimpan!");
                      Swal.fire('Tersimpan!', 'Data berhasil disimpan.', 'success');

                      $("#myModal").modal("hide"); // Tutup modal
                      
                      cari();
                    } else {
                      alert("Gagal menyimpan keterangan: " + res.message);
                  }
              },
              error: function () {
                  alert("Terjadi kesalahan saat menyimpan data.");
              }
          });

        }

    }


    function cari() {
    const filters = {
        periode: $("#periode").val() || "",
        nama_pasien: $("#nama_pasien").val() || "",
        no_rekam_medik: $("#no_rekam_medik").val() || "",
        ruanganSelect: $("#ruanganSelect").val() || "",
        dateRangePicker: $("#dateRangePicker").val() || "",
        sudahMRS: $("#sudahMRS").prop("checked") ? true : false // Cek checkbox
    };
    initDataTable(filters);
}
    function loadDataRuangan(){

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
    
    const URL_API = "backend/LoadRuangan.php";
    
    axios.get(URL_API)
    .then(response => {
        const data = response.data.options; // Sesuaikan dengan struktur data dari API

        let selectedValues = [];
        const instalasi_id = <?php echo $_SESSION['instalasi_id']?>;
        const ruangan_id = <?php echo $_SESSION['ruangan_id']?>;

        const instalasi_allow = [2,8,3,73];

        // Looping data dan menambahkan option ke dalam select
        $.each(data, function(index, item) {
            $("#ruanganSelect").append(
                `<option value="${item.ruangan_id}">${item.ruangan_nama}</option>`
            );
        });

        // Cek apakah instalasi_id ada dalam daftar instalasi_allow
        if(instalasi_allow.includes(instalasi_id)){
            if(data.some(item => parseInt(item.ruangan_id) === ruangan_id)){
                selectedValues.push(ruangan_id);
            } 
          } else {
            // selectedValues.push(ruangan_id); // Jika instalasi_id tidak diizinkan, push ruangan_id
            selectedValues.push(7); // Jika tidak cocok, push 7
        }

        // Set nilai terpilih dalam select
        $("#ruanganSelect").val(selectedValues).trigger("change");
    })
    .catch(error => {
        console.error("Error fetching data: ", error);
    });
    }
 
    function loadData() {
    initDataTable();
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
              cari();
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
              cari();
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
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    const formattedToday = today.toISOString().split('T')[0]; // Format YYYY-MM-DD
    const formattedYesterday = yesterday.toISOString().split('T')[0];

    flatpickr("#dateRangePicker", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [formattedYesterday, formattedToday], // Set default range to yesterday and today
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
              cari();
            },
            error: function (error) {
              console.error('Error deleting data:', error);
              Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data.', 'error');
            }
          });
        }
      });
    }
    $(document).on('click', '.openDialogAdd', function (e) {
        e.preventDefault();

        const pendaftaran_id = $(this).data('id'); // Ambil ID dari elemen yang diklik

        // Buat AJAX request untuk mengambil data tambahan (jika diperlukan)
        $.ajax({
            url: 'backend/GetDetailKeteranganMRS.php',
            type: 'POST',
            data: { pendaftaran_id: pendaftaran_id },
            success: function (response) {
                $('#modalBody').html(response); // Masukkan data ke dalam modal
                $('#myModal').modal('show');   // Tampilkan modal
            },
            error: function () {
                alert('Gagal mengambil data.');
            }
        });
    });

    $(document).on('click', '.editKeterangan', function (e) {
        e.preventDefault();

        const keteranganrespontime_id = $(this).data('id'); // Ambil ID dari elemen yang diklik

        // Buat AJAX request untuk mengambil data tambahan (jika diperlukan)
        $.ajax({
            url: 'backend/UpdateDetailKeteranganMRS.php',
            type: 'POST',
            data: { keteranganrespontime_id: keteranganrespontime_id },
            success: function (response) {
                $('#modalBodyEdit').html(response); // Masukkan data ke dalam modal
                $('#myModalEdit').modal('show');   // Tampilkan modal
            },
            error: function () {
                alert('Gagal mengambil data.');
            }
        });
    });


    function downloadExcel(filters = {}) {
      // Construct the query string from filters
      const params = new URLSearchParams(filters).toString();
      // Redirect to the backend script with filter parameters
      window.location.href = `backend/LoadDataFormLaporanExcelMRS.php?action=export_excel&${params}`;
    }
    
    $(document).on('click', '.deleteKeterangan', function (e) {
        e.preventDefault();

        const keteranganrespontime_id = $(this).data('id'); // Ambil ID dari elemen yang diklik

        console.log("Keterangan " , keteranganrespontime_id);
        Swal.fire({
        title: 'Anda yakin?',
        text: "Ingin Menghapus Data ",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
     
             // Buat AJAX request untuk mengambil data tambahan (jika diperlukan)
        $.ajax({
            url: 'backend/deleteKeteranganMRS.php',
            type: 'POST',
            data: { keteranganrespontime_id: keteranganrespontime_id },
            success: function (response) {
                $('#myModalEdit').modal('hide');   // Tampilkan modal
                cari();
              },
            error: function () {
                alert('Gagal mengambil data.');
            }
        });


        }
      });
     
    });

    
    $(document).ready(function () {
      // Load data into DataTable
      // loadData();
      cari();
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