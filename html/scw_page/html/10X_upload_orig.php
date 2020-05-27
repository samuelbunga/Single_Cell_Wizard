<?php
    include_once 'scw.php';
    echo $_POST['jobName'];
    echo "reached";
    echo ini_get('open_basedir');
    $currentDir = getcwd();
    $folderDir = "/home/ubuntu/ksharma/jobs/";
    $numFilesInLoc = count(scandir($folderDir))-2;
    $numFilesInLoc = (string)$numFilesInLoc;
    $uploadDir = $folderDir . $numFilesInLoc . $_POST['jobName'] . "/" . $_POST['jobName'];
    mkdir($uploadDir, 0777, true);
    $errors = [];

    $fileExtensions = ['mtx', 'tsv'];
    echo sys_get_temp_dir();
    if (isset($_POST['submitMatrix'])) {
      $fileName = $_FILES['10Xmatrix']['name'];
      $fileSize = $_FILES['10Xmatrix']['size'];
      $fileTmpName = $_FILES['10Xmatrix']['tmp_name'];
      $fileType = $_FILES['10Xmatrix']['type'];
      $fileExtension = strtolower(end(explode('.', $fileName)));
      echo $_FILES['10Xmatrix']['tmp_name'];
      if (!in_array($fileExtension, $fileExtensions)){
        $errors[] = "File extension is not of 10X type. Please upload a .mtx
        file as the Matrix";
	echo $fileName;
      }

      if($fileSize > 10000000){
        $errors[] = "File is too large, most likely not a 10X file.";
      }

      if(empty($errors)){
        $didUpload = move_uploaded_file($fileTmpName, $uploadDir);

        if($didUpload){
          echo "The file " . basename($fileName) . " was successfully uploaded.";
        } else {
	if(!is_writable($uploadDir)){echo "not writable";}
        if(!file_exists($_FILES['10Xmatrix']['tmp_name'])){if(!file_exists($uploadDir)){echo "Reached"; echo $uploadDir;}}
	echo "An error occurred in uploading " . basename($fileName) . ".";
        }
      } else {
        foreach($errors as $error) {
          echo "Error: " . $error . "\n";
        }
      }
      $matrix = True;
    }

    if (isset($_POST['submitBarcodes'])) {
      $fileName = $_FILES['10Xbarcodes']['name'];
      $fileSize = $_FILES['10Xbarcodes']['size'];
      $fileTmpName = $_FILES['10Xbarcodes']['tmp_name'];
      $fileType = $_FILES['10Xbarcodes']['type'];
      $fileExtension = strtolower(end(explode('.', $fileName)));

      if (!in_array($fileExtension, $fileExtensions)){
        $errors[] = "File extension is not of 10X type. Please upload a .mtx
        file as the Matrix";
      }

      if($fileSize > 10000000){
        $errors[] = "File is too large, most likely not a 10X file.";
      }

      if(empty($errors)){
        $didUpload = move_uploaded_file($fileTmpName, $uploadDir);

        if($didUpload){
          echo "The file " . basename($fileName) . " was successfully uploaded.";
        } else {
          echo "An error occurred in uploading " . basename($fileName) . ".";
        }
      } else {
        foreach($errors as $error) {
          echo "Error: " . $error . "\n";
        }
      }
      $barcodes = True;
    }

    if (isset($_POST['submitGenes'])) {
      $fileName = $_FILES['10XGenes']['name'];
      $fileSize = $_FILES['10XGenes']['size'];
      $fileTmpName = $_FILES['10XGenes']['tmp_name'];
      $fileType = $_FILES['10XGenes']['type'];
      $fileExtension = strtolower(end(explode('.', $fileName)));

      if (!in_array($fileExtension, $fileExtensions)){
        $errors[] = "File extension is not of 10X type. Please upload a .mtx
        file as the Matrix";
      }

      if($fileSize > 10000000){
        $errors[] = "File is too large, most likely not a 10X file.";
      }

      if(empty($errors)){
        $didUpload = move_uploaded_file($fileTmpName, $uploadDir);

        if($didUpload){
          echo "The file " . basename($fileName) . " was successfully uploaded.";
        } else {
          echo "An error occurred in uploading " . basename($fileName) . ".";
        }
      } else {
        foreach($errors as $error) {
          echo "Error: " . $error . "\n";
        }
      }
      $genes = True;
    }
    if($matrix & $barcodes & $genes){
      $enableRunPipeline = True;
    }
      if($_POST["RunPipeline"]){
        exec("/home/ubuntu/ksharma/web_pipeline_bash_script.sh",$uploadDir);}
?>
