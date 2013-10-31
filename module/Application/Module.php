<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $sharedManager       = $eventManager->getSharedManager();
        $services            = $e->getApplication()->getServiceManager();
        
        // http://samsonasik.wordpress.com/2012/09/23/1675-using-logger-to-save-exception-in-zend-framework-2/
        $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
        		function($e) use ($services) {
        			if ($e->getParam('exception')) {
        				$services->get('Zend\Log\Logger')->crit($e->getParam('exception'));
        			}
        		}
        );
        
        $logger = $services->get('Zend\Log\Logger');
        // http://www.webconsults.eu/blog/entry/78-Error_Handling_for_Debugging_in_Zend_Framework_2
        register_shutdown_function(function () use ($logger)
        {
        	$e = error_get_last();
        	if ($e['type'] == 4) {
        		$logger->err($e['message'] . " in " . $e['file'] . ' line ' . $e['line']);
        		$logger->__destruct();
        	}
        });
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
