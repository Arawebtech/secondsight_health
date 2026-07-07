<?php
error_reporting(0);
include('admin/include/db_config.php');

$id_param = isset($_GET['id']) ? $_GET['id'] : '';
$id = 0;
if (!empty($id_param)) {
    // Decrypt the ID: (Decoded - 567) / 1234
    $decoded = base64_decode($id_param);
    if (is_numeric($decoded)) {
        $id = ($decoded - 567) / 1234;
    }
}

if ($id > 0) {
    $query = "SELECT * FROM user WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $seller = mysqli_fetch_object($result);

    // Fetch SSF Customer details if linked
    if ($seller && $seller->customer_id > 0) {
        $cust_q = "SELECT * FROM tbl_user WHERE id = '$seller->customer_id'";
        $cust_res = mysqli_query($conn3, $cust_q);
        $customer = mysqli_fetch_object($cust_res);
    }
}

if (!$seller) {
    echo "<h2 style='text-align:center; margin-top:50px;'>Seller Profile Not Found.</h2>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $seller->blog_title ?: $seller->user_name; ?> - Profile</title>
    <meta name="keywords" content="<?php echo $seller->meta_keyword; ?>">
    <meta name="description" content="<?php echo $seller->meta_description; ?>">
    <link rel="icon" type="image/png" href="https://secondsightfoundation.com/assets/img/logo-fav.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        /* ================= GLOBAL ================= */

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
        }

        a {
            text-decoration: none;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: auto;
        }

        /* ================= HEADER ================= */

        .header {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #000;
            padding: 15px 20px;
            text-align: center;
        }


        .header1 {
            /* background: ; */
            color: #000;
            padding: 40px 20px;
            text-align: center;
        }


        .site-logo {
            max-width: 220px;
            height: auto;
            margin-bottom: 10px;
        }

        .profile-img {
            width: 180px;
            height: 200px;
            border-radius: 15px;
            border: 5px solid white;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .vendor-name {
            font-size: 28px;
            font-weight: bold;
        }

        .vendor-url {
            font-size: 16px;
            margin-top: 5px;
        }

        /* ================= SECTION ================= */

        .section {
            background: white;
            margin-top: 20px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            margin-bottom: 15px;
            color: #333;
        }

        .description {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
        }

        /* ================= PRODUCT SLIDER ================= */
        .swiper {
            width: 100%;
            padding: 10px 0 40px 0;
        }

        .product-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 10px;
            text-align: center;
        }

        .product-card img {
            width: 100%;
            aspect-ratio: 4 / 5;
            object-fit: cover;
            object-position: center;
            border-radius: 5px;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: #ffc107;
            background: rgba(0, 0, 0, 0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 18px;
            font-weight: bold;
        }

        .swiper-pagination-bullet-active {
            background: #ffc107;
        }

        /* ================= VIDEO ================= */

        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }

        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 10px;
        }

        /* ================= COUPON ================= */

        .coupon-box {
            text-align: center;
        }

        .coupon-code {
            font-size: 32px;
            font-weight: bold;
            color: #ffc107;
            margin: 10px 0;
            letter-spacing: 2px;
            cursor: pointer;
            display: inline-block;
            padding: 5px 15px;
            border: 2px dashed #ffc107;
            border-radius: 5px;
            transition: 0.3s;
        }

        .coupon-code:hover {
            background: rgba(255, 193, 7, 0.1);
        }

        .copy-status {
            font-size: 14px;
            color: #28a745;
            display: block;
            margin-top: 5px;
            height: 20px;
            font-weight: bold;
        }

        .buy-btn {
            display: inline-block;
            background: #ffc107;
            color: black;
            padding: 14px 30px;
            border-radius: 6px;
            font-size: 16px;
            margin-top: 10px;
            transition: 0.3s;
            font-weight: bold;
        }

        .buy-btn:hover {
            background: #e0a800;
        }

        /* ================= SOCIAL ================= */

        .social-links {
            text-align: center;
        }

        .social-links a {
            margin: 0 10px;
            font-size: 22px;
            color: #ffc107;
            display: inline-block;
            width: 45px;
            height: 45px;
            line-height: 45px;
            border-radius: 50%;
            border: 2px solid #ffc107;
            transition: 0.3s;
        }

        .social-links a:hover {
            background: #ffc107;
            color: black;
        }

        /* ================= MOBILE ================= */

        @media (max-width: 600px) {

            .vendor-name {
                font-size: 22px;
            }

            .coupon-code {
                font-size: 26px;
            }

        }



        /* ================= FOOTER ================= */

        .footer {
            background: #000;
            color: #fff;
            margin-top: 30px;
            padding: 30px 20px;
            text-align: center;
        }

        .footer h3 {
            margin-bottom: 10px;
            color: #ffc107;
        }

        .footer p {
            font-size: 14px;
            line-height: 1.6;
            margin: 5px 0;
        }

        .copyright {
            margin-top: 15px;
            font-size: 13px;
            opacity: 0.8;
        }
    </style>

