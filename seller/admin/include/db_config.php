
  <?php

    $servername = "localhost";

    /* First Database */
    // $username   = "root";
    // $password   = "";
    // $dbname     = "secondside_seller";

    $username = "projectuser";
    $password = "Solutions@321@";
    $dbname   = "secondside_seller";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (! $conn) {
        die("Connection failed (Seller DB): " . mysqli_connect_error());
    }

    /* Second Database */


    // $username2   = "root";
    // $password2   = "";
    // $dbname2     = "new_jhbewdmy_ssf_in";



    $dbname2 = "jhbewdmy_health";
    $username2 = "myuser";
    $password2 = "Solutions@321@";

    $conn3 = mysqli_connect($servername, $username2, $password2, $dbname2);

    if (! $conn3) {
        die("Connection failed (SSF DB): " . mysqli_connect_error());
    }

    date_default_timezone_set('Asia/Kolkata');

    // $base_url = "http://localhost/araweb/githubhealth/";
    $base_url = "https://ssfhealth.in/";
