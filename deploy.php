<?php


namespace {

    require_once __DIR__ . '/vendor/autoload.php';
    require_once 'RancherDeployer.php';

    // GET CONFIG
    $rancher = getenv('RANCHER_URL');
    $cluster = getenv('RANCHER_CLUSTER');
    $project = getenv('RANCHER_PROJECT');
    $access_key = getenv('RANCHER_ACCESS_KEY');
    $secret = getenv('RANCHER_SECRET');

    // BASE
    $image = getenv('IMAGE');
    $tag = getenv('TAG');
    $namespace = getenv('RANCHER_NAMESPACE');

    // CONDITION
    $label_condition = getenv('CONDITION_LABEL');
    $label_condition_value = getenv('CONDITION_VALUE');
    $label_version = getenv('VERSION_LABEL');

    // GET RANCHER MODEL
    $rancher = new RancherDeployer($rancher, $cluster, $project, $access_key, $secret);

    // GET SERVICES
    $services = $rancher->getServices($namespace);

    // FILTER BY LABEL
    if ($label_condition && $label_condition_value) {
        $services = $services->filter(function ($service) use (&$label_condition, &$label_condition_value) {
            return data_get($service, 'labels.' . $label_condition) == $label_condition_value;
        });
    }

    echo 'Services found : ' . $services->count() . PHP_EOL;

    // UPDATE
    $services->each(function ($service) use (&$rancher, &$version, &$image, &$tag, &$label_version) {

        // IMAGES
        $containers = data_get($service, 'containers');

        // CONTAINERS
        foreach ($containers as &$container) {

            // IMAGE
            $current = data_get($container, 'image');

            // IMAGE GERER
            $matches = [];

            $pattern = sprintf('#^(?<image>%s):.+#', $image);

            if (!preg_match($pattern, $current, $matches)) {
                echo 'Images différentes : ' . $image . ' != ' . $current . PHP_EOL;
                continue;
            }

            // new image name
            data_set($container, 'image', data_get($matches, 'image') . ':' . $tag);
        }

        // SET NEW VERSION
        data_set($service, 'containers', $containers);
        empty($label_version) || data_set($service, 'labels.' . $label_version, $tag);

        // UPDATE
        $rancher->updateService($service);

        echo 'DEPLOY ' . data_get($service, 'name') . ' => ' . data_get($service, 'containers.0.image') . PHP_EOL;
    });

    exit(0);
}