</head>

<body>

    <!-- HEADER / PROFILE -->

    <div class="header">
        <!-- Logo -->
        <div class="logo-wrapper">
            <img src="https://secondsightfoundation.in/assets/images/header2.png" alt="Company Logo" class="site-logo">
        </div>
    </div>




    <div class="header1">
        <img src="images/user/user/<?php echo rawurlencode($seller->user_image); ?>" class="profile-img">
        <div class="vendor-name">
            <?php echo $seller->blog_title ?: $seller->user_name; ?>
        </div>
        <div class="vendor-url">
            <?php echo $seller->website_url; ?>
        </div>
        <p style="max-width: 800px; margin: 10px auto;"><?php echo $seller->description; ?></p>
    </div>


    <div class="container">

        <!-- PRODUCT SLIDER -->

        <div class="section">

            <h2>Our My Picture</h2>

            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php
                    if (!empty($seller->product_images)) {
                        $imgs = explode(",", $seller->product_images);
                        foreach ($imgs as $img) {
                            if (trim($img) != '') {
                                echo '<div class="swiper-slide">';
                                echo '<div class="product-card">';
                                echo '<img src="images/user/products/' . rawurlencode(trim($img)) . '" alt="Product">';
                                echo '</div></div>';
                            }
                        }
                    } else {
                        echo '<div class="swiper-slide"><div class="product-card"><img src="images/slider1.jpg" alt="Default Product"></div></div>';
                    }
                    ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

        </div>

        <!-- DESCRIPTION -->

        <div class="section">

            <h2>Bio Me</h2>

            <div class="description">
                <?php echo $seller->product_description; ?>
            </div>

        </div>


        <div class="section coupon-box">



            <?php if ($seller->coupon_permission == 'Yes') { ?>
                <h2>Special Offer Coupon</h2>
                <div class="coupon-code" id="couponCode" onclick="copyToClipboard('<?php echo $seller->coupon_code; ?>')">
                    <?php echo $seller->coupon_code; ?>
                </div>
                <div id="copyStatus" class="copy-status">Click to Copy</div>
                <p><?php echo $seller->coupon_tagline; ?></p>
                <!--<a href="<?php echo $seller->buy_link; ?>" class="buy-btn">-->
                <!--    Buy Product Now-->
                <!--</a>-->
            <?php } else { ?>
                <!--<p>No active Coupons available at this time.</p>-->
            <?php } ?>
            <a href="<?php echo $seller->buy_link; ?>" class="buy-btn">
                Buy Product Now
            </a>

        </div>
        <!-- VIDEO SECTION -->
        <div class="section">
            <h2>Watch My Video </h2>
            <?php
            $v_query = "SELECT * FROM user_youtube_videos WHERE user_id = '$id' ORDER BY id ASC";
            $v_res = mysqli_query($conn, $v_query);
            $has_video = false;

            if (mysqli_num_rows($v_res) > 0) {
                echo '<div class="swiper videoSwiper" style="padding: 10px 0 40px 0;">';
                echo '<div class="swiper-wrapper">';
                while ($vid = mysqli_fetch_object($v_res)) {
                    $has_video = true;
                    $thumb = !empty($vid->thumbnail_image) ? "images/user/videos/" . rawurlencode($vid->thumbnail_image) : "images/slider1.jpg";
                    echo '<div class="swiper-slide">';
                    echo '<a href="' . $vid->youtube_link . '" target="_blank" style="text-decoration:none; display:block; transition: transform 0.3s;" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">';
                    echo '<div class="product-card" style="position:relative; padding-bottom:15px;">';
                    echo '<div style="position:relative;">';
                    echo '<img src="' . $thumb . '" style="width:100%; aspect-ratio:4/5; object-fit:cover; border-radius:5px;">';
                    echo '<div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:rgba(255,0,0,0.8); color:white; width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22px; box-shadow:0 4px 10px rgba(0,0,0,0.3);"><i class="fa fa-play" style="margin-left:4px;"></i></div>';
                    echo '</div>';
                    echo '<p style="text-align:center; color:#333; margin-top:12px; margin-bottom:0; font-weight:bold;">Watch Video</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
                echo '</div>';
                echo '<div class="swiper-pagination video-pagination"></div>';
                echo '<div class="swiper-button-next video-next"></div>';
                echo '<div class="swiper-button-prev video-prev"></div>';
                echo '</div>';
            }

            if (!$has_video && !empty($seller->youtube_link)) {
                // Fallback to old single video
                echo '<div class="video-wrapper">';
                $video_url = str_replace("watch?v=", "embed/", $seller->youtube_link);
                $video_url = str_replace("youtu.be/", "youtube.com/embed/", $video_url);
                echo '<iframe src="' . $video_url . '" frameborder="0" allowfullscreen></iframe>';
                echo '</div>';
            } elseif (!$has_video) {
                echo '<p style="text-align:center; color:#777;">No videos available.</p>';
            }
            ?>
        </div>

        <?php if ($customer) { ?>
            <!-- CONTACT INFORMATION (FROM SSF) -->
            <div class="section contact-info">
                <h2>Contact Information</h2>
                <div class="description">
                    <p><strong><i class="fa fa-phone"></i> Phone:</strong> <?php echo $customer->phone; ?></p>
                    <p><strong><i class="fa fa-envelope"></i> Email:</strong> <?php echo $customer->email; ?></p>
                    <?php if (!empty($customer->cust_address)) { ?>
                        <p><strong><i class="fa fa-map-marker-alt"></i> Address:</strong>
                            <?php
                            echo $customer->cust_address . ", " . $customer->cust_city . ", " . $customer->cust_state . " - " . $customer->cust_zip;
                            ?>
                        </p>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>


        <!-- SOCIAL LINKS -->

        <div class="section social-links">

            <h2>Connect With Me</h2>

            <?php if (!empty($seller->facebook_link)) { ?>
                <a href="<?php echo $seller->facebook_link; ?>" title="Facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <?php } ?>
            <?php if (!empty($seller->instagram_link)) { ?>
                <a href="<?php echo $seller->instagram_link; ?>" title="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
            <?php } ?>
            <?php if (!empty($seller->youtube_social_link)) { ?>
                <a href="<?php echo $seller->youtube_social_link; ?>" title="YouTube" target="_blank"><i class="fab fa-youtube"></i></a>
            <?php } ?>
            <?php if (!empty($seller->whatsapp_number)) { ?>
                <a href="https://wa.me/<?php echo $seller->whatsapp_number; ?>" title="WhatsApp" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <?php } ?>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                },
            },
        });

        var videoSwiper = new Swiper(".videoSwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".video-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".video-next",
                prevEl: ".video-prev",
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                },
            },
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const status = document.getElementById('copyStatus');
                status.innerText = 'Copied!';
                status.style.color = '#28a745';
                setTimeout(() => {
                    status.innerText = 'Click to Copy';
                    status.style.color = '#666';
                }, 2000);
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>



    <!-- FOOTER -->

    <footer class="footer">
        <div class="container">

            <div class="footer-content">

                <h3>About Our Company</h3>

                <p>
                    We provide trusted products and exclusive offers through our verified vendors.
                    Use the coupon above to get the best deals directly from the vendor.
                </p>

                <p class="copyright">
                    © 2026 All Rights Reserved | Your Company Name
                </p>

            </div>

        </div>
    </footer>

</body>

</html>