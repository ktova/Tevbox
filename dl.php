<?php 

// Variables
$error = false;
$invalid_pass = false;
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$conf = parse_ini_file("config.ini");

// SQL Connect
$con = new mysqli($conf['server'], $conf['user'], $conf['pass'], $conf['dbname']);
if($con->connect_error){
    die();
}

// File logic
if( !isset($_GET['fname']) ){
    # pass to no file screen
} else {
    $filename = $_GET['fname'];
    mysqli_select_db($con, $conf['dbname']);
    $sql = "SELECT * FROM private_uploads WHERE fname = '$filename'";
    $result = $con->query($sql);
    if ( $result->num_rows == 0 ){
        $error = true;
    } else {
        $res = $result->fetch_assoc();
        if( isset($_POST['filepass']) ){
            if (password_verify($_POST['filepass'], $res['fpwd'])) {
                # Don't forget to move sec_shared/ to an unreachable location
                $file_url = 'sec_shared/pvt-up_' . $filename;
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary"); 
                header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
                ob_clean(); flush();
                readfile($file_url); 
            } else {
                $invalid_pass = true;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Tova's Shared</title>
        <meta name="description" content="Tova shared upload">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type="image/jpg" href="https://ktova.fr/old-site/static-old/favicontk.png"/>
    </head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/0d2059c859.js" crossorigin="anonymous"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap');
        body{
            background-color: #1d1d1d;
            display:flex;
            justify-content:center;
            align-items:center;
            flex-direction:column;
            padding: 0 100px;
            color:white;
            font-family: 'Noto Sans', sans-serif;
            min-height:100vh;
        }
        form{
            display:flex;
            justify-content:center;
            flex-direction:column;
        }
        .logofile{
            font-size: 100px;
        }
        .weight{
            color:gold;
        }
        hr{
            width:100%;
            color:white;
        }
        #filepass{
            text-align:center;
            padding:10px;
        }
        #filedl{
            margin-top:16px;
            color:gold;
            border: 1px solid gold;
            padding: 10px 30px;
            border-radius:5px;
            background:none;
            cursor:pointer;
            transition: background-color 0.33s;
            font-weight:400;
        }
        #filedl:hover{
            background-color: #424242;
        }
        .toUpload > a {
            text-decoration:none;
            color:orange;
        }
    </style>
    <body class="container-fluid bg-dark bg-gradient">

    <img style="margin:20px 0" src="/old-site/static-old/mainlogowhite.svg">

    <?php if( !isset($_GET['fname']) || empty($_GET['fname']) ){ ?>
        <i class="fas fa-frown-open logofile" style="color:gold;"></i>
        <br>
        <p>Please provide a valid file</p>
    <?php } elseif ($error === true) { ?>
        <i class="fas fa-frown-open logofile" style="color:gold;"></i>
        <br>
        <p>The file you are looking for doesn't exist or has been deleted</p>
    <?php } else { ?>
        <i class="fas fa-cloud-download-alt logofile" style="color:gold;"></i>
        <br>
        <p><?= $res['fname'] ?></p>
        <br>
        <b class="weight"><?= $res['fsize'] ?></b>
        <hr>
        <p>This file is password protected</p>
        <form method="post" action="<?= $actual_link ?>">
            <input type="text" id="filepass" name="filepass">
            <br>
            <input type="submit" id="filedl" value="Download">
            <br>
        </form>
        <script>document.getElementById('filepass').value = ''</script>
        <?php if($invalid_pass === true){ echo '<p style="color:red">Invalid Password !</p>'; } ?>
    <?php } ?>

    <div class="toUpload">
        <br>
        <a href="/share">
            <i class="fas fa-arrow-alt-circle-left"></i> Back to upload
        </a>
    </div>

    </body>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script>
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
    </script>

</html>