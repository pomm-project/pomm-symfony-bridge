<?php

/*
 * This file is part of Pomm's SymfonyBidge package.
 *
 * (c) 2016 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\Configurator;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Pomm;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Data collector for the database profiler.
 *
 * @package PommSymfonyBridge
 * @copyright 2016 Grégoire HUBERT
 * @author Paris Mikael
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see DataCollector
 */
class DatabaseCollectorConfigurator
{
    public function __construct(protected DataCollector $datacollector)
    {
    }

    /**
     * @param Pomm $pomm
     * @return void
     * @throws FoundationException
     */
    public function configure(Pomm $pomm): void
    {
        $callable = [$this->datacollector, 'execute'];

        foreach ($pomm->getSessionBuilders() as $name => $builder) {
            $pomm->addPostConfiguration($name, function ($session) use ($callable) {
                $session
                    ->getClientUsingPooler('listener', 'query')
                    ->attachAction($callable)
                ;
            });
        }
    }
}
