# topicanalysis
Topic analysis for scientific articles

These are the source files for doing topic analysis with the help of scientific search engines in the internet.
Topic analysis is to learn more about a knowledge field with the help of latent dirichlet
allocation (see https://cran.r-project.org/web/packages/lda/lda.pdf).
In order that latent dirichlet allocation can work many
scientific articles have to be fetched from search engines that have
to be chosen by oneself. The handling of these search engines is
adjustable with the help of handler in php-scripts. For more details
concerning handler in php-scripts see the information in
04_create_and_complete/topic_analysis_04_create_and_complete_treatment_01.php and in
04_create_and_complete/topic_analysis_04_create_and_complete_treatment_02.php. 
The lda must be manually executed with the help of R from https://cran.r-project.org after any
information is collected with the help of "13_execute_LDA".
The LDA-output is generated with the help of LDAvis from http://kennyshirley.com/LDAvis/#topic=10&lambda=1&term=. 
