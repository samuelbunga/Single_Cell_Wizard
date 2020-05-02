<?php include ('header.php');?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
   
    <!--<link href="style.css" rel="stylesheet">-->
    <link rel="shortcut icon" type="image/png" href="../images/favicon.ico"/>
  </head>
  <div class="container" style="margin: 25px 25px">
  <div style:"display:inline">
  <!--?php include 'old_header.php';?>-->
  <!--<h1 style="margin-left: 120px;font-size: 18px;">Single Cell Wizard</h1>-->
  </div>
  <body style="padding-top: 56px">
  <br />
  <br />
    <?php
          session_start();
         if(isset($_POST['reset'])||isset($_SESSION['pipe_done'])){
		session_unset();
		unset($_POST['reset']);
	}
          $set = False; ?>
    <fieldset class="field_set">
      <form action="scwiz.php" method = "post" enctype="multipart/form-data">
	<div class="uemailcc">
            <label class="labell">Email:</label>
            <input type="text" name="uemail" id="uemail" class="iii form-control">
       </div>
	<br />
       <div class="jobnamecc">
            <label class="labell">Job Name:</label>
            <input type="text" name="jobName" id="jobName" class="iii form-control">
       </div>

    <br />
    <a href="./propic/Sample_SingleCell_Data.zip" download="sample_data.zip" class="der" style="">Download Sample Data</a>
    <br />
    <br />
      <div>
        <label class="idjd" for="upload">Upload Fastq Data</label>
        <br />
        <input id="upload" name="upload[]" type="file" multiple="multiple" class="uploadimg" />

      </div>
          <br />
          <label class="idjd" for="species">Species</label>
          </br>
          <select name="species" class="margin_pixel">
          <option value="H">Human</option>
          <option value="M">Mouse</option>
          </select>
          <br />
        <label for="protocol" class="gmtclass">Protocol</label>
        <br />
        <select name="protocol" class="margin_pixel">
        <option value="10X">10X</option>
        <option value="dropseq">Dropseq</option>
        <option value="SRA">SRA</option>
        <option value="celseq">Celseq</option>
        </select>
          <br />
        <label for="aligner" class="gmtclass">Aligner</label>
        </br>
        <select name="aligner" class="margin_pixel">
        <option value="hisat2">hisat2</option>
        <option value="STAR">STAR</option>
        </select>
        </br>
        <label for="qc" class="gmtclass">Quality Control</label>
        </br>
        <select name="qc" class="margin_pixel">
        <option value="yes">Yes</option>
        <option value="no">No</option>
        </select>
        </br>
	</br>
        <label class="idjd" for="ssize">Sample Size:</label>
            <input type="text" name="ssize" id="ssize" class="iii form-control">
	
	<label for="pp" class="gmtclass">Post Process</label>
        </br>
        <select name="pp" class="margin_pixel">
        <option value="yes">yes</option>
        <option value="no">no</option>
	</select>

	<label class="labell">Barcode Length (Only for SRA):</label>
            <input type="text" name="bclen" id="bclen" class="iii form-control">
	<label class="labell">UMI Length (Only for SRA):</label>
            <input type="text" name="umi" id="umi" class="iii form-control">
	<!--- <label class="labell">Min Reads/Barcode (Only for SRA):</label>
	    <input type="text" name="min_bc" id="min_bc" class="iii form-control">
	--->
	<a href="javascript:toggleTable()">advance options</a><br>
	<div id="show_advance", style="display:none">
	<label class="min_cells">min cells (default=3):</label>
        <input type="text" name="min_cells" id="min_cells" class="iii form-control">
	<label class="min_genes">min genes (default=100):</label>
        <input type="text" name="min_genes" id="min_genes" class="iii form-control">

	</div>
	<script>
	function toggleTable() {
    var lTable = document.getElementById("show_advance");
    lTable.style.display = (lTable.style.display == "table") ? "none" : "table";
	}
	</script>
	
        <input class="submitbuton" type="submit" name="RunPipeline" value = "Run Pipeline">
    </form>
    <form action="scwiz.php" method="post">
      <input type="submit" name="reset" value = "Reset"/>
    </form>
    </fieldset>
    </br>
    <?php

    if(!empty($_POST['min_cells'])){
	$_SESSION['min_cells'] = $_POST['min_cells'];
	}
    else{
	$_SESSION['min_cells'] = 3;
	}
    if(!empty($_POST['min_genes'])){
        $_SESSION['min_genes'] = $_POST['min_genes'];
        }
    else{
        $_SESSION['min_genes'] = 100;
        }

		
    if(isset($_POST['RunPipeline'])){
    $_SESSION['jobName']	= $_POST['jobName'];
    $_SESSION['qc'] 		= $_POST['qc'];
    $_SESSION['pp']		= $_POST['pp'];
    $_SESSION['aligner']	= $_POST['aligner'];
    $_SESSION['protocol']	= $_POST['protocol'];
    $_SESSION['ssize']		= $_POST['ssize'];
    $_SESSION['bclen']		= $_POST['bclen'];
    $_SESSION['umi']		= $_POST['umi'];
    $_SESSION['species']	= $_POST['species'];
    #$_SESSION['min_bc']		= $_POST['min_bc'];
    $_SESSION['uemail']		= $_POST['uemail'];
    $currentDir 		= getcwd();
    $folderDir 			= "/home/ubuntu/project/" . $_SESSION['jobName'];

    $uploadDir = $folderDir . "/" . "_tmp_upload"; 
    $_SESSION['dir'] = $uploadDir;
    mkdir($uploadDir, 0777, true);
    chmod($uploadDir, 0777);
    chmod($folderDir, 0777);
    chown($folderDir, "ubuntu");
    chown($uploadDir, "ubuntu");
    $_SESSION['dir'] = $uploadDir;
    }

    ?>
    </div>
    <br/>

