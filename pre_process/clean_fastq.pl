#/usr/bin/perl
use warnings;
use strict;
use feature qw(say);
use Getopt::Long;

GetOptions ( 
	"file=s" => \my $infile,
	"umi=i" => \my $umi,
	"bc_len=i" => \my $bc_len,
	"wd=s" => \my $wd,
	"min_bc=i" => \my $min_bc,
)

or die("Error please provide arguments\n");
my $infh;


unless(open($infh, "<", $infile)){ die;}
my $barcodes = join("/", $wd,"barcodes.txt");
my $count = 0;
my(@lines,@null,$i,$j,$fh,$process,$outFH,@bc,$bc_counts,@temp, $temp_file, $outfh,$bc_file, $out_r);
while(<$infh>){
	chomp $_;
	$count++;
	push @lines, $_;
	if($count == 4){
		if($lines[0] =~ /(\@.+?)(\_)([ATGC]+)/){
			@lines=@null;
			$count=0;
			open $outFH, '>>:encoding(UTF-8)', "$barcodes"; 
			say $outFH $3;	
		}
	}
}
close $infh;

#Full path to the barcodes file
$bc_file = $barcodes;
$out_r	 = join("/",$wd, "filtered_bc.txt");

#Run the r_sra_wrapper script to get the filtered barcodes
`/home/ubuntu/anaconda3/bin//R --no-save --no-restore --slave --args $bc_file $out_r $min_bc < /home/ubuntu/scwizard_pipeline/pre_process/r_sra_wrapper.R`;


#Open the filtered barcodes file and push them to an array
my $filter_bc = join("/",$wd,"filtered_bc.txt");
open($fh, "<", $filter_bc);
my @bc_filtered;

while(<$fh>){
	chomp $_;
	push @bc_filtered, $_;
}
close $fh;

#Open the FastQ file and filter out the low quality reads
open($infh, "<", $infile);
@lines=@null;
$count=0;
my $out=join("/",$wd,"clean_filtered.fastq");

say "running the main loop";
while(<$infh>){
        chomp $_;
        $count++;
        push @lines, $_;
        if($count == 4){
                $count=0;
                if($lines[0] =~ /(\@.+?)(\_)([ATGC]+)/){
                        $j= $3;
                        for($i=0; $i<scalar(@bc_filtered); $i++){if($j ~~ $bc_filtered[$i]){$process = join("\n",@lines); open $outFH, '>>:encoding(UTF-8)', $out; say $outFH $process;$process="";}}
                        @lines=@null;
                }
        }
}
