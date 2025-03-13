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
                <div class="row">
                  <div class="col-5">
                    <div class="form-group daterange-container" style="margin-left:25px;">
                      <label for="date_range">Periode:</label>
                      <input type="text" id="date_range" name="date_range" class="form-control"
                        placeholder="Masukkan Periode">


                      <div class="form-check form-switch" style="margin-left:25px;">
                        <input class="form-check-input" type="checkbox" role="switch" id="toggle_mode" checked />
                        <label class="form-check-label" for="toggle_mode" id="toggle_label">
                          Filter Periode Aktif
                        </label>
                      </div>
                    </div>



                    <div class="form-group" style="margin-left:25px;">
                      <label for="poli_tujuan">Poli Tujuan:</label>
                      <input type="text" id="poli_tujuan" name="poli_tujuan" class="form-control"
                        placeholder="Masukkan Nama Poli yang dituju">
                    </div>
                  </div>


                  <div class="col-5" style="margin-top: 30px;">
                    <div class="form-group" style="margin-left:50px;">
                      <label for="no_rekam_medik">No Rekam Medik:</label>
                      <input type="text" id="no_rekam_medik" name="no_rekam_medik" class="form-control"
                        placeholder="Masukkan No Rekam Medik">
                    </div>
                    <div class="form-group" style="margin-left:50px;">
                      <label for="status">Status:</label>
                      <select id="status" name="status" class="form-control">
                        <option value="">--Silahkan pilih Status--</option>

                        <option value="Terduga">Terduga</option>
                        <option value="Bukan Terduga">Tidak Terduga</option>
                      </select>
                    </div>

                  </div>
                </div>

                <div class="row">
                  <div class="col-12 text-left" style="margin-left:25px;">
                    <button type="button" class="btn btn-primary" id="searchButton">Search</button>
                    <!-- <button id="btnPrint" onclick="printData()">Print</button> -->
                    <!-- <button id="btnDownloadExcel" onclick="downloadExcel()">Download Excel</button> -->
                    <!-- <button id="btnDownloadPDF" onclick="downloadPDF()">Download PDF</button> -->
                  </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>No.</th>
                        <th>Ruangan</th>
                        <th>No. Rekam Medik / Nama</th>
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
                  data: null, render: function (data, type, row) {
                    console.log(data,type,"no_rekam_medik", row);
                    const no_rekam_medik = row.no_rekam_medik;
                    const nama_pasien = (row.nama_pasien === '') ? '-' : row.nama_pasien;
                    return `${no_rekam_medik} / ${nama_pasien}`;
                    // return renderButtons(row.id, sessionData);
                  }
                },
                {
                    data: null,
                    render: function (data,type,row) {
                      const tgl_adviskrs = (row.tgl_adviskrs === ''||row.tgl_adviskrs ===null) ? '-' : row.tgl_adviskrs;
                      const pegawai_adviskrs = (row.pegawai_adviskrs === ''||row.pegawai_adviskrs ===null) ? '-' : row.pegawai_adviskrs;
                      if( (row.tgl_adviskrs === ''||row.tgl_adviskrs ===null) &&(row.pegawai_adviskrs === ''||row.pegawai_adviskrs ===null) ){
                        return renderButtons(row.pasienadmisi_id);
                      }else{
                        return `${tgl_adviskrs} / ${pegawai_adviskrs}`;
                      }
                    }
                },
                {
                    data: null,
                    render: function (data,type,row) {
                      const tgl_skfarmasi = (row.tgl_skfarmasi === ''||row.tgl_skfarmasi ===null) ? '-' : row.tgl_skfarmasi;
                      const  pegawai_skfarmasi= (row.pegawai_skfarmasi === ''||row.pegawai_skfarmasi ===null) ? '-' : row.pegawai_skfarmasi;
                      if( (tgl_skfarmasi==='-') && (pegawai_skfarmasi==='-') ){
                        return renderButtonsk(row.pasienadmisi_id);
                      }else{
                        return `${tgl_skfarmasi} / ${pegawai_skfarmasi}`;
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
                      if (Array.isArray(row.loopKeterangan) && row.loopKeterangan.length > 0) {
                        let result = ''; // Variabel untuk menyimpan hasil looping
                        row.loopKeterangan.forEach(item => {
                            result += (item.nama_keterangan || 'No Data') + ', ';
                        });
                        return result; // Menghapus koma dan spasi terakhir    
                        // return `<a href="#" class="openDialog" data-id="${row.pasienadmisi_id}">Tambah Keterangan</a>`;

                      } else {
                        return `<a href="#" class="openDialogAdd" data-id="${row.pasienadmisi_id}">Tambah Keterangan</a>`;
                      }
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
    function simpanKeterangan() {
        let keterangan = $("#keterangan").val(); // Ambil nilai dari textarea
        let pasienadmisi_id = $("#pasienadmisi_id").val(); // Ambil ID pasien

        if (!keterangan.trim()) {
            Swal.fire('Error', 'Data Keterangan tidak boleh kosong!', 'error');
        }else{
          $.ajax({
              url: "backend/simpanKeterangan.php",
              type: "POST",
              data: {
                  pasienadmisi_id: pasienadmisi_id,
                  keterangan: keterangan
              },
              success: function (response) {
                  let res = JSON.parse(response);
                  if (res.status === "success") {
                      alert("Keterangan berhasil disimpan!");
                      $("#keteranganModal").modal("hide"); // Tutup modal
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

    function renderButtons(id) {
      let buttons = '';
      buttons += `<button type="button" class="btn btn-success btn-sm" onclick="advisKrs(${id});">Masukkan Jam Advis KRS</button>`;
      return buttons;
    }
    function renderButtonsk(id) {
      let buttons = '';
      buttons += `<button type="button" class="btn btn-success btn-sm" onclick="skFarmasi(${id});">Masukkan Jam SK Farmasi</button>`;
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
    $(document).on('click', '.openDialogAdd', function (e) {
        e.preventDefault();

        const pasienadmisiId = $(this).data('id'); // Ambil ID dari elemen yang diklik

        // Buat AJAX request untuk mengambil data tambahan (jika diperlukan)
        $.ajax({
            url: 'backend/GetDetailKeterangan.php',
            type: 'POST',
            data: { pasienadmisi_id: pasienadmisiId },
            success: function (response) {
                $('#modalBody').html(response); // Masukkan data ke dalam modal
                $('#myModal').modal('show');   // Tampilkan modal
            },
            error: function () {
                alert('Gagal mengambil data.');
            }
        });
    });

    $(document).ready(function () {
      // Load data into DataTable
      loadData();
    });

    // Initialize the date range picker
    let isRangeMode = true; // Default mode adalah range

    // Fungsi untuk menginisialisasi Flatpickr
    function initFlatpickr() {
      flatpickr("#date_range", {
        mode: isRangeMode ? "range" : "single", // Mode bergantung pada toggle
        dateFormat: "Y-m-d", // Format tanggal
        onChange: function (selectedDates, dateStr) {
          console.log(`Tanggal yang dipilih: ${dateStr}`);
        },
      });
    }

    // Inisialisasi Flatpickr
    initFlatpickr();

    // Event listener untuk menangani perubahan toggle
    document.getElementById("toggle_mode").addEventListener("change", function () {
      isRangeMode = this.checked; // Cek status toggle (checked/unchecked)
      flatpickr("#date_range").destroy(); // Hancurkan instance Flatpickr saat ini
      initFlatpickr(); // Re-inisialisasi dengan mode baru

      // Ubah teks label sesuai dengan mode
      document.getElementById("toggle_label").textContent = isRangeMode
        ? "Filter Periode Aktif"
        : "Filter Harian Aktif";
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