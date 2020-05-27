<?php include ('header.php');?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
   
    <link href="style.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="../images/favicon.ico"/>
  </head>
  <div class="container" style="margin: 25px 25px">
  <div style:"display:inline">
  <?php include 'old_header.php';?>
  <h1 style="margin-left: 120px;font-size: 18px;">Single Cell Wizard</h1>
  </div>
  <body>
  <br />
  <br />
    <?php
	  session_start();
	 if(isset($_POST['reset'])||isset($_SESSION['pipe_done'])){session_unset(); unset($_POST['reset']);}
	  $set = False; ?>
    <fieldset class="field_set">
      <form action="v2_10X_upload.php" method = "post" enctype="multipart/form-data">
       <div class="jobnamecc">
            <label class="labell">Job Name:</label> 
            <input type="text" name="jobName" id="jobName" class="iii form-control">
       </div>
        
    <br />
    <a href="./propic/Sample_SingleCell_Data.zip" download="sample_data.zip" class="der" style="">Download Sample Data</a>
    <br />
    <br />
      <div>
        <label class="idjd" for="upload">Upload 10X Data</label>
	<br />
        <input id="upload" name="upload[]" type="file" multiple="multiple" class="uploadimg" />
      </div>
          <br />
          <select name="gmt" class="margin_pixel">
          <option value="human.gmt">Full Human Genes</option>
          <option value="transcription_factors.gmt">Transcription Factors</option>
          <option value="mouse.gmt">Mouse Genes</option>
          <option value="cancer_stemness.gmt">Cancer/Stemness</option>
          </select>
          <br />
	<label for="gmt-upload" class="gmtclass">Upload Your GMT</label>
	<br />
	<input id="gmt-upload" name="gmt-upload" type="file" class="uploadimg" />
	  <br />
      <input class="submitbuton" type="submit" name="RunPipeline" value = "Run Pipeline">
    </form>
    
    <form action="v2_10X_upload.php" method="post">
      <input type="submit" name="reset" value = "Reset"/>
    </form>
    </fieldset>
    <?php
    if(isset($_POST['RunPipeline'])){
    $_SESSION['jobName'] = $_POST['jobName'];
    $currentDir = getcwd();
    $folderDir = "/home/ubuntu/ksharma/jobs/";
    $numFilesInLoc = count(scandir($folderDir))-2;
    $numFilesInLoc = (string)$numFilesInLoc;
    if(isset($_SESSION['jobName']) & !$set){
    $_SESSION['num'] = $numFilesInLoc;
    $_SESSION['out'] = $folderDir . $numFilesInLoc . $_POST['jobName'];
    $_SESSION['full_name'] = $numFilesInLoc . $_POST['jobName'];
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
    ?>
    <?php
    $fileExtensions = [".tsv", ".mtx"];
    if(isset($_POST['RunPipeline'])){
      if(count($_FILES['upload']['name']) == 3){
        for($i=0; $i < count($_FILES['upload']['name']); $i++){
          $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
	  $fileName = $_FILES['upload']['name'][$i];
          if($tmpFilePath != ""){
            $filenm = $_FILES['upload']['name'][$i];
            if(move_uploaded_file($tmpFilePath, ($_SESSION['dir'] . "/" . $filenm))) {
              $files[] = $_FILES['upload']['name'][$i];
            }
          }
        }
	$blank = " ";
	$_SESSION['go_back'] = getcwd();
	chdir("/home/ubuntu/ksharma");
	$validate = "sudo ./dataValidation.sh " . $_SESSION['dir'];
	$success = exec("sudo ./dataValidation.sh " . $_SESSION['dir'], $blank, $success);
	chdir($_SESSION['go_back']);
	if($success == 1){
	   echo "Barcode file missing.";}
	if($success == 2){
	   echo "Gene file missing.";}
	if($success == 3){
	   echo "Matrix file missing.";}
	if($success == 4){
	   $_SESSION['files_up'] = True;}
	if($success == 5){
	   echo "10X files did not upload properly due to incompatibility";}
      }else {echo "Please upload the correct format of 10X Data: matrix.mtx, barcodes.tsv, genes.tsv.";}
      ?><br />
      <div class="centered-list">
      <?php
      if(isset($_SESSION['files_up'])){
          echo "Uploaded Files:"; ?>
      <br /><?php
      if(is_array($files)){
        echo "<ul>";
        foreach ($files as $file) {
          echo "<li>$file</li>";
        }
        echo "<ul>";
      }}else {echo "Files failed to upload. Please make sure they are the correct format of 10X data: matrix.mtx, barcodes.tsv, genes.tsv.";}
    } ?>
    </div>
    <br/>
    <?php
    if(isset($_POST["RunPipeline"])){
      if(file_exists($_FILES['gmt-upload']['tmp_name'])){
	if(strcmp(pathinfo($_FILES['gmt-upload']['name'], PATHINFO_EXTENSION), "gmt"))
	$gmtDir = "/home/ubuntu/ksharma/gsea_data/user/" . $_FILES['gmt-upload']['name'];
	if(move_uploaded_file($_FILES['gmt-upload']['tmp_name'], $gmtDir)){
	$_SESSION['gmt'] = "user/" . $_FILES['gmt-upload']['name'];
	echo "Chosen GMT: " . $_FILES['gmt-upload']['name'];}}
      else {
      $_SESSION['gmt'] = $_POST["gmt"];
      $gmtDir = "/home/ubuntu/ksharma/gsea_data/" . $_SESSION['gmt'];
      echo "Chosen GMT: " . $_POST['gmt'];}}

      if(isset($_POST["RunPipeline"]) && isset($_SESSION['gmt']) && isset($_SESSION['files_up'])){
	$_SESSION['p_PL'] = getcwd();
	chdir("/home/ubuntu/ksharma");
	$_SESSION['cmd'] = "sudo ./web_pipeline_bash_script.sh " . $_SESSION['dir'] . " " . $_SESSION['gmt'] . " " . $gmtDir;
	echo $_ENV["USER"];
        exec($_SESSION['cmd']);
	$_SESSION['copy'] = "cp -r " . $_SESSION['out'] . "/ /var/www/html/scw_page/out/";
	exec($_SESSION['copy']);
	$_SESSION['pipe_done'] = True;
	chdir($_SESSION['p_PL']);}elseif(isset($_POST["RunPipeline"]) && !(isset($_SESSION['gmt'])) && !(isset($_SESSION['files_up']))) {echo "Please ensure that the all of the 10X files were confirmed as uploaded and that your gene set matrix has been chosen";}?>
<br />
<br />
<?php if(isset($_SESSION['pipe_done'])) { ?>
<img class="logo" src= "<?php $_SESSION['tsne']= './out/' .  $_SESSION['job_name'] . '/' . $_SESSION['job_name'] . '_tsne'; echo $_SESSION['tsne'];?>" alt=<?php echo $_SESSION['tsne'];?>/>
<img class="logo" src= "<?php $_SESSION['l_tsne']= "./out/" . $_SESSION['job_name'] . '/' . 'gene_set_labelled_TSNE.png';echo $_SESSION['l_tsne'];?>" alt=<?php echo $_SESSION['l_tsne'];?>/>
<br />
<div class="tabl">
<table>
    <tr>
	<th>File Type</th>
	<th>Download Link</th>
    </tr>
<?php $files = array_filter(scandir(("/var/www/html/scw_page/out/" . $_SESSION['job_name'])), function($item) { return !is_dir(("/var/www/html/scw_page/out/" . $_SESSION['job_name'] . "/" . $item));});
foreach($files as $file){?>
    <tr>
	<td>
    <?php
       if(strcmp(($_SESSION['full_name'] . "_tsne"), $file) == 0){
	echo "Blank tSNE Plot: ";}
       elseif(strcmp("Cluster_GSEA_Results.xlsx", $file) == 0){
	echo "Enrichment Analysis Details: ";}
       elseif(strcmp("Mapped_Cluster_Reference.xlsx", $file) == 0){
	echo "Identification Reference Information: ";}
       elseif(strcmp("file.rds", $file) == 0){
	echo "Loadable R Object for Further Analysis: ";}
       elseif(strcmp("file_markers.csv", $file) == 0){
	echo "Gene Expression Analysis: ";}
       elseif(strcmp(($_SESSION['job_name'] . "_out.txt"), $file) == 0){
	echo "Pipeline outputs: ";}
       elseif(strcmp("gene_set_labelled_TSNE.png", $file) == 0){
	echo "Labelled tSNE Plot: ";}
       elseif(strcmp("mtx", $file) == 0){
	echo "Expression score matrix (gene set by cluster): ";}
       else{echo "Output file: ";} ?>
	</td>
	<td>
    <?php $link = "./out/" . $_SESSION['job_name'] . "/" .  $file; ?>
    <a href="<?php echo $link;?>" download=<?php echo $file;?>><?php echo $file;?></a>
	</td>
	</tr> 
<?php } }?>
</table>
</div>
</div>
</div>
</body>

</html>

<?php include 'footer.php';?>