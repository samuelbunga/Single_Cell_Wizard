<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Single Cell Wizard</title>
    <link href="style.css" rel="stylesheet">
    <link rel="shortcut icon" href="./favicon.ico">
  </head>
  <h1> Single Cell Wizard </h1>
  <body>
    <?php 
	  session_start();
	  if(isset($_SESSION['pipe_done'])){unset($_SESSION);}
	  $set = False; ?>
    <?php if(!isset($_SESSION['jobName'])){ ?>
      <form action="10X_upload.php" method = "post">
        Job Name: <input type="text" name="jobName" id="jobName">
        <input type = "submit" name = "nameSubmit" value = "Submit Job Name">
      </form> <?php } ?>
    <?php if(!isset($_POST['jobName'])){echo "Name not set";}else{$jobName=$_POST['jobName']; if(!isset($_SESSION['jobName'])){$_SESSION['jobName'] = $_POST['jobName'];}}?>
    <br />
    <?php if(!isset($_SESSION['matrix_in'])){ ?>
    <form action="10X_upload.php" method = "post" enctype="multipart/form-data">
      Upload 10X Matrix:
      <input type="file" name="10Xmatrix" id="10Xmatrix"><br />
      <input type="submit" name="submitMatrix" value = "Upload Matrix">
    </form> <?php } ?>
    <br />
    <?php if(!isset($_SESSION['barcodes_in'])){ ?>
    <form action="10X_upload.php" method = "post" enctype="multipart/form-data">
      Upload 10X Barcodes:
      <input type="file" name="10Xbarcodes" id="10Xbarcodes"><br />
      <input type="submit" name="submitBarcodes" value = "Upload Barcodes">
    </form> <?php } ?>
    <br />
    <?php if(!isset($_SESSION['genes_in'])) { ?>
    <form action="10X_upload.php" method = "post" enctype="multipart/form-data">
      Upload 10X Genes:
      <input type="file" name="10Xgenes" id="10Xgenes"><br />
      <input type="submit" name="submitGenes" value = "Upload Genes">
    </form> <?php } ?>
    <br />

    <form action="10X_upload.php" method = "post">
          <select name="gmt">
          <option value="human.gmt">Full Human Genes</option>
          <option value="transcriptionFactors.gmt">Transcription Factors</option>
          <option value="mouse.gmt">Mouse Genes</option>
          <option value="cancer_stemness.gmt">Cancer/Stemness</option>
          </select>
          <br>
          <input type="submit" name="gmt_select" value="Submit GMT Selection">
     </form>
    <form action="10X_upload.php" method="post">
      <input type="submit" name="RunPipeline" value = "Run Pipeline">
    </form>

