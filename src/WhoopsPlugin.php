<?php
namespace OpenPress\Plugin\Whoops;

use DI\ContainerBuilder;
use OpenPress\Content\Loader;
use OpenPress\Content\Plugin;
use OpenPress\Config\Configuration;
use Psr\Container\ContainerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class WhoopsPlugin extends Plugin
{
    public function createContainer(ContainerBuilder $builder)
    {
        if (Configuration::get("debug")) {
            $builder->addDefinitions([
                "whoops" => function (ContainerInterface $c) {
                    $whoops = new \Whoops\Run();
                    $environment = $c->get('environment');

                    $prettyPageHandler = new PrettyPageHandler();

                    $prettyPageHandler->addDataTable('Slim Application', [
                        'Application Class' => get_class($this),
                        'Script Name'       => $environment->get('SCRIPT_NAME'),
                        'Request URI'       => $environment->get('PATH_INFO') ?: '<none>',
                    ]);

                    $plugins = $c->get(Loader::class)->getEnabledPlugins();
                    $pluginInformation = [];

                    foreach ($plugins as $name => $class) {
                        $pluginInformation[$name] = $class->getVersion();
                    }

                    $prettyPageHandler->addDataTable('Plugins Enabled', $pluginInformation);

                    $whoops->pushHandler($prettyPageHandler);
                    if (\Whoops\Util\Misc::isAjaxRequest()) {
                        $whoops->pushHandler(new JsonResponseHandler);
                    }
                    $whoops->register();

                    return $whoops;
                },

                "phpErrorHandler" => function (ContainerInterface $c) {
                    return new WhoopsErrorHandler($c->get("whoops"));
                },
                "errorHandler" => function (ContainerInterface $c) {
                    return new WhoopsErrorHandler($c->get("whoops"));
                }
            ]);
        }
    }

    public function load()
    {
        $this->container->get("whoops");
    }
}
