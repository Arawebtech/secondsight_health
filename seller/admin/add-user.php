<?php
error_reporting(0);
session_start();
if (empty($_SESSION['username'])) {
	header('Location:index.php');
}
include('include/db_config.php');

if (isset($_GET['del_video']) && isset($_GET['id'])) {
    $del_vid = $_GET['del_video'];
    $q_del = mysqli_query($conn, "SELECT thumbnail_image FROM user_youtube_videos WHERE id='$del_vid'");
    if($r_del = mysqli_fetch_assoc($q_del)){
        if(!empty($r_del['thumbnail_image']) && file_exists("../images/user/videos/".$r_del['thumbnail_image'])){
            unlink("../images/user/videos/".$r_del['thumbnail_image']);
        }
    }
    mysqli_query($conn, "DELETE FROM user_youtube_videos WHERE id='$del_vid'");
    exit("<script>window.location.href='add-user.php?id=".$_GET['id']."';</script>");
}

if (isset($_GET['del_product_img']) && isset($_GET['id'])) {
    $del_img = $_GET['del_product_img'];
    $u_id = $_GET['id'];
    $q_u = mysqli_query($conn, "SELECT product_images FROM user WHERE id='$u_id'");
    if($r_u = mysqli_fetch_assoc($q_u)){
        $imgs = explode(",", $r_u['product_images']);
        $new_imgs = [];
        foreach($imgs as $img){
            if(trim($img) != $del_img && trim($img) != ''){
                $new_imgs[] = $img;
            } elseif (trim($img) == $del_img) {
                if(file_exists("../images/user/products/".trim($img))){
                    unlink("../images/user/products/".trim($img));
                }
            }
        }
        $new_imgs_str = implode(",", $new_imgs);
        mysqli_query($conn, "UPDATE user SET product_images='$new_imgs_str' WHERE id='$u_id'");
    }
    exit("<script>window.location.href='add-user.php?id=".$u_id."';</script>");
}

