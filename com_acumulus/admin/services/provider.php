<?php

declare(strict_types=1);

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Siel\Joomla\Component\Acumulus\Administrator\Extension\AcumulusComponent;

/**
 * The Acumulus service provider.
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\Siel\\Joomla\\Component\\Acumulus'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Siel\\Joomla\\Component\\Acumulus'));
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                return new AcumulusComponent($container->get(ComponentDispatcherFactoryInterface::class));
            }
        );
    }
};
