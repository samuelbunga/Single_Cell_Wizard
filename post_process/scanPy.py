import numpy as np
import pandas as pd
import scanpy as sc
import argparse
import gseapy
import logging
import os


def start_logging(outdir):
    logging.basicConfig(filename=outdir+'/'+'post_process.log', level=logging.INFO, format='%(levelname)s:%(message)s')

def get_input():
    parser = argparse.ArgumentParser()
    parser.add_argument('-i', '--infile', dest='infile', type=str)
    parser.add_argument('-o', '--outfile', dest='outdir', type=str)
    parser.add_argument('-sp', '--species', dest='species', type=str)
    parser.add_argument('-mc', '--min_cells', dest="min_cell", type=int)
    parser.add_argument('-mg', '--min_genes', dest="min_gene", type=int)
    _args = parser.parse_args()
    return _args


def sc_settings(outdir):
    sc.settings.verbosity = 3  # verbosity: errors (0), warnings (1), info (2), hints (3)
    sc.logging.print_versions()
    sc.settings.set_figure_params(dpi=80, facecolor='white')
    sc.settings.figdir = outdir
    sc.settings.autosave=True
    sc.logging.print_versions()


def read_swiz(infile):
    _outfile = sc.read(infile, delimiter=",", cache=True).transpose()
    return _outfile


def export_markers(adata, outdir):
    result = adata.uns['rank_genes_groups']
    groups = result['names'].dtype.names
    df = pd.DataFrame(
        {group + '_' + key[:1]: result[key][group]
         for group in groups for key in ['names', 'pvals']}).head(25)

    df.to_csv(outdir + '/' + 'top_25.csv', index=False)


def make_dir(outdir):
    try:
        os.mkdir(outdir)
        os.mkdir(outdir + '/rnk')
        os.mkdir(outdir + '/label_data')
    except FileExistsError:
        pass


def make_rnk(adata, outdir, species):
    result = adata.uns['rank_genes_groups']
    groups = result['names'].dtype.names
    count = 0
    top = []
    for group in groups:
        df = pd.DataFrame(
            {group + '_' + key[:1]: result[key.split("_")[0]][group]
             for key in ['names', 'pvals']}).head(25)
        rows = df[group + "_n"]
        rows = [name.split("_")[0] for name in rows]
        df[group + "_n"] = rows
        df[group + "_p"] = df[group + "_p"].astype('float')
        df.to_csv(outdir + '/rnk/' + str(count) + '.rnk', sep="\t", index=False, header=False)

        # Annotate each cluster
        try:
            gseapy.prerank(outdir + '/rnk/' + str(count) + '.rnk', '/home/ubuntu/scwizard_pipeline/post_process/Database/'+ species + ".gmt",
                           outdir + '/label_data/' + str(count) + '_folder')
        except:
            pass
        
        if os.path.isfile(outdir+'/label_data/'+str(count)+'_folder/gseapy.prerank.gene_sets.report.csv'):
            this_top = pd.read_csv(outdir+'/label_data/'+str(count)+'_folder/'+'gseapy.prerank.gene_sets.report.csv', header=0)
            label = this_top['Term'].iloc[-1]
            top.append(str(label))
        else:
            top.append("unknown" + str(count))
        count += 1
    return top


def check_species(species):
    if species == 'M':
        species = 'mouse'
    else:
        species = 'human'  
    return species


def get_markers(adata, n):
    """ 
    Get top n markers from each cluster 
    and append to list
    """
    df = pd.DataFrame(adata.uns['rank_genes_groups']['names']).head(n) 
    df_genes = list()
    for i in range(0, len(df.columns)):
        col=str(i)
        df_genes.append(df[col])
    top_n = list()
    for i in range(0,len(df_genes)):
        for j in range(0, n):
            top_n.append(df_genes[i][j])
    return top_n


