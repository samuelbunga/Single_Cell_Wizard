import argparse
import time


def get_job_id():
    """ using current time create a unique job ID """
    job_id = str(time.time()).split(".")[0]
    return job_id


def filter_args(ARGS_hash):
    """ filters empty arguments and returns a dictionary with real values """
    filtered_map = {}
    for each_key in ARGS_hash:
        if not ARGS_hash[each_key] or ARGS_hash[each_key] =='None':
            pass
        else:
            filtered_map[each_key] = ARGS_hash[each_key]
    return filtered_map


def write_to_file(outfile, filtered_args):
    OutFile = open(outfile, 'a+')
    key_values = []
    for i in filtered_args:
        #append keys
        key_values.append(i)
        #append values
        key_values.append(filtered_args[i])
    key_values = " ".join(key_values)
    OutFile.write(key_values+"\n")


if __name__ == '__main__':
    PARSER = argparse.ArgumentParser(description='pass the options values from scwizard to append them into the '
                                                 'database')
    PARSER.add_argument('-i', '--infiles', dest='infiles', help='provide path to the input fastq files', required=True)
    PARSER.add_argument('-o', '--output', dest='output_dir', help='provide path to the output directory', required=True)
    PARSER.add_argument('-id', '--job-name', dest='job_name', help='provide a job name', required=True)
    PARSER.add_argument('-e', '--email', dest='email', help='provide your email', required=True)
    PARSER.add_argument('-sp', '--species', dest='species', help='provide a species name (mouse or human)', required=True)
    PARSER.add_argument('-p', '--protocol', dest='protocol', help='provide a protocol of the experiment', required=True)
    PARSER.add_argument('-a', '--aligner', dest='aligner', help='provide a aligner for the analysis', required=False)
    PARSER.add_argument('-qc', '--quality-control', dest='qc', help='select an option to weather perform qc or not',
                        required=False)
    PARSER.add_argument('-ss', '--sample-size', dest='sample_size', help='provide an estimation of sample size',
                        required=True)
    PARSER.add_argument('-pp', '--post-process', dest='post_process', help='option to skip post-process', required=False)
    PARSER.add_argument('-bc', '--barcode', dest='barcode', help='provide barcode length for single-end fastqs',
                        required=False)
    PARSER.add_argument('-umi', '--umi-len', dest='umi', help='provide UMI length for single-end fastqs', required=False)
    PARSER.add_argument('-min_cells', '--min-cells', dest='min_cells', help='provide min cells to be filtered during '
                                                                            'post-process', required=False)
    PARSER.add_argument('-min_genes', '--min-gene', dest='min_genes', help='provide min gene to be filtered during '
                                                                          'post-process', required=False)
    ARGS = PARSER.parse_args()
    job_id = get_job_id()
    ARGS_HASH = {
        '-job': job_id,
        '-id': ARGS.job_name,
        '-out': ARGS.output_dir,
        '-file': ARGS.infiles,
        '-email': ARGS.email,
        '-sp': ARGS.species,
        '-protocol': ARGS.protocol,
        '-aligner': ARGS.aligner,
        '-qc': ARGS.qc,
        '-sample_size': ARGS.sample_size,
        '-post_process': ARGS.post_process,
        '-bclen': ARGS.barcode,
        '-umi': ARGS.umi,
        '-min_cells': ARGS.min_cells,
        '-min_genes':  ARGS.min_genes,
    }
    FILTERED_ARGS = filter_args(ARGS_HASH)
    write_to_file('/home/ubuntu/pre_process/database/database.txt', FILTERED_ARGS)
