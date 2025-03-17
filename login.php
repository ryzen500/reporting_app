<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style type="text/css">
    .login-page {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      width: 360px;
      margin: 20px;
    }
    .card {
      border-radius: 10px;
    }
    .card-header {
      border-bottom: none;
    }
    .login-box-msg {
      margin: 0;
      padding: 0 20px 20px;
      text-align: center;
    }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <div class="p-5">
        <div class="text-center text-wrap">
          <img src="./dist/img/logo-back-preview.png" style="height:150px;">
        </div>
      </div>
      <div class="text-center">
        <p class="h4 mt-3 mb-4" style="font-weight: bold; font-family: sans-serif;">
RS William Booth Surabaya 
        </p>
      </div>
    </div>
    <div class="card-body">
      <p class="h4  login-box-msg" style="font-weight: bold; font-family: sans-serif;">RSWB Surabaya</p>

      <p class="login-box-msg">Sistem Reporting William Booth</p>
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
          <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <form action="./backend/loginProses.php" method="post">
        <div class="input-group mb-3">
          <input type="text" name="username" class="form-control" id="username" placeholder="Username" required onchange="loadDropdownData()">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        
        <!-- Dropdown Input -->
        <div class="input-group mb-3">
          <select id="dynamic-dropdown" name="ruangan_id" class="form-control">
            <option value="">Pilih Ruangan</option>
          </select>
        </div>
        
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <!-- /.social-auth-links -->
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<script>
function loadDropdownData() {
    // Get the username value from the input field
    var username = $('#username').val();
    
    console.log("Username ", username);
    // Only proceed if the username is not empty
    if (username.trim() !== "") {
        $.ajax({
            url: 'backend/DropdownLoginSIMRS.php', // Backend PHP script URL
            type: 'GET',
            data: { username: username },  // Send the username as part of the request
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    // Empty the dropdown before appending new data
                    $('#dynamic-dropdown').empty();
                    $('#dynamic-dropdown').append('<option value="">Pilih Ruangan</option>'); // Default option
                    
                    // Loop through the returned data and populate the dropdown
                    data.options.forEach(function(option) {
                        $('#dynamic-dropdown').append('<option value="' + option.ruangan_id + '">' + option.ruangan_nama + '</option>');
                    });
                } else {
                    console.log('Error: ' + data.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error: ' + error);
            }
        });
    } else {
        // If username is empty, clear the dropdown
        $('#dynamic-dropdown').empty();
        $('#dynamic-dropdown').append('<option value="">Pilih Ruangan</option>');
    }
}
</script>

</body>
</html>
