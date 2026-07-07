<?php
error_reporting(0);
session_start();
if (empty($_SESSION['username'])) {
  header('Location:index.php');
}
include("include/db_config.php");

/* -----delete  record------*/
if (isset($_GET['del'])) {
  $id = $_GET['del'];
  $query  = "DELETE FROM user WHERE id ='$id' ";
  $result = mysqli_query($conn, $query);
  if ($result) {
    $msg = "<p style='color:green';>Record has been deleted successfully</p>";
  } else {
    $msg = "There is some problem in delete record";
  }
}

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Admin- View Users</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="icon" href="<?=$base_url;?>assets/images/logo-fav.png" type="image/png">

  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
<style>
    /* Premium Modern Table Styling */
    .box {
        border-top: none !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important;
        overflow: hidden !important;
        border: 1px solid #edf2f7 !important;
    }
    .box-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 15px 20px !important;
    }
    .box-body {
        padding: 0 !important;
        background: #fff !important;
    }
    .table {
        margin-bottom: 0 !important;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        font-size: 13px !important;
    }
    .table th, .table td {
        border: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 14px 16px !important;
        vertical-align: middle !important;
        word-break: break-word;
        color: #334155 !important;
    }
    .table th {
        background-color: #f8fafc !important;
        color: #64748b !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.8px !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .table tbody tr {
        transition: all 0.2s ease-in-out;
    }
    .table tbody tr:hover {
        background-color: #f1f5f9 !important;
    }
    .table tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* Reduce sidebar width and expand main content area */
    .main-sidebar {
        width: 180px !important;
    }
    .content-wrapper, .main-footer {
        margin-left: 180px !important;
        background-color: #f8fafc !important;
    }
    .main-header .logo {
        width: 180px !important;
    }
    .main-header .navbar {
        margin-left: 180px !important;
    }
    @media (max-width: 767px) {
        .content-wrapper, .main-footer, .main-header .navbar {
            margin-left: 0 !important;
        }
    }
</style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <?php include('include/header.php'); ?>
    <?php include('include/side-bar.php'); ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <section class="content-header">
        <P>
          <?PHP if (!empty($msg)) {
            echo $msg;
          }
          if (isset($_GET['id']) && $_GET['id'] == 'Added') {
            echo '<p style="color:green;">New User Profile has been added successfully</p>';
          }
          if (isset($_GET['id']) and $_GET['id'] == 'Update') {
            echo '<p style="color:green;">Record has been updated successfully</p>';
          }
          ?>
        </P>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">User List</h3>
                <div class="box-tools pull-right">
                  <a href="add-user.php" class="btn btn-primary">Add User</a>
                </div>
              </div>
              <div class="box-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>SR.NO</th>
                      <th style="width:100px;">USER NAME</th>
                      <!-- <th>BLOG TITLE</th> -->
                      <!-- <th>URL SLUG</th> -->
                      <th>SSF USER</th>
                      <th>DESCRIPTION</th>
                      <th>IMAGE</th>
                      <!-- <th>THUMB</th> -->
                      <th>COUPON PERMISSION</th>
                      <th>Status</th>
                      <th>Referral Link</th>
                      <th>CREATED DATE</th>
                      <th width="10%">ACTION</th>
                    </tr>

                  </thead>
                  <tbody>
                    <?php
                    $query = "select * from  user ORDER BY id desc";
                    $result_item = mysqli_query($conn, $query);
                    $count = 1;
                    while ($info_item = mysqli_fetch_object($result_item)) {  ?>
                      <tr style="color:<?php if (!empty($color)) {
                                          echo $color;
                                        } ?>">
                        <td><?php echo $count++ ?></td>
                        <td><?php echo $info_item->user_name; ?></td>
                        <!-- <td><?php echo $info_item->blog_title; ?></td> -->
                        <!-- <td><?php echo $info_item->url; ?></td> -->
                        <td>
                          <?php
                          if ($info_item->customer_id > 0) {
                            $cust_q = "SELECT full_name FROM tbl_user WHERE id = '$info_item->customer_id'";
                            $cust_res = mysqli_query($conn3, $cust_q);
                            $cust_data = mysqli_fetch_assoc($cust_res);
                            echo ($cust_data['full_name'] ?? 'N/A') . " (ID: " . $info_item->customer_id . ")";
                          } else {
                            echo "N/A";
                          }
                          ?>
                        </td>

                        <td><?php echo substr(strip_tags($info_item->product_description), 0, 50) . '...'; ?></td>
                        <td><img src="../images/user/user/<?php echo rawurlencode($info_item->user_image); ?>" width="60px"></td>
                        <!-- <td><img src="../images/user/user/<?php echo $info_item->thumbnail_image; ?>" width="40px"></td> -->
                        <td><?php echo $info_item->coupon_permission; ?></td>
                        <td><?php echo $info_item->status; ?></td>
                        <td>
                          <?php if($info_item->customer_id > 0): ?>
                            <small><code><?php echo $base_url; ?>?ref=<?php echo $info_item->customer_id; ?></code></small>
                            <button class="btn btn-xs btn-default" onclick="navigator.clipboard.writeText('<?php echo $base_url; ?>?ref=<?php echo $info_item->customer_id; ?>'); alert('Copied!');">Copy</button>
                          <?php else: ?>
                            N/A
                          <?php endif; ?>
                        </td>
                        <td><?php echo $info_item->created_date; ?></td>

                        <td width="12%">
                          <a href="../view-profile.php?id=<?php echo urlencode(base64_encode(($info_item->id * 1234) + 567)) ?>" target="_blank"><i class="fa fa-eye" style="font-size:24px;" title="View Profile"></i></a> |
                          <a href="add-user.php?id=<?php echo $info_item->id ?>"><i class="fa fa-pencil" style="font-size:24px;" title="Edit"></i></a> |
                          <a href="view-user.php?del=<?php echo $info_item->id ?>" onclick="return confirm('Are you sure want to delete record ?');"><i class="fa fa-times" style="font-size:24px;" title="Delete"></i></a>
                        </td>
                      </tr>
                    <?php
                    } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>

    </div>

    <?php include('include/footer.php'); ?>

  </div>
  <!-- ./wrapper -->

  <!-- Model of View Package Details Start -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Student Details</h4>
        </div>
        <div class="modal-body">
          <div id="packageDetails"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="bower_components/jquery/dist/jquery.min.js"></script>
  <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  <script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
  <script src="bower_components/fastclick/lib/fastclick.js"></script>
  <script src="dist/js/adminlte.min.js"></script>
  <script src="dist/js/demo.js"></script>

  <script>
    $(function() {
      $('#example1').DataTable()
      $('#example2').DataTable({
        'paging': true,
        'lengthChange': false,
        'searching': false,
        'ordering': true,
        'info': true,
        'autoWidth': false
      })
    })
  </script>
</body>

</html>