<?php
    if(isset($_POST['jobName'])){ 
    $currentDir = getcwd();
    $folderDir = "/home/ubuntu/ksharma/jobs/";
    $numFilesInLoc = count(scandir($folderDir))-2;
    $numFilesInLoc = (string)$numFilesInLoc;
    if(isset($_SESSION['jobName']) & !$set){
    $_SESSION['num'] = $numFilesInLoc;
    $_SESSION['out'] = $folderDir . $numFilesInLoc . $_POST['jobName'];
    $uploadDir = $folderDir . $numFilesInLoc . $_POST['jobName'] . "/" . $_POST['jobName'];
    $_SESSION['job_name'] = $_SESSION['num'] . $_POST['jobName'];
    $_SESSION['dir'] = $uploadDir;
    mkdir($uploadDir, 0777, true);
    chmod($uploadDir, 0777);
    chmod($_SESSION['out'], 0777);
    chown($_SESSION['out'], "ubuntu");
    chown($uploadDir, "ubuntu");
    $set = True;}
    $errors = [];
    }
    $fileExtensions = ['mtx', 'tsv'];
    if (isset($_POST['submitMatrix'])) {
      $fileName = $_FILES['10Xmatrix']['name'];
      $fileSize = $_FILES['10Xmatrix']['size'];
      $fileTmpName = $_FILES['10Xmatrix']['tmp_name'];
      $fileType = $_FILES['10Xmatrix']['type'];
      $fileExtension = strtolower(end(explode('.', $fileName)));

      if (!in_array($fileExtension, $fileExtensions)){
        $errors[] = "File extension is not of 10X type. Please upload a .mtx
        file as the Matrix";
	echo $fileName;
      }

      if($fileSize > 10000000){
        $errors[] = "File is too large, most likely not a 10X file.";
      }

      if(empty($errors)){
        $didUpload = move_uploaded_file($fileTmpName, ($_SESSION['dir'] . "/matrix.mtx"));

        if($didUpload){
          echo "The file " . basename($fileName) . " was successfully uploaded.";
	  $_SESSION['matrix_in'] = True;
        } else {
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
        echo $uploadDir;
        $didUpload = move_uploaded_file($fileTmpName, ($_SESSION['dir'] . "/barcodes.tsv"));

        if($didUpload){
          echo "The file " . basename($fileName) . " was successfully uploaded.";
	  $_SESSION['barcodes_in'] = True;
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
      $fileName = $_FILES['10Xgenes']['name'];
      $fileSize = $_FILES['10Xgenes']['size'];
      $fileTmpName = $_FILES['10Xgenes']['tmp_name'];
      $fileType = $_FILES['10Xgenes']['type'];
      $fileExtension = strtolower(end(explode('.', $fileName)));

      if (!in_array($fileExtension, $fileExtensions)){
        $errors[] = "File extension is not of 10X type. Please upload a .mtx
        file as the Matrix";
      }

      if($fileSize > 10000000){
        $errors[] = "File is too large, most likely not a 10X file.";
      }

      if(empty($errors)){
        $didUpload = move_uploaded_file($fileTmpName, ($_SESSION['dir'] . "/genes.tsv"));

        if($didUpload){
          echo "The file " . basename($fileName) . " was successfully uploaded.";
	  $_SESSION['genes_in'] = True;
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
    if(isset($_POST["gmt_select"])){
      $_SESSION['gmt'] = $_POST["gmt"];
      echo $_SESSION['gmt'];}
    if($matrix & $barcodes & $genes){
      $_SESSION['runPipe'] = True;
    }
      if(isset($_POST["RunPipeline"]) && isset($_SESSION['gmt'])){
	$_SESSION['p_PL'] = getcwd();
	chdir("/home/ubuntu/ksharma");
	echo "Pipeline running...";
	$_SESSION['cmd'] = "sudo ./web_pipeline_bash_script.sh " . $_SESSION['dir'];
	echo $_ENV["USER"];
        exec($_SESSION['cmd']);
	$_SESSION['copy'] = "cp -r " . $_SESSION['out'] . "/ /var/www/html/scw_page/out/";
	exec($_SESSION['copy']);
	$_SESSION['pipe_done'] = True;
	chdir($_SESSION['p_PL']);}?>
<br />

<?php if(isset($_SESSION['pipe_done'])) { ?>
<img class="logo" src= "<?php $_SESSION['tsne']= './out/' .  $_SESSION['job_name'] . '/' . $_SESSION['job_name'] . '_tsne'; echo $_SESSION['tsne'];?>" alt=<?php echo $_SESSION['tsne'];?>/>
<img class="logo" src= "<?php $_SESSION['l_tsne']= "./out/" . $_SESSION['job_name'] . '/' . 'gene_set_labelled_TSNE.png';echo $_SESSION['l_tsne'];?>" alt=<?php echo $_SESSION['l_tsne'];?>/>
<br />
<?php $files = array_filter(scandir(("/var/www/html/scw_page/out/" . $_SESSION['job_name'])), function($item) { return !is_dir(("/var/www/html/scw_page/out/" . $_SESSION['job_name'] . "/" . $item));});
foreach($files as $file){?>
    <?php $link = "./out/" . $_SESSION['job_name'] . "/" .  $file; ?>
    <a href="<?php echo $link;?>" download=<?php echo $file;?>><?php echo $file;?></a>
 <br><?php } }?>
</body>
</html>
