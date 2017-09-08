# topicanalysis
Topic analysis with the help of scientific articles and latent dirichlet allocation

These are the source files for doing topic analysis with the help of scientific search
engines from web search engines in the internet and latent dirichlet allocation.
Topic analysis is to learn more about a category that is part of a scientific field or
another knowledge field with the help of latent dirichlet allocation.
The prerequisite of latent dirichlet allocation in this tool is
R from https://cran.r-project.org and latent dirichlet allocation package for R which
is described in https://cran.r-project.org/web/packages/lda/lda.pdf.
In order that latent dirichlet allocation can return good results sources like
scientific articles scientific web search engines must be fetched in advance and saved
in table "search_results" for a topic analysis of a particular knowledge category.
Any web search engine can be used to fetch sources. However these web search engines
must be written in php and must be referenced from the "filling-procedure" in
04_fill_and_complete/topic_analysis_04_fill_and_complete_treatment_01.php and
referenced from the "completing-procedure" in
04_fill_and_complete/topic_analysis_04_fill_and_complete_treatment_02.php.
"filling-procedure" is the reference for the handler of one web search engine that
begins to fill table "search_results" with search results (search results are values
for the fetched sources from web search engines like value for authors, title, year
of publication and conference of scientific articles). Because many attributes like
abstracttext and fulltext can be filled by just one web search engine the
"completing-handler" references handler to web search engines that complete these sources'
attributes with values fetched from predefined web search engines for one or more than one
"completing-handler". If enough sources in the column "abstracttext" and "fulltext"
are available the exclusion procedure must be executed because some search results may
be wrong. Then preprocessing and optimizing for latent dirichlet allocation is necessary.
Then latent dirichlet allocation must be manually executed with the help of "R"
from https://cran.r-project.org. The output of latent dirichlet allocation R must be
generated with the help of LDAvis from http://kennyshirley.com/LDAvis/. The output can
be referenced to another webpage. 