global $conn;
if (isset($_POST['submit']) and $_POST['submit'] == 'Save') {
	$user_image = "";
	$banner_image = "";
	$thumbnail_image = "";

	// User Image
	$appl_univrep1 = $_FILES["user_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = rand(1000, 9999) . "_" . trim($appl_univrep1);
		$file_locu1 = $_FILES["user_image"]["tmp_name"];
		$foldervc1 = "../images/user/user/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$user_image = $appl_univrep1s;
	}

	// Banner Image
	$banner_image_name = $_FILES["banner_image"]["name"];
	if (!empty($banner_image_name)) {
		$banner_image_s = rand(1000, 9999) . "_" . trim($banner_image_name);
		$file_loc_banner = $_FILES["banner_image"]["tmp_name"];
		$folder_banner = "../images/user/user/";
		move_uploaded_file($file_loc_banner, $folder_banner . $banner_image_s);
		$banner_image = $banner_image_s;
	}

	// Thumbnail Image
	$thumbnail_image_name = $_FILES["thumbnail_image"]["name"];
	if (!empty($thumbnail_image_name)) {
		$thumbnail_image_s = rand(1000, 9999) . "_" . trim($thumbnail_image_name);
		$file_loc_thumb = $_FILES["thumbnail_image"]["tmp_name"];
		$folder_thumb = "../images/user/user/";
		move_uploaded_file($file_loc_thumb, $folder_thumb . $thumbnail_image_s);
		$thumbnail_image = $thumbnail_image_s;
	}

	$product_images_str = "";
	if (!empty($_FILES['product_images']['name'][0])) {
		$product_images_arr = [];
		foreach ($_FILES['product_images']['name'] as $key => $val) {
			$file_name = rand(1000, 9999) . "_" . $_FILES['product_images']['name'][$key];
			$file_tmp = $_FILES['product_images']['tmp_name'][$key];
			$folder = "../images/user/products/";
			move_uploaded_file($file_tmp, $folder . $file_name);
			$product_images_arr[] = $file_name;
		}
		$product_images_str = implode(",", $product_images_arr);
	}

	$user_name = addslashes($_POST['user_name']);
	$customer_id = $_POST['customer_id'];
	$website_url = addslashes($_POST['website_url']);
	$product_description = addslashes($_POST['product_description']);
	$coupon_tagline = addslashes($_POST['coupon_tagline']);
	$coupon_code = addslashes($_POST['coupon_code']);
	$buy_link = addslashes($_POST['buy_link']);
	$youtube_link = addslashes($_POST['youtube_link']);
	$facebook_link = addslashes($_POST['facebook_link']);
	$instagram_link = addslashes($_POST['instagram_link']);
	$youtube_social_link = addslashes($_POST['youtube_social_link']);
	$whatsapp_number = addslashes($_POST['whatsapp_number']);
	$coupon_permission = addslashes($_POST['coupon_permission']);

	// New fields
	$blog_title = addslashes($_POST['blog_title']);
	$url = addslashes($_POST['url']);
	$short_name = addslashes($_POST['short_name']);
	$description = addslashes($_POST['description']);
	$alt = addslashes($_POST['alt']);
	$meta_keyword = addslashes($_POST['meta_keyword']);
	$meta_description = addslashes($_POST['meta_description']);
	$blog_schema = addslashes($_POST['blog_schema']);

	$status = $_POST['status'];
	$created_date = date("Y-m-d H:i:s");

	// Full query with 31 columns
	$sql_query = "INSERT INTO user (
		customer_id, blog_name, blog_title, url, short_name, description, banner_image, thumbnail_image, 
		user_image, user_name, website_url, product_images, product_description, coupon_tagline, coupon_code, 
		buy_link, youtube_link, facebook_link, instagram_link, youtube_social_link, whatsapp_number, 
		coupon_permission, alt, meta_keyword, meta_description, blog_schema, status, created_date, created_by, updated_date
	) VALUES (
		'$customer_id', '$user_name', '$blog_title', '$url', '$short_name', '$description', '$banner_image', '$thumbnail_image', 
		'$user_image', '$user_name', '$website_url', '$product_images_str', '$product_description', '$coupon_tagline', '$coupon_code', 
		'$buy_link', '$youtube_link', '$facebook_link', '$instagram_link', '$youtube_social_link', '$whatsapp_number', 
		'$coupon_permission', '$alt', '$meta_keyword', '$meta_description', '$blog_schema', '$status', '$created_date', '', ''
	)";

	$res_query = mysqli_query($conn, $sql_query);

	if ($res_query > 0) {
		$new_user_id = mysqli_insert_id($conn);
		if (isset($_POST['yt_links']) && is_array($_POST['yt_links'])) {
			foreach ($_POST['yt_links'] as $key => $yt_link) {
				if (!empty(trim($yt_link))) {
					$thumb_name = "";
					if (!empty($_FILES['yt_thumbs']['name'][$key])) {
						$thumb_name = rand(1000, 9999) . "_" . $_FILES['yt_thumbs']['name'][$key];
						$tmp_name = $_FILES['yt_thumbs']['tmp_name'][$key];
						move_uploaded_file($tmp_name, "../images/user/videos/" . $thumb_name);
					}
					$yt_link = addslashes($yt_link);
					mysqli_query($conn, "INSERT INTO user_youtube_videos (user_id, youtube_link, thumbnail_image) VALUES ('$new_user_id', '$yt_link', '$thumb_name')");
				}
			}
		}
		exit("<script>window.location.href='view-user.php?id=Added';</script>");
	} else {
		$error = "There is some problem in inserting record: " . mysqli_error($conn);
	}
}



