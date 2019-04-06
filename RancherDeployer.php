<?php

namespace {

    use GuzzleHttp\Client;


    class RancherDeployer
    {


        /**
         * @var string Rancher Instance URL
         */
        protected $url;

        /**
         * @var string Authentification token for Rancher API
         */
        protected $token;

        /**
         * RancherDeployer constructor.
         *
         */
        public function __construct($rancher, $cluster, $project, $access_key, $secret)
        {
            // BUILD WORKLOAD URL
            $this->url = sprintf('%s/v3/projects/%s:%s/workloads', $rancher, $cluster, $project);

            // BUILD AUTH TOKEN
            $this->token = base64_encode(sprintf('%s:%s', $access_key, $secret));
        }

        /**
         * @return Client
         */
        public function client()
        {
            return new Client([
                'headers' => [
                    'Authorization' => 'Basic ' . $this->token,
                    'Content-Type' => 'application/json',
                ]
            ]);
        }


        /**
         * Get services from project / namespace
         *
         * @param null $namespace
         */
        public function getServices($namespace = null)
        {

            // QUERY
            $query = [];
            if ($namespace) {
                data_set($query, 'namespaceId', $namespace);
            }

            // CALL
            $response = $this->client()->get($this->url, compact('query'));

            // SI REPONSE ERREUR
            if ($response->getStatusCode() != 200) {
                throw new \Exception('Une erreur est survenue sur la récupération des services');
            }

            // Traitement réponse
            $response = $response->getBody()->getContents();

            // SERVICES
            $services = collect(data_get(json_decode($response), 'data'));

            return $services;
        }

        /**
         * Update service
         *
         * @param $url
         * @param $service
         * @return $this
         * @throws Exception
         */
        public function updateService($service)
        {

            // URL
            $url = data_get($service, 'links.update');
            throw_if(empty($url), new \Exception('Impossible de toruver l\'url pour update le service : ' . data_get($service, 'name')));

            // POST
            $response = $this->client()->put($url, [
                'json' => $service
            ]);

            // SI REPONSE ERREUR
            throw_unless(
                $response->getStatusCode() == 200,
                new \Exception('Une erreur est survenue dans l\'update du service')
            );

            return $this;
        }
    }
}