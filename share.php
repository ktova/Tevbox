<?php 

// functions
function getRealUserIp(){
    switch(true){
      case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
      case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
      case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
      default : return $_SERVER['REMOTE_ADDR'];
    }
}

$ip = getRealUserIp();
$conf = parse_ini_file("config.ini");

// SQL Connect
$con = new mysqli($conf['server'], $conf['user'], $conf['pass'], $conf['dbname']);
if($con->connect_error){
    die();
}
mysqli_select_db($con, $conf['dbname']);

// Get uploads from user's IP
$sql = "SELECT * FROM public_uploads WHERE IP = '$ip'";
$pql = "SELECT * FROM private_uploads WHERE IP = '$ip'";
$result = $con->query($sql);
$private_res = $con->query($pql);

// Remove public asset
if( isset($_POST['rem_pub']) ){
    $fileID = (int)$_POST['fid'];
    $checkql = "SELECT * FROM public_uploads WHERE IP = '$ip' AND ID = '$fileID'";
    $checkres = $con->query($checkql);
    $checkdata = $checkres->fetch_assoc();
    // sec check
    $ip = getRealUserIp();
    if( $ip !== $checkdata['IP'] ){
        die();
    }
    // delete row
    $deleteql = "DELETE FROM public_uploads WHERE ID='$fileID'";
    if ($con->query($deleteql) === TRUE) {
        #good
    } else {
        die();
    }
    $toUnlink = 'shared/t-up_' . $checkdata['fname'];
    unlink($toUnlink);
}

// Remove private asset
if( isset($_POST['rem_pri']) ){
    $fileID = (int)$_POST['fid'];
    $checkql = "SELECT * FROM private_uploads WHERE IP = '$ip' AND ID = '$fileID'";
    $checkres = $con->query($checkql);
    $checkdata = $checkres->fetch_assoc();
    // sec check
    $ip = getRealUserIp();
    if( $ip !== $checkdata['IP'] ){
        die();
    }
    // delete row
    $deleteql = "DELETE FROM private_uploads WHERE ID='$fileID'";
    if ($con->query($deleteql) === TRUE) {
        #good
    } else {
        die();
    }
    $toUnlink = '../sec_shared/pvt-up_' . $checkdata['fname'];
    unlink($toUnlink);
}