if (isset($_POST['submit']) and $_POST['submit'] == 'Update') {
	$id = $_GET['id'];

	$user_image = $_POST['user_image2'];
	$appl_univrep1 = $_FILES["user_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = rand(1000, 9999) . "_" . trim($appl_univrep1);
		$file_locu1 = $_FILES["user_image"]["tmp_name"];
		$foldervc1 = "../images/user/user/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$user_image = $appl_univrep1s;
	}

	$banner_image = $_POST['banner_image2'];
	$banner_image_name = $_FILES["banner_image"]["name"];
	if (!empty($banner_image_name)) {
		$banner_image_s = rand(1000, 9999) . "_" . trim($banner_image_name);
		$file_loc_banner = $_FILES["banner_image"]["tmp_name"];
		$folder_banner = "../images/user/user/";
		move_uploaded_file($file_loc_banner, $folder_banner . $banner_image_s);
		$banner_image = $banner_image_s;
	}

	$thumbnail_image = $_POST['thumbnail_image2'];
	$thumbnail_image_name = $_FILES["thumbnail_image"]["name"];
	if (!empty($thumbnail_image_name)) {
		$thumbnail_image_s = rand(1000, 9999) . "_" . trim($thumbnail_image_name);
		$file_loc_thumb = $_FILES["thumbnail_image"]["tmp_name"];
		$folder_thumb = "../images/user/user/";
		move_uploaded_file($file_loc_thumb, $folder_thumb . $thumbnail_image_s);
		$thumbnail_image = $thumbnail_image_s;
	}

	$product_images_str = $_POST['product_images2'];
	if (!empty($_FILES['product_images']['name'][0])) {
		$product_images_arr = [];
		if (!empty($product_images_str)) {
			$product_images_arr = explode(",", $product_images_str);
		}
		foreach ($_FILES['product_images']['name'] as $key => $val) {
			if (!empty(trim($val))) {
				$file_name = rand(1000, 9999) . "_" . $_FILES['product_images']['name'][$key];
				$file_tmp = $_FILES['product_images']['tmp_name'][$key];
				$folder = "../images/user/products/";
				move_uploaded_file($file_tmp, $folder . $file_name);
				$product_images_arr[] = $file_name;
			}
		}
		$product_images_str = implode(",", $product_images_arr);
	}

	$user_name = addslashes($_POST['user_name']);
	$customer_id = $_POST['customer_id'];
	$website_url = addslashes($_POST['website_url']);
	$product_description = addslashes($_POST['product_description']);
	$coupon_tagline = addslashes($_POST['coupon_tagline']);
	$coupon_code = addslashes($_POST['coupon_code']);
	$buy_link = addslashes($_POST['buy_link']);
	$youtube_link = addslashes($_POST['youtube_link']);
	$facebook_link = addslashes($_POST['facebook_link']);
	$instagram_link = addslashes($_POST['instagram_link']);
	$youtube_social_link = addslashes($_POST['youtube_social_link']);
	$whatsapp_number = addslashes($_POST['whatsapp_number']);
	$coupon_permission = addslashes($_POST['coupon_permission']);

	// New fields
	$blog_title = addslashes($_POST['blog_title']);
	$url = addslashes($_POST['url']);
	$short_name = addslashes($_POST['short_name']);
	$description = addslashes($_POST['description']);
	$alt = addslashes($_POST['alt']);
	$meta_keyword = addslashes($_POST['meta_keyword']);
	$meta_description = addslashes($_POST['meta_description']);
	$blog_schema = addslashes($_POST['blog_schema']);

	$status = $_POST['status'];
	$updated_date = date("Y-m-d H:i:s");

	$sql = "UPDATE user SET 
		customer_id = '$customer_id', 
		blog_name = '$user_name', 
		blog_title = '$blog_title',
		url = '$url',
		short_name = '$short_name',
		description = '$description',
		banner_image = '$banner_image',
		thumbnail_image = '$thumbnail_image',
		status = '$status', 
		user_image = '$user_image', 
		user_name = '$user_name', 
		website_url = '$website_url', 
		product_images = '$product_images_str', 
		product_description = '$product_description', 
		coupon_tagline = '$coupon_tagline', 
		coupon_code = '$coupon_code', 
		buy_link = '$buy_link', 
		youtube_link = '$youtube_link', 
		facebook_link = '$facebook_link', 
		instagram_link = '$instagram_link', 
		youtube_social_link = '$youtube_social_link', 
		whatsapp_number = '$whatsapp_number', 
		coupon_permission = '$coupon_permission',
		alt = '$alt',
		meta_keyword = '$meta_keyword',
		meta_description = '$meta_description',
		blog_schema = '$blog_schema',
		updated_date = '$updated_date'
		WHERE id = '$id'";

	$resq = mysqli_query($conn, $sql);

	if ($resq) {
		if (isset($_POST['yt_links']) && is_array($_POST['yt_links'])) {
			foreach ($_POST['yt_links'] as $key => $yt_link) {
				if (!empty(trim($yt_link))) {
					$thumb_name = "";
					if (!empty($_FILES['yt_thumbs']['name'][$key])) {
						$thumb_name = rand(1000, 9999) . "_" . $_FILES['yt_thumbs']['name'][$key];
						$tmp_name = $_FILES['yt_thumbs']['tmp_name'][$key];
						move_uploaded_file($tmp_name, "../images/user/videos/" . $thumb_name);
					}
					$yt_link = addslashes($yt_link);
					mysqli_query($conn, "INSERT INTO user_youtube_videos (user_id, youtube_link, thumbnail_image) VALUES ('$id', '$yt_link', '$thumb_name')");
				}
			}
		}
		exit("<script>window.location.href='view-user.php?id=Update';</script>");
	} else {
		$error = "There is some problem in updating record: " . mysqli_error($conn);
	}
}