if __name__ == '__main__':
    try:
        # Get user input
        ARGS = get_input()
    
        # Define default scanpy settings
        sc_settings(ARGS.outdir)

        # Initiate logging
        start_logging(ARGS.outdir)
    
        # Make rnk and label_data dir for gseapy
        make_dir(ARGS.outdir)

        # assigning values
        infile = ARGS.infile
        outdir = ARGS.outdir
        species = ARGS.species
        min_gene = ARGS.min_gene
        min_cell = ARGS.min_cell

        # Check species
        species = check_species(species)
        results_file = outdir+"/"+'output.h5ad' 

        # Read the Scwizard csv
        adata = read_swiz(ARGS.infile)
        adata.var_names_make_unique()

        # Show those genes that yield the highest fraction of counts
        # in each single cells, across all cells.
        sc.pl.highest_expr_genes(adata, n_top=20, )

        # Basic filtering
        sc.pp.filter_cells(adata, min_genes=min_gene)
        sc.pp.filter_genes(adata, min_cells=min_cell)

        # Calculate the percentage of mitochondrial genes
        adata.var['mt'] = adata.var_names.str.startswith('MT-')  # annotate the group of mitochondrial genes as 'mt'
        sc.pp.calculate_qc_metrics(adata, qc_vars=['mt'], percent_top=None, log1p=False, inplace=True)

        # Violin plot of computed quality metrics
        sc.pl.violin(adata, ['n_genes_by_counts', 'total_counts', 'pct_counts_mt'],
                     jitter=0.4, multi_panel=True)
        sc.pl.scatter(adata, x='total_counts', y='pct_counts_mt')
        sc.pl.scatter(adata, x='total_counts', y='n_genes_by_counts')

        # Filtering by slicing the anndata
        adata = adata[adata.obs.n_genes_by_counts < 6500, :]
        adata = adata[adata.obs.pct_counts_mt < 5, :]

        # Normalize
        sc.pp.normalize_total(adata, target_sum=1e4)

        # Logarithmize the data
        sc.pp.log1p(adata)

        # Identify highly variable genes
        sc.pp.highly_variable_genes(adata, min_mean=0.0125, max_mean=3, min_disp=0.5)
        sc.pl.highly_variable_genes(adata)

        # Saving the raw object
        adata.raw = adata
     
        # Regress out effects of total counts per cell and the percentage of mitochondrial genes expressed.
        # Scale the data to unit variance.
        sc.pp.regress_out(adata, ['total_counts', 'pct_counts_mt'])

        # Scale each gene to unit variance. Clip values exceeding standard deviation 10.
        sc.pp.scale(adata, max_value=10)

        # Principle component analysis
        sc.tl.pca(adata, svd_solver='arpack', n_comps=40)

        # Scatterplot of PCA
        sc.pl.pca(adata)

        # Estimation of each PC
        sc.pl.pca_variance_ratio(adata, log=True)

        # Save results
        adata.write(results_file, compression='gzip')

        # Compute the neighborhood graph
        sc.pp.neighbors(adata, n_neighbors=10, n_pcs=10)

        # Embedding the neighbouring graph
        '''
        tl.paga(adata)
        pl.paga(adata, plot=False)  # remove `plot=False` if you want to see the coarse-grained graph
        tl.umap(adata, init_pos='paga')
        '''

        # Computing UMAP
        sc.tl.umap(adata)

        # Featureplots
        # sc.pl.umap(adata, color=['Zic5_ENSMUSG00000041703'])

        # Clustering the neighbourhood graph
        sc.tl.leiden(adata)
        # sc.pl.umap(adata, color=['leiden', 'Zic5_ENSMUSG00000041703'])

        # Finding marker genes
        sc.tl.rank_genes_groups(adata, 'leiden', method='t-test')
        sc.pl.rank_genes_groups(adata, n_genes=25, sharey=False)

        # Compare to single cluster (0 vs 1)
        # sc.tl.rank_genes_groups(adata, 'leiden', groups=['0'], reference='1', method='wilcoxon')
        # sc.pl.rank_genes_groups(adata, groups=['0'], n_genes=20)

        # Export top 25 markers from each cluster to csv
        export_markers(adata, outdir)
    
        print(adata.uns['rank_genes_groups']['names'].dtype.names)
    
        # Create ranks for each cluster
        cell_types = make_rnk(adata, outdir, species)

        # Get top markers
        marker_genes = get_markers(adata, 5)

        adata.rename_categories('leiden', cell_types)
        sc.pl.umap(adata, color='leiden', size = 12)       
        sc.pl.umap(adata, color='leiden', legend_loc='on data', legend_fontsize=8, save='_cluster_labelled.pdf',size=12)
        sc.pl.rank_genes_groups_matrixplot(adata, n_genes=3, standard_scale='var', cmap='Blues', save='_rank_groups.pdf')
        sc.pl.rank_genes_groups_heatmap(adata, n_genes=3, standard_scale='var', cmap='brg', swap_axes='True', save='_rank_groups.pdf')
        sc.pl.heatmap(adata, marker_genes, groupby='leiden', swap_axes=True, show_gene_labels=True, save="_top_markers.pdf")
        sc.pl.dotplot(adata, marker_genes, groupby='leiden', save="_top_markers.pdf")
        sc.pl.stacked_violin(adata, marker_genes, groupby='leiden', save="_top_markers.pdf")

        # Save the result
        adata.write(results_file)

    except:
        logging.exception('Somthing went wrong. Could not process further. Please review the error and try again')

