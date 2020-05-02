#!/usr/bin/perl
#use strict;
use warnings;
use feature qw(say);
use utf8;
use Getopt::Long;

=cut

Program: Remove same nucleotide type barcode and delete the reads
Usage: Integrated into the SC Wizard pipeline
Version: 1.0

=cut


GetOptions ( 
              	"file=s"	=> 	\my $infile,    # path to the file
                "protocol=s" 	=>	\my $protocol,  #protocol used
                "bclen=i" 	=> 	\my $bc_len, 	#barcode length
		"out=s"	 	=>	\my $outfile,	#Output name
	)

  or die("Error in command line arguments\n");

chomp($outfile);


open(my $infh, "gunzip -c $infile |") or die;


my $count = 0;
my $line  = "";
my $out;
my @store;

unless($bc_len){my $bc_len;}

if($protocol eq "10x" || $protocol eq "10X"){
	$bc_len = 16;
	}
if($protocol eq "dropseq"){
	$bc_len = 8;
	}
if($protocol eq "celseq"){
	$bc_len = 12;
	}
if($protocol eq "SRA" || $protocol eq "fastq"){
	$bc_len = $bc_len;
	}

             
my(@A,@T,@G,@C);

for(my $i = 1; $i <= $bc_len; $i++){
	push @A,"A";
	push @T,"T";
	push @G,"G";
	push @C,"C";		

}

my $A = join("",@A);
my $T = join("",@T);
my $G = join("",@G);
my $C = join("",@C);

	
while(<$infh>){
	
	chomp $_;
	$count++;
	push @store, $_;
	if($count == 4){
		$line = join("\n", @store);

	if($store[0] =~ /($A)/ || $store[0] =~ /($T)/ || $store[0] =~ /($G)/ || $store[0] =~ /($C)/){
		$count = 0;
		($line, @store) = "";
        }

        else{
                open $out, '>>:encoding(UTF-8)', "$outfile";
                say $out $line;
                ($line, @store) = "";
                $count = 0;
                	}
        	}
	}


######### END OF PROGRAM #########