<?php

    if(isset($_POST["RunPipeline"])){
      if(count($_FILES['upload']['name']) == 2 || count($_FILES['upload']['name'] == 1)){
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
     }
}
?>

    <?php
      if(isset($_POST["RunPipeline"])){
	$_SESSION['p_PL'] = getcwd();
	chdir("/home/ubuntu/project/");
	if($_SESSION['protocol'] == "SRA"){
	$perl = exec("nohup perl /home/ubuntu/pre_process/main.pl -file " . $_SESSION['dir'] . " -id " . $_SESSION['jobName'] . " -protocol " . $_SESSION['protocol'] . " -out " . $folderDir . " -sp " . $_SESSION['species'] . " -bclen " . $_SESSION['bclen'] . " -umi " . $_SESSION['umi'] . " -sample_size " . $_SESSION['ssize'] . " -email " . $_SESSION['uemail'] . " -aligner " . $_SESSION['aligner'] . " -post_process " . $_SESSION['pp'] . " -min_cells " . $_SESSION['min_cells']. " -min_genes " . $_SESSION['min_genes'] ."&");
	}
	else{
	$perl = exec("nohup /usr/bin/perl /home/ubuntu/pre_process/main.pl -file "  . $_SESSION['dir'] . " -id " . $_SESSION['jobName'] . " -protocol " . $_SESSION['protocol'] . " -out " . $folderDir . " -sample_size " . $_SESSION['ssize'] . " -sp " . $_SESSION['species'] . " -aligner " . $_SESSION['aligner'] . " -qc " . $_SESSION['qc'] . " -email " . $_SESSION['uemail'] . " -post_process " . $_SESSION['pp'] . " -min_cells " . $_SESSION['min_cells']. " -min_genes " . $_SESSION['min_genes'] . "&");
	}
	echo $_ENV["USER"];
        exec($_SESSION['cmd']);
	$_SESSION['copy'] = "cp -r " . $_SESSION['out'] . "/ /var/www/html/scw_page/out/";
	exec($_SESSION['copy']);
	$_SESSION['pipe_done'] = True;
	chdir($_SESSION['p_PL']);} ?>
<br />
<br />

</div>
</body>

</html>

<?php include 'bhasin_lab_footer.php';?>