if (isset($_GET['id'])) {
	$blog_id = isset($_GET['id']) ? $_GET['id'] : '';
	$query = "SELECT * FROM user WHERE id ='$blog_id'";
	$result_blog = mysqli_query($conn, $query);
	$info_blog = mysqli_fetch_object($result_blog);
} else {
	$info_blog = (object) [
		'status' => 'Active',
		'customer_id' => isset($_GET['cust_id']) ? $_GET['cust_id'] : '',
		'user_image' => '',
		'banner_image' => '',
		'thumbnail_image' => '',
		'user_name' => '',
		'website_url' => '',
		'product_images' => '',
		'product_description' => '',
		'coupon_tagline' => '',
		'coupon_code' => '',
		'buy_link' => '',
		'youtube_link' => '',
		'facebook_link' => '',
		'instagram_link' => '',
		'youtube_social_link' => '',
		'whatsapp_number' => '',
		'coupon_permission' => 'No',
		'blog_title' => '',
		'url' => '',
		'short_name' => '',
		'description' => '',
		'alt' => '',
		'meta_keyword' => '',
		'meta_description' => '',
		'blog_schema' => ''
	];
}

$existing_videos = [];
if (isset($_GET['id'])) {
	$vid_id = $_GET['id'];
	$vid_q = mysqli_query($conn, "SELECT * FROM user_youtube_videos WHERE user_id='$vid_id' ORDER BY id ASC");
	while($vrow = mysqli_fetch_object($vid_q)){
		$existing_videos[] = $vrow;
	}
}

?>




<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Add User</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
	<link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="dist/css/AdminLTE.min.css">
	<link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
	<link rel="icon" type="image/png" href="https://secondsightfoundation.com/assets/img/logo-fav.png">

	<link rel="stylesheet"
		href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<style>
	.cke_notification_warning {
		background: #c83939;
		border: 1px solid #902b2b;
		display: none;
	}
</style>

