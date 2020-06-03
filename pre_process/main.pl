#/usr/bin/perl
use warnings;
use strict;
use feature qw(say);
use Getopt::Long;
use Pod::Usage;


=cut
Program:        Pre-processing of 10X, CelSeq, DropSeq or SRA protocol fastq
Input:          Paired-end or Single end fastq files
Output:         Counts matrix
Author:         Samuel Bunga
Contact:        bunga.s@husky.neu.edu
=cut


#variables list
my($exe);

my $usage = "\n\n$0 [options] \n

Usage: perl $0 -file <path/to/file> -sp <M/H> -out <output_name> -sample_size <integer> -protocol <10x/dropseq/SRA/celseq> -qc <yes/no> -aligner <hisat2/STAR>

Options:
	-file 		input file/path, (if it is a pair-end, this program only expects 2 files  *_R1.fastq and *_R2.fastq)
	-sp		Species(Human/Mouse)
	-out		Output name
	-protocol	Protocol(10X/dropseq/SRA/celseq)
	-sample_size 	Size of the sample
	-bclen		Size of the barcode
	-qc		Quality control (default = yes)
	-aligner 	Choose aligner: STAR/hisat2 (default = hisat2)
	-umi		UMI length
	-min_bc		Minimum reads per each barcode
	-email		Receiver email
	-post_process   Select if to perform post process or not
	-min_genes	give min no. of genes
	-min_cells	give min no. of cells
	-help		Show this message	

";

GetOptions (	
		"job=i"		=> \my $job_id,		#batch number
		"sp=s"		=> \my $species,    	#M or H
              	"file=s"   	=> \my $data,      	#path to the file
              	"out=s"  	=> \my $name,		#name of the project
		"protocol=s" 	=> \my $protocol,	#Protocol
		"bclen=i"	=> \my $bc_len, 	#bc length
		"sample_size=i" => \my $sample_size,	#sample size
		"aligner=s"	=> \my $aligner,	#Aligner
		"qc=s"		=> \my $qc,		#quality check
		"umi=i"		=> \my $umi,		#UMI length
		"min_bc=i"	=> \my $min_bc,		#Min reads/barcode
		"email=s"       => \my $to_email,       #Receiver email
		"post_process=s"=> \my $pp,		#post process
		"min_genes=i"	=> \my $min_genes,	#min genes
		"min_cells=i"	=> \my $min_cells,	#min cells
		"id=s"		=> \my $id,		#Get the output name
		"help" 		=> sub{pod2usage($usage);}
	)   
  	or die($usage);

	unless($data) { 
		die "Provide a file to open";
	}

	unless($species){
		die "Please provide a species name";
	}
	unless($name){
		die "Please give a name for the output";
	}
	unless($protocol){
		die "Please provide protocol type";
	}
	
	if($protocol eq "10x" || $protocol eq "10X" || $protocol eq "dropseq" || $protocol eq "celseq"){ 

		unless($sample_size){
			die say"Please provide sample size!";
		}
	}
	else{
			
	}
	
	if($protocol eq "SRA" || $protocol eq "sra"){
		
		unless($bc_len){
			die "Please provide barcode length for SRA";
		}
	}
	else{
		
		}

	unless($qc){
		say "Quality check has not been defined, proceeding with default (qc == yes)!";
		$qc = "yes";
	}
	unless($aligner){
		say "Aligner has not been defined, proceeding with default (aligner == hisat2)!";
		$aligner = "hisat2";
	}
	unless($pp){
		say "Post process not defined, default is 'Yes'";
		$pp = "yes";
	}
	unless($min_genes){
		say "min genes not defined, default is 100";
		$min_genes='100';
		}
	unless($min_cells){
		say "min cells not defined, default is 3";
		$min_cells='3';
		}

	#attaching job id to the given job name
	$id = join("_", $job_id, $id);
        

	if($protocol eq "10Xv2" || $protocol eq "dropseq" || $protocol eq "celseq" || $protocol eq "10Xv3"){

		 $exe = `perl /home/ubuntu/pre_process/pre_process_10x.pl -file $data -sp $species -out $name -protocol $protocol -sample_size $sample_size -qc $qc -aligner $aligner -email $to_email -post_process $pp -id $id -min_genes $min_genes -min_cells $min_cells`;
	}
	elsif($protocol eq "SRA" || $protocol eq "sra" || $protocol eq "fastq"){
	
		unless($bc_len){
			die "Program killed. No barcode length given\n $usage";
			}
		$exe = `perl /home/ubuntu/pre_process/pre_process_sra.pl -file $data -sp $species -out $name -protocol $protocol -bclen $bc_len -umi $umi -sample_size $sample_size -email $to_email -aligner $aligner -post_process $pp -id $id -min_cells $min_cells -min_genes $min_genes`;

	}

	else{
		die $usage;
	}


