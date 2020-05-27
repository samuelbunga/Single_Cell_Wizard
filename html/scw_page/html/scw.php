<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Single Cell Wizard</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
  </head>
  <h1> Single Cell Wizard </h1>
  <body>
      <form action="10X_upload.php" method = "post">
        Job Name: <input type="text" name="jobName" id="jobName">
        <input type = "submit" name = "nameSubmit" value = "Submit Job Name">
      </form>
    <?php if(!isset($_POST['jobName'])){echo "not set";}else{$jobName=$_POST['jobName']; echo $jobName;}?>
    <form action="10X_upload.php" method = "post" enctype="multipart/form-data">
      Upload 10X Matrix:
      <input type="file" name="10Xmatrix" id="10Xmatrix">
      <input type="submit" name="submitMatrix" value = "Upload Matrix">
    </form>
    <?php echo $_FILES['10Xmatrix']['name'];?>
    <br />
    <form action="10X_upload.php" method = "post" enctype="multipart/form-data">
      Upload 10X Barcodes:
      <input type="file" name="10Xbarcodes" id="10Xbarcodes">
      <input type="submit" name="submitBarcodes" value = "Upload Barcodes">
    </form>
    <br />
    <form action="10X_upload.php" method = "post" enctype="multipart/form-data">
      Upload 10X Genes:
      <input type="file" name="10Xgenes" id="10Xgenes">
      <input type="submit" name="submitGenes" value = "Upload Genes">
    </form>
    <br />
    <form action="10X_upload.php" method="post">
      <input type="submit" name="RunPipeline" value = "Run Pipeline">
    </form>
  </body>
</html>
