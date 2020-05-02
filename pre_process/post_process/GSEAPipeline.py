import gseapy
import os
import glob


#gmt_dir = sys.argv[1]

def run_gsea (gsea_output = os.getcwd() + "/gsea_output", rnk_files_dir = os.getcwd() + "/cluster_rnks/",
              gmt_file_dir = os.getcwd()+"/gsea_data/CLEANED_human.gmt"):
    single_cells = glob.glob1(rnk_files_dir, 'sc*')

    if len(single_cells) != 0:
        for i in single_cells:
            output_dir = gsea_output + "/" + i + "_gsea"
            os.mkdir(output_dir)
            gseapy.prerank(i, gmt_file_dir, output_dir)

    cluster_file_names = glob.glob1(rnk_files_dir, 'cluster*')
    final_ind_clust_list = len(cluster_file_names)

    for i in range(0, final_ind_clust_list):
        cluster_gsea_output_dir = gsea_output + "/" + str(i)
        cluster_rnk_loc = rnk_files_dir + "cluster_" + str(i) + ".rnk"
        os.mkdir(cluster_gsea_output_dir)
        gseapy.prerank(cluster_rnk_loc, gmt_file_dir, cluster_gsea_output_dir)
