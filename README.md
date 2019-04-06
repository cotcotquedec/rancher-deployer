# rancher-deployer
Docker image script to deploy an image into a rancher cluster.


# Environment variables

*RANCHER_URL* : Main url of the rancher installation

*RANCHER_CLUSTER* : Cluster id
*RANCHER_PROJECT* : Project id
*RANCHER_ACCESS_KEY* :  Rancher access key for API access 
*RANCHER_SECRET* : Rancher secret for API access

*IMAGE* : Main name of the new image
*TAG* : Tag to deploy for the image
*RANCHER_NAMESPACE* : Namespace to filter deployment

*CONDITION_LABEL* : if you want to deploy only matching label services
*CONDITION_VALUE* : value for the matching label
*VERSION_LABEL* : put new version into label (gonna add version value)