// Close mysql connection
$con->close();

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Tova's Shared</title>
        <meta name="description" content="Tova shared upload">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="assets/sharercss.css">
        <link rel="shortcut icon" type="image/jpg" href="https://ktova.fr/old-site/static-old/favicontk.png"/>
    </head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/0d2059c859.js" crossorigin="anonymous"></script>
    <body class="bg-dark bg-gradient">
  
        <div id="menu" class="d-flex justify-content-between align-items-center bg-dark container-fluid py-2 px-5">
            <img style="width:50px" src="https://ktova.fr/old-site/static-old/mainlogochoco.svg">
            <div>
                <button type="button" class="btn text-light" onclick="openLogin()">Login</button>
                <button type="button" id="SignUpBtn" class="btn btn-outline-dark" onclick="openSignup()">Sign Up</button>
            </div>
        </div>

        <div id="content" class="container-fluid px-5 d-flex flex-column">

        <div id="drop_file_zone" ondrop="upload_file(event)" ondragover="return false" class="container-fluid bg-light bg-gradient shadowed">
            <div id="drag_upload_file">
                <h1 style="text-align:center"><i class="fas fa-cloud-upload-alt fa-2x"></i></h1>
                <p>Drop file here (Max 100MB)</p>
                <p>or</p>
                <p><input type="button" value="Select File" onclick="file_explorer();" /></p>
                <div class="makePriv">
                    <input type="checkbox" id="dlprotect" name="dlprotect">
                    <label for="scales">Protected</label>
                    <br>
                    <input type="text" id="passToolTip" name="passToolTip" style="display:none;text-align:center" placeholder="Enter a Pass">
                </div>
                <input type="file" id="selectfile" />

                <div class="stateOn state">
                    <span><i class="fas fa-sun fa-spin"></i>&nbsp;Uploading Files...</span>
                </div>

                <div class="stateSucceed state">
                    <span><i class="fas fa-check-circle"></i>&nbsp;Wonderful</span>
                </div>

                <div class="stateFailed state">
                    <span><i class="far fa-times-circle"></i>&nbsp;Upload Failed</span>
                </div>

            </div>
        </div>
        <div class="img-content container-fluid"></div>

        <!-- Uploads Container -->
        <div class="uploadsParent container-fluid">

            <div class="uploadsContainer bg-dark bg-gradient shadowed">
                <h1 class="uploadTitle"><i class="fas fa-lock"></i></h1>
                <hr>
                <table>
                    <tr>
                        <th>File</th>
                        <th>Date</th>
                        <th></th>
                    </tr>

            <?php while($row = $private_res->fetch_assoc()) { ?>
                    <tr>
                        <td><a target="_blank" class="fileLink" href="/dl?fname=<?= $row['fname'] ?>"><?= $row['fname'] ?></a><span class="badge bg-primary rounded-pill"><?= $row['fsize'] ?></span></td>
                        <td><?= $row['fdate'] ?></td>
                        <td class="tcenter" style="display:flex;justify-content:center;align-items:center">
                            <form method="post" action="/share.php">
                                <input type="hidden" name="fid" value="<?= $row['ID'] ?>">
                                <input type="submit" name="rem_pri" value="&#xf056" class="btn fa-input fas fa-minus-circle deleteBtn">
                            </form>
                            <a target="_blank" class="gotoBtn" href="/dl?fname=<?= $row['fname'] ?>"><i class="fas fa-external-link-alt gotoBtn"></i></a>
                        </td>
                    </tr>
            <?php } ?>

                </table>
            </div>

            <div class="uploadsContainer bg-dark bg-gradient shadowed">
                <h1 class="uploadTitle"><i class="far fa-eye"></i></h1>
                <hr>
                <table>
                    <tr>
                        <th>File</th>
                        <th>Date</th>
                        <th></th>
                        <th></th>
                    </tr>

            <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><a class="fileLink" href="/shared/t-up_<?= $row['fname'] ?>"><?= $row['fname'] ?></a><span class="badge bg-primary rounded-pill"><?= $row['fsize'] ?></span></td>
                        <td><?= $row['fdate'] ?></td>
                        <td class="tcenter">
                            <form method="post" action="/share.php">
                                <input type="hidden" name="fid" value="<?= $row['ID'] ?>">
                                <input type="submit" name="rem_pub" value="&#xf056" class="btn fa-input fas fa-minus-circle deleteBtn">
                            </form>
                        </td>
                        <td class="tcenter"><i class="far fa-eye viewBtn" onclick="preview('<?= $row['fname'] ?>')"></i></td>
                    </tr>
            <?php } ?>

                </table>
            </div>

        </div>

        <!-- Form Signin -->
        <div class="form-signin absolute" id="login">
            
            <form class="container-sm p-4 bg-light rounded text-center relative mt-4">
                <button type="button" class="btn-close absolute modalCloseBtn" aria-label="Close" onclick="closeLogin()"></button>
                <img class="mb-3" src="https://ktova.fr/old-site/static-old/mainlogochoco.svg" alt="" width="72" height="57">

                <div class="form-floating">
                <input type="email" class="form-control mb-2" id="floatingInput" placeholder="name@example.com">
                <label for="floatingInput">Email address</label>
                </div>
                <div class="form-floating">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                <label for="floatingPassword">Password</label>
                </div>

                <div class="checkbox my-3">
                <label>
                    <input type="checkbox" value="remember-me"> Remember me
                </label>
                </div>
                <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
            </form>

        </div>

        <!-- Form Signup -->
        <!-- Form Signin -->
        <div class="form-signup absolute" id="signup">
            
            <form class="container-sm p-4 bg-light rounded text-center relative mt-4">
                <button type="button" class="btn-close absolute modalCloseBtn" aria-label="Close" onclick="closeSignup()"></button>
                <img class="mb-3" src="https://ktova.fr/old-site/static-old/mainlogochoco.svg" alt="" width="72" height="57">

                <div class="form-floating">
                <input type="text" class="form-control mb-2" id="floatingInput" placeholder="JohnDoe">
                <label for="floatingInput">Username</label>
                </div>
                <div class="form-floating">
                <input type="email" class="form-control mb-2" id="floatingInput" placeholder="name@example.com">
                <label for="floatingInput">Email address</label>
                </div>
                <div class="form-floating">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                <label for="floatingPassword">Password</label>
                </div>

                <div class="checkbox my-3">
                <label>
                    <input type="checkbox" value="remember-me"> I accept
                </label>
                </div>
                <button class="w-100 btn btn-lg btn-primary" type="submit">Register</button>
            </form>

        </div>

        </div>
                
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
        <script src="assets/balls.js"></script>
        <script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }

            var fileobj;             
            
            function validate() {
                if (document.getElementById('dlprotect').checked) {
                    return true;
                } else {
                    return false;
                }
            }

            $( "#dlprotect" ).click(function() {
                $("#passToolTip").toggle();
            });

            function showPassToolTip(){
                if (document.getElementById('dlprotect').checked) {
                    $("#passToolTip").toggle();
                }
            }
            showPassToolTip();

            // Upload functions
            function upload_file(e) {
                e.preventDefault();
                fileobj = e.dataTransfer.files[0];
                ajax_file_upload(fileobj);
            }
            
            function file_explorer() {
                document.getElementById('selectfile').click();
                document.getElementById('selectfile').onchange = function() {
                    fileobj = document.getElementById('selectfile').files[0];
                    ajax_file_upload(fileobj);
                };
            }
            
            function ajax_file_upload(file_obj) {

                $('.stateOn').fadeIn();
                var protected = validate();
                if(file_obj != undefined && file_obj.size < 100485760) {
                    var form_data = new FormData();                  
                    form_data.append('file', file_obj);
                    var xhttp = new XMLHttpRequest();
                    if(protected===true){
                        var pwd = document.getElementById('passToolTip').value;
                        xhttp.open("POST", "/ajax.php?protected=true&id=<?= $ip ?>&pass="+pwd+"", true);
                        xhttp.onload = function(event) {  
                            oOutput = document.querySelector('.img-content');
                            if (xhttp.status == 200) {
                                $('.stateOn').fadeOut();
                                $('.stateSucceed').fadeIn();
                                furl = '/dl.php?fname=' + file_obj['name'];
                                oOutput.innerHTML = "Fichier disponible à l'adresse suivante : <a style='color:gold;text-decoration:none' href='"+ furl +"' target='_blank'>"+ furl +"</a>.";
                            } else {
                                $('.stateOn').fadeOut();
                                $('.stateFailed').fadeIn();
                                oOutput.innerHTML = "Error " + xhttp.status + " occurred when trying to upload your file.";
                            }
                        }
                        xhttp.send(form_data);
                    }

                    if(protected===false){
                        xhttp.open("POST", "/ajax.php?id=<?= $ip ?>", true);
                        xhttp.onload = function(event) {  
                            oOutput = document.querySelector('.img-content');
                            if (xhttp.status == 200) {
                                $('.stateOn').fadeOut();
                                $('.stateSucceed').fadeIn();
                                furl = '/shared/t-up_' + file_obj['name'];
                                oOutput.innerHTML = "Fichier disponible à l'adresse suivante : <a style='color:gold;text-decoration:none' href='"+ furl +"' target='_blank'>"+ furl +"</a>.";
                            } else {
                                $('.stateOn').fadeOut();
                                $('.stateFailed').fadeIn();
                                oOutput.innerHTML = "Error " + xhttp.status + " occurred when trying to upload your file.";
                            }
                        }
                        xhttp.send(form_data);
                    }
                }
                else{
                    oOutput = document.querySelector('.img-content');
                    oOutput.innerHTML = "An error occured.";
                }
            }

            function preview(fileName){
                var extension = fileName.substr(fileName.lastIndexOf(".") + 1);
                var fileUrl = '/shared/t-up_' + fileName;
                var pointer = document.getElementById("drop_file_zone");
                if( extension == 'png' || extension == 'jpg' || extension == 'gif'){
                    var previewpack = '<div class="filePreviewer" onclick="closePreview()"> <i class="fas fa-times-circle closePreview" onclick="closePreview()"></i> <img src="'+fileUrl+'"> <p style="color:white">'+extension.toUpperCase()+' file</p> </div>';
                } else {
                    var previewpack = '<div class="filePreviewer" onclick="closePreview()"> <i class="fas fa-times-circle closePreview" onclick="closePreview()"></i> <img src="/assets/fileicon.png"> <p style="color:white">'+extension.toUpperCase()+' file</p> </div>';
                }

                pointer.insertAdjacentHTML(
                    'afterend',
                    previewpack
                );
            }

            function closePreview(){
                $('.filePreviewer').remove();
            }
            
            function closeLogin(){
                $('#login').fadeOut(100);
            }

            function openLogin(){
                $('#login').fadeIn(100);
                $('#signup').fadeOut(100);
            }

            function closeSignup(){
                $('#signup').fadeOut(100);
            }

            function openSignup(){
                $('#login').fadeOut(100);
                $('#signup').fadeIn(100);
            }

        </script>

    </body>
</html>