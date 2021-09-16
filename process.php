<?php

        // setup
        if( !isset($_GET['id']) ){
            die();
        }

        // data & checks
        $user_ip = $_GET['id'];
        $filename = $_FILES['file']['name'];
        $date = date('m/d/Y h:i:s', time()); 
        $size =  (int)$_FILES['file']['size'] / (int)1000000;
        $sizename = number_format((float)$size, 2, '.', '') . ' mb';
        $conf = parse_ini_file("config.ini");
  
        if( ($_FILES['file']['name'] == '.htaccess') ){
            die();
        }

        if( ($_FILES['file']['name'] == '.htpasswd') ){
            die();
        }

        if (strpos($_FILES['file']['name'], '.php') !== false) {
            die();
        }

        // SQL Insert
        $con = new mysqli($conf['server'], $conf['user'], $conf['pass'], $conf['dbname']);
        if($con->connect_error){
            die();
        }

        
        // Handle Upload
        switch($_GET['protected']){

            /* Private Upload */
            case true:
                if(empty($_GET['pass'])){
                    die();
                }

                $filepass = password_hash($_GET['pass'], PASSWORD_DEFAULT);
                $private_sql = "INSERT INTO private_uploads (IP, fname, fsize, fdate, fpwd)
                VALUES ('$user_ip', '$filename', '$sizename', '$date', '$filepass')";

                if ($con->query($private_sql) === TRUE) {
                  # success
                } else {
                    die();
                }
                $con->close();

                $filename = 'pvt-up_'.$_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], '../sec_shared/'.$filename);

                // mail
                $message = 'New file upload by ' . $user_ip . ' <br> Filename: ' . $filename;
                mail('tevkeo@gmail.com','New Shared Protected Upload',$message,'From: Share@ktova.fr');

                break;

            /* Public Upload */
            case false: 
                $sql = "INSERT INTO public_uploads (IP, fname, fsize, fdate)
                VALUES ('$user_ip', '$filename', '$sizename', '$date')";

                if ($con->query($sql) === TRUE) {
                  # success
                } else {
                    die();
                }
                $con->close();

                $filename = 't-up_'.$_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], 'shared/'.$filename);

                // mail
                $message = 'New file upload by ' . $user_ip . ' <br> Filename: ' . $filename;
                mail($conf['mailto'],'New Shared Public Upload',$message,$conf['mailfrom']);

                break;
        }
        
        die;

        ?>