<body class="hold-transition skin-blue sidebar-mini">
	<div class="wrapper">

		<?php include('include/header.php'); ?>
		<?php include('include/side-bar.php'); ?>

		<!-- Content Wrapper. Contains Blog content -->
		<div class="content-wrapper">

			<!-- Main content -->
			<section class="content">
				<div class="row">
					<div class="col-xs-12">
						<span style="color:red;">
							<?php if (!empty($error)) {
								echo $error;
							} ?> </span>
						<div class="box">
							<div class="box-body">
								<div class="box box-primary">
									<div class="box-header with-border">
										<h3 class="box-title"><?php if (isset($_GET['id']))
																	echo 'Update User';
																else
																	echo 'Add User'; ?></h3>
										<?php if (isset($_GET['id'])) { ?>
											<a href="../view-profile.php?id=<?php echo urlencode(base64_encode(($info_blog->id * 1234) + 567)) ?>"
												target="_blank" class="btn btn-sm btn-info pull-right"><i
													class="fa fa-eye"></i> View Profile</a>
										<?php } ?>
									</div>

									<div class="box-body">
										<div class="row">
											<form name="addusersform" Method="POST" id="addusersform"
												enctype="multipart/form-data">
												<div class="col-md-6">
													<div class="form-group">
														<label for="reporttitle">Status : <span style="color:red;"> *
															</span></label>
														<div class="input-group">
															<div class="input-group-addon">
																<i class="fa fa-tasks"></i>
															</div>
															<select name="status" class="form-control" required>
																<option value="Active" <?php if ($info_blog->status == 'Active') {
																							echo 'selected';
																						} ?>>Active</option>
																<option value="De Active" <?php if ($info_blog->status == 'De Active') {
																								echo 'selected';
																							} ?>>De Active</option>
															</select>
														</div>
														<span class="errorMSG" id="msgNAME"></span>
													</div>
												</div>

												<div class="col-md-12">
													<hr>
													<h4>User Details (Select from SSF Database)</h4>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<label for="customer_id">Select User : <span style="color:red;">
																* </span></label>
														<div class="input-group">
															<div class="input-group-addon">
																<i class="fa fa-users"></i>
															</div>
															<select name="customer_id" id="customer_id"
																class="form-control select2" required>
																<option value="">-- Select User --</option>
																<?php
																$customer_query = "SELECT u.id as user_id, u.full_name, tc.coupon_code, uc.id as assignment_id, uc.custom_url, p_c.p_name as coupon_p_name, p_direct.p_name as direct_p_name
																				   FROM tbl_user_coupon uc 
																				   INNER JOIN tbl_user u ON uc.user_id = u.id 
																				   LEFT JOIN tbl_coupon tc ON uc.coupon_id = tc.id
                                                                                   LEFT JOIN tbl_product p_c ON tc.p_id = p_c.p_id
                                                                                   LEFT JOIN tbl_product p_direct ON uc.p_id = p_direct.p_id
																				   ORDER BY u.full_name ASC";
																$customer_result = mysqli_query($conn3, $customer_query);
																while ($customer_row = mysqli_fetch_assoc($customer_result)) {
                                                                    // Generate Target Info for Label
                                                                    $target_info = "Homepage";
                                                                    $f_url = $base_url;
                                                                    if(!empty($customer_row['custom_url'])) {
                                                                        $target_info = "Custom URL";
                                                                        $f_url = $customer_row['custom_url'];
                                                                    } else {
                                                                        $p_name = !empty($customer_row['coupon_p_name']) ? $customer_row['coupon_p_name'] : $customer_row['direct_p_name'];
                                                                        if(!empty($p_name)) {
                                                                            $target_info = $p_name;
                                                                            $slug = strtolower(trim($p_name));
                                                                            $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
                                                                            $slug = preg_replace('/-+/', '-', $slug);
                                                                            $slug = trim($slug, '-');
                                                                            $f_url .= "product/" . $slug;
                                                                        }
                                                                    }
                                                                    $ref_link = $f_url . (strpos($f_url, '?') !== false ? '&' : '?') . "ref=" . $customer_row['user_id'];
                                                                    
                                                                    $coupon_display = !empty($customer_row['coupon_code']) ? "Coupon: " . $customer_row['coupon_code'] : "No Coupon";
																?>
																	<option value="<?php echo $customer_row['user_id']; ?>" 
                                                                            data-name="<?php echo $customer_row['full_name']; ?>" 
                                                                            data-coupon="<?php echo $customer_row['coupon_code']; ?>" 
                                                                            data-url="<?php echo $ref_link; ?>"
                                                                            <?php if ($info_blog->customer_id == $customer_row['user_id']) echo 'selected'; ?>>
																		<?php echo $customer_row['full_name']; ?> (ID: <?php echo $customer_row['user_id']; ?>) 
                                                                        [<?php echo !empty($customer_row['coupon_code']) ? "Coupon: ".$customer_row['coupon_code'] : "No Coupon"; ?>] 
                                                                        [Ref URL: <?php echo $ref_link; ?>]
																	</option>
																<?php } ?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="user_image">User Image <small class="text-muted">(Recommended: 400x400 px, 1:1 Square, Max: 2MB)</small> :</label>
														<div class="input-group">
															<div class="input-group-addon">
																<i class="fa fa-image"></i>
															</div>
															<input type="file" name="user_image" class="form-control">
															<?php if (!empty($info_blog->user_image)) { ?>
																<img width="50px"
																	src="../images/user/user/<?php echo $info_blog->user_image; ?>">
																<input type="hidden" name="user_image2"
																	value="<?php echo $info_blog->user_image; ?>">
															<?php } ?>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="user_name">Name :</label>
														<div class="input-group">
															<div class="input-group-addon">
																<i class="fa fa-user"></i>
															</div>
															<input type='text' name='user_name' id="user_name"
																value="<?php echo $info_blog->user_name; ?>"
																class='form-control'>
														</div>
													</div>
												</div>
												<!-- <div class="col-md-12">
													<div class="form-group">
														<label for="website_url">Website URL :</label>
														<div class="input-group">
															<div class="input-group-addon">
																<i class="fa fa-link"></i>
															</div>
															<input type='url' name='website_url'
																value="<?php echo $info_blog->website_url; ?>"
																class='form-control'>
														</div>
													</div>
												</div> -->

												<!-- <div class="col-md-12">
													<hr>
													<h4>SEO & Branding Details</h4>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="blog_title">Profile / Blog Title :</label>
														<input type='text' name='blog_title' value="<?php echo $info_blog->blog_title; ?>" class='form-control'>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="url">URL Slug :</label>
														<input type='text' name='url' value="<?php echo $info_blog->url; ?>" class='form-control' placeholder="e-g-john-doe">
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="banner_image">Banner Image :</label>
														<input type="file" name="banner_image" class="form-control">
														<?php if (!empty($info_blog->banner_image)) { ?>
															<img width="100px" src="../images/user/user/<?php echo $info_blog->banner_image; ?>" style="margin-top:5px;">
															<input type="hidden" name="banner_image2" value="<?php echo $info_blog->banner_image; ?>">
														<?php } ?>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="thumbnail_image">Thumbnail Image :</label>
														<input type="file" name="thumbnail_image" class="form-control">
														<?php if (!empty($info_blog->thumbnail_image)) { ?>
															<img width="60px" src="../images/user/user/<?php echo $info_blog->thumbnail_image; ?>" style="margin-top:5px;">
															<input type="hidden" name="thumbnail_image2" value="<?php echo $info_blog->thumbnail_image; ?>">
														<?php } ?>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="short_name">Short Name :</label>
														<input type='text' name='short_name' value="<?php echo $info_blog->short_name; ?>" class='form-control'>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="alt">Image Alt Text :</label>
														<input type='text' name='alt' value="<?php echo $info_blog->alt; ?>" class='form-control'>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<label for="description">Short Description :</label>
														<textarea name="description" class="form-control" rows="2"><?php echo $info_blog->description; ?></textarea>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="meta_keyword">Meta Keywords :</label>
														<input type='text' name='meta_keyword' value="<?php echo $info_blog->meta_keyword; ?>" class='form-control'>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="meta_description">Meta Description :</label>
														<input type='text' name='meta_description' value="<?php echo $info_blog->meta_description; ?>" class='form-control'>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<label for="blog_schema">Schema (JSON-LD) :</label>
														<textarea name="blog_schema" class="form-control" rows="3"><?php echo $info_blog->blog_schema; ?></textarea>
													</div>
												</div> -->


												<div class="col-md-12">
													<hr>
													<h4>Product Details</h4>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<label for="product_images">Our Products (Multiple Images) <small class="text-muted">(Recommended: 800x1000 px, Portrait, Max: 2MB)</small>
															:</label>
														<div class="input-group">
															<div class="input-group-addon">
																<i class="fa fa-images"></i>
															</div>
															<input type="file" name="product_images[]"
																class="form-control" multiple>
															<input type="hidden" name="product_images2"
																value="<?php echo $info_blog->product_images; ?>">
														</div>
														<?php if (!empty($info_blog->product_images)) {
															$imgs = explode(",", $info_blog->product_images);
															echo '<div class="row" style="margin-top:15px;">';
															foreach ($imgs as $img) {
																if(trim($img) == '') continue;
															?>
																<div class="col-md-3" style="margin-bottom:15px;">
																	<div style="border:1px solid #ddd; padding:10px; border-radius:5px; background:#f9f9f9; text-align:center;">
																		<img src="../images/user/products/<?php echo trim($img); ?>" style="width:100%; height:120px; object-fit:cover; border-radius:4px; margin-bottom:10px;">
																		<a href="add-user.php?id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&del_product_img=<?php echo trim($img); ?>" class="btn btn-danger btn-xs btn-block" onclick="return confirm('Are you sure you want to delete this product image?');"><i class="fa fa-trash"></i> Remove Image</a>
																	</div>
																</div>
															<?php }
															echo '</div>';
														} ?>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<label for="product_description">About Product Description
															:</label>
														<textarea class="form-control" id="product_description"
															name='product_description'><?php echo $info_blog->product_description; ?></textarea>
													</div>
												</div>

												<div class="col-md-12">
													<hr>
													<h4>Coupon, Referral & Video</h4>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="coupon_permission">Coupon Permission :</label>
														<select name="coupon_permission" class="form-control">
															<option value="No" <?php if ($info_blog->coupon_permission == 'No')
																					echo 'selected'; ?>>No</option>
															<option value="Yes" <?php if ($info_blog->coupon_permission == 'Yes')
																					echo 'selected'; ?>>Yes</option>
														</select>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="coupon_tagline">Coupon Tagline :</label>
														<input type='text' name='coupon_tagline'
															value="<?php echo $info_blog->coupon_tagline; ?>"
															class='form-control'>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="coupon_code">Coupon Code :</label>
														<input type='text' name='coupon_code' id="coupon_code"
															value="<?php echo $info_blog->coupon_code; ?>"
															class='form-control'>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="buy_link">Buy Link :</label>
														<input type='url' name='buy_link' id="buy_link"
															value="<?php echo $info_blog->buy_link; ?>"
															class='form-control'>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<label>YouTube Videos :</label>
														<!-- Existing Videos -->
														<?php if(!empty($existing_videos)){ ?>
															<div class="row" style="margin-bottom:15px;">
																<?php foreach($existing_videos as $vid){ ?>
																	<div class="col-md-3" style="margin-bottom:15px;">
																		<div style="border:1px solid #ddd; padding:10px; border-radius:5px; background:#f9f9f9;">
																			<img src="../images/user/videos/<?php echo $vid->thumbnail_image; ?>" style="width:100%; height:120px; object-fit:cover; border-radius:4px; margin-bottom:10px;">
																			<input type="text" value="<?php echo $vid->youtube_link; ?>" class="form-control input-sm" readonly style="margin-bottom:10px;">
																			<a href="add-user.php?id=<?php echo $_GET['id']; ?>&del_video=<?php echo $vid->id; ?>" class="btn btn-danger btn-xs btn-block" onclick="return confirm('Are you sure you want to delete this video?');"><i class="fa fa-trash"></i> Remove Video</a>
																		</div>
																	</div>
																<?php } ?>
															</div>
														<?php } ?>

														<!-- New Videos -->
														<div id="yt-videos-wrapper">
															<div class="row yt-video-row" style="margin-bottom: 10px;">
																<div class="col-md-6">
																	<label>YouTube Link</label>
																	<div class="input-group">
																		<div class="input-group-addon"><i class="fa fa-youtube"></i></div>
																		<input type="url" name="yt_links[]" class="form-control" placeholder="https://youtube.com/...">
																	</div>
																</div>
																<div class="col-md-5">
																	<label>Thumbnail Image <small class="text-muted">(Recommended: 1280x720 px, 16:9, Max: 2MB)</small></label>
																	<input type="file" name="yt_thumbs[]" class="form-control" accept="image/*">
																</div>
																<div class="col-md-1" style="padding-top: 25px;">
																	<button type="button" class="btn btn-success btn-add-yt"><i class="fa fa-plus"></i></button>
																</div>
															</div>
														</div>
													</div>
												</div>

												<div class="col-md-12">
													<hr>
													<h4>Social Links</h4>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="facebook_link">Facebook Link :</label>
														<input type='url' name='facebook_link'
															value="<?php echo $info_blog->facebook_link; ?>"
															class='form-control'>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="instagram_link">Instagram Link :</label>
														<input type='url' name='instagram_link'
															value="<?php echo $info_blog->instagram_link; ?>"
															class='form-control'>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="youtube_social_link">YouTube Social Link :</label>
														<input type='url' name='youtube_social_link'
															value="<?php echo $info_blog->youtube_social_link; ?>"
															class='form-control'>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="whatsapp_number">WhatsApp Number :</label>
														<input type='text' name='whatsapp_number'
															value="<?php echo $info_blog->whatsapp_number; ?>"
															class='form-control'>
													</div>
												</div>

												<?php if (!empty($info_blog->customer_id)): ?>
													<div class="col-md-12">
														<hr>
														<h4 class="text-primary"><i class="fa fa-link"></i> Assigned Referral Links & Coupons</h4>
														<div class="table-responsive">
															<table class="table table-bordered table-striped bg-gray">
																<thead>
																	<tr class="bg-blue">
																		<th>Coupon Code</th>
																		<th>Product / Targeted Page</th>
																		<th>Referral Link</th>
																		<th>Comm. %</th>
																	</tr>
																</thead>
																<tbody>
																	<?php
																	$c_id = $info_blog->customer_id;
																	$q_links = "SELECT uc.*, c.coupon_code, p_c.p_name as coupon_p_name, p_direct.p_name as direct_p_name
                                                                                FROM tbl_user_coupon uc 
                                                                                LEFT JOIN tbl_coupon c ON uc.coupon_id = c.id 
                                                                                LEFT JOIN tbl_product p_c ON c.p_id = p_c.p_id
                                                                                LEFT JOIN tbl_product p_direct ON uc.p_id = p_direct.p_id
                                                                                WHERE uc.user_id = '$c_id'";
																	$r_links = mysqli_query($conn3, $q_links);
																	if (mysqli_num_rows($r_links) > 0) {
																		while ($row_l = mysqli_fetch_assoc($r_links)) {
																			// URL Logic
																			$f_url = $base_url;
																			if (!empty($row_l['custom_url'])) {
																				$f_url = $row_l['custom_url'];
																			} else {
																				$p_name = !empty($row_l['coupon_p_name']) ? $row_l['coupon_p_name'] : $row_l['direct_p_name'];
																				if (!empty($p_name)) {
																					$slug = strtolower(trim($p_name));
																					$slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
																					$slug = preg_replace('/-+/', '-', $slug);
																					$slug = trim($slug, '-');
																					$f_url .= "product/" . $slug;
																				}
																			}
																			$ref_link = $f_url . (strpos($f_url, '?') !== false ? '&' : '?') . "ref=" . $c_id;
																	?>
																			<tr>
																				<td><span class="label label-primary" style="font-size:14px;"><?php echo $row_l['coupon_code'] ?: 'N/A'; ?></span></td>
																				<td><?php echo $row_l['coupon_p_name'] ?: ($row_l['direct_p_name'] ?: 'Homepage / General'); ?></td>
																				<td>
																					<div class="input-group input-group-sm">
																						<input type="text" class="form-control" value="<?php echo $ref_link; ?>" id="ref_<?php echo $row_l['id']; ?>" readonly>
																						<span class="input-group-btn">
																							<button type="button" class="btn btn-info btn-flat" onclick="navigator.clipboard.writeText('<?php echo $ref_link; ?>'); alert('Link Copied!');">Copy</button>
																						</span>
																					</div>
																				</td>
																				<td><strong><?php echo $row_l['percentage']; ?>%</strong></td>
																			</tr>
																		<?php }
																	} else { ?>
																		<tr>
																			<td colspan="4" class="text-center">No assignments found.</td>
																		</tr>
																	<?php } ?>
																</tbody>
															</table>
														</div>
													</div>
												<?php endif; ?>

												<div class="box-footer">
													<input type="submit" name="submit" value="<?php if (isset($_GET['id']))
																									echo 'Update';
																								else
																									echo 'Save'; ?>" class="btn btn-primary pull-right">
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
			</section>
		</div>
		<?php include('include/footer.php'); ?>
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
		$(document).ready(function() {
			$('#customer_id').on('change', function() {
				var cust_id = $(this).val();
				if (cust_id) {
					// Set name and coupon code directly from selected option's data attributes
					var selected_option = $(this).find('option:selected');
					var selected_name = selected_option.data('name');
					var selected_coupon = selected_option.data('coupon');
					var selected_url = selected_option.data('url');

					if (selected_name) {
						$('#user_name').val(selected_name);
					}
					if (selected_coupon) {
						$('#coupon_code').val(selected_coupon);
					}
					if (selected_url) {
						$('#buy_link').val(selected_url);
					}

					$.ajax({
						url: 'get_user_data_ajax.php',
						type: 'GET',
						data: {
							cust_id: cust_id
						},
						dataType: 'json',
						success: function(data) {
							if (data) {
								// Name is now handled by dropdown selection for better control
								// if ($('#user_name').val() == '') {
								// 	$('#user_name').val(data.name);
								// }
								// Removing the automatic filling from AJAX result to favor the dropdown selection for accuracy
								// $('#coupon_code').val(data.coupon_code);
							}
						}
					});
				}
			});

			// Trigger change if customer_id is pre-selected (e.g. from URL)
			if ($('#customer_id').val() != '') {
				$('#customer_id').trigger('change');
			}

			// Add multiple YouTube Videos
			$(document).on('click', '.btn-add-yt', function(){
				var html = '<div class="row yt-video-row" style="margin-bottom: 10px; margin-top:10px;">' +
								'<div class="col-md-6">' +
									'<div class="input-group">' +
										'<div class="input-group-addon"><i class="fa fa-youtube"></i></div>' +
										'<input type="url" name="yt_links[]" class="form-control" placeholder="https://youtube.com/...">' +
									'</div>' +
								'</div>' +
								'<div class="col-md-5">' +
									'<label>Thumbnail Image <small class="text-muted">(Recommended: 1280x720 px, 16:9, Max: 2MB)</small></label>' +
									'<input type="file" name="yt_thumbs[]" class="form-control" accept="image/*">' +
								'</div>' +
								'<div class="col-md-1">' +
									'<button type="button" class="btn btn-danger btn-remove-yt"><i class="fa fa-minus"></i></button>' +
								'</div>' +
							'</div>';
				$('#yt-videos-wrapper').append(html);
			});

			$(document).on('click', '.btn-remove-yt', function(){
				$(this).closest('.yt-video-row').remove();
			});
		});
	</script>

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

	<script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
	<script>
		CKEDITOR.replace('product_description');
	</script>
</body>

</html>