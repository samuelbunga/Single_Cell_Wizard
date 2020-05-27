<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Description</title>
    <link rel="stylesheet" type="text/css" href="desc.css">
  </head>
  <?php include 'header.php'; ?>
  <h1>Pipeline Workflow</h1>
  <body>
   <div class="banner">
    <img class="banner-image" src="./propic/Map.png" alt="Workflow">
   </div>
   <h2>About the Pipeline</h2>
    <p>
    	As shown above, the Single Cell Wizard uses a clustering based approach for the identification of cells from raw single cell sequenced data. The input used is the three 10X data files: matrix.mtx, barcodes.tsv, and genes.tsv.
	<br/>
	<br/>
    	The tool first clusters the cells on the basis of gene similarity and overestimates the initial number of total clusters in order for more specific and accurate labelling through gene expression levels. Gene expression levels are employed for enrichment analysis against known cell type gene expression rankings, through this, we are able to accurate identify the cell type on a labelled plot as well as providing various details on how the identification was obtained. Below is a description for every output to the user.
	<br/>
	<br/>
    	The tool allows for the analysis of single sample and the comparison of two samples. 
    </p>
    <h2>Usage</h2>
    <p>
        The key concept of the pipeline is to provide a combination of efficacy, efficiency, and ease of use. Usage instructions are provided below:
    </p>
    <div class="centered-list">
    <ol style="text-align:left; list-style-position:inside;">
        <li><strong>Job Name:</strong> You will need to enter a name for the job you are submitting. In the final output there will be a random number prepended to the job name in order to insure that your job is unique and correctly processed. <strong>For sample comparison, please enter a job name for each sample.</strong></li>
        <li><strong>File Upload:</strong> This step requires the user to upload <strong>all three</strong> single cell sequenced files: matrix.mtx, barcodes.tsv, genes.tsv. Please make sure that you select all three at once and then click open in your file selector prompt.</li>
        <li><strong>GMT Selection:</strong> We have compiled developed 4 different gene sets for analysis and cell labelling. You may select one of the following:
            <ul style="text-align:left; list-style-position:inside;">
                <li><strong>Full Human Genes: </strong> Contains more than 600 unique cell type identifications. Primarily immune, skin, and muscle cells are in this database.</li>
                <li><strong>Transcription Factors: </strong> Contains  mapping for which transcription factor proliferated the coding of expressed genes. </li>
                <li><strong>Mouse Genes: </strong> Contains the homologous genes to the Full Human Genes database in mice. Translated using a script for complete GMT conversion, please contact us if you would like to translate your GMT.</li>
                <li><strong>Cancer/Stemness: </strong> Contains genes which are found in cancers and stemness indicators. Especially useful for cancer or general disease single cell insight.</li>
            </ul>
        </li>
        <li><strong>Run Pipeline:</strong> Executes the pipeline on upload data. Please only click this button once, the page will load and display appropriate tSNE plots and links for your job's file downloads.</li>
        <li><strong>Reset:</strong> Clear the previous job for analysis on other samples.</li>
    </ol>
    </div>
    <h2>Outputs</h2>
    <div class="centered-list">
    <ul style="text-align:left; list-style-position:inside;">
    	<li><strong>*job name* tSNE:</strong> An unlabelled tSNE plot containing the clustered cells.</li>
    	<li><strong>Cluster GSEA Results:</strong> An excel workbook containing the information on how the identity of the cells was determined.</li>
    	<li><strong>Mapped Cluster Reference:</strong> An excel workbook containing information for the details on the determination of the broader identity of the cells.</li>
    	<li><strong>File.rds:</strong> A Seurat R object for further analysis. Contains cluster labelled cells, marker genes, PCAs, etc.</li>
    	<li><strong>File Markers:</strong> A CSV that contains ranked overexpressed expressed genes used for identification.</li>
    	<li><strong>Gene Set Labelled tSNE:</strong> A PNG of a labelled tSNE plot containing the clustered cells.</li>
    	<li><strong>MTX:</strong> A matrix containing the enrichment scores for cell identities on the basis of cluster for further analysis.</li>
    </ul>
    </div>
  </body>
</html>


