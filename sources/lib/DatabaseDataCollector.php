<?php
/*
 * This file is part of Pomm's SymfonyBidge package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PommProject\SymfonyBridge;

use PommProject\Foundation\Exception\SqlException;
use PommProject\Foundation\Listener\Listener;
use PommProject\Foundation\Session\Session;
use PommProject\Foundation\Pomm;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Data collector for the database profiler.
 *
 * @package PommSymfonyBridge
 * @copyright 2014 Grégoire HUBERT
 * @author Jérôme MACIAS
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see DataCollector
 */
class DatabaseDataCollector extends DataCollector
{
    public function __construct(Pomm $pomm, Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
        $this->data = [
            'time' => 0,
            'queries' => [],
            'exception' => null,
        ];

        $callable = [$this, 'execute'];

        foreach ($pomm->getSessionBuilders() as $name => $builder) {
            $pomm->addPostConfiguration($name, function($session) use ($callable) {
                $session
                    ->getClientUsingPooler('listener', 'query')
                    ->attachAction($callable)
                    ;
            });
        }
    }

    /**
     * @param string $name
     * @param array $data
     * @param $session
     *
     * @return null
     */
    public function execute($name, $data, Session $session)
    {
        switch ($name) {
            case 'query:post':
                $this->data['time'] += $data['time_ms'];
                $data += array_pop($this->data['queries']);
            case 'query:pre':
                $this->data['queries'][] = $data;
                break;
        }

        $this->watch($name);
    }

    private function watch($name)
    {
        if ($this->stopwatch !== null) {
            switch ($name) {
                case 'query:pre':
                    $this->stopwatch->start('query.pomm', 'pomm');
                    break;
                case 'query:post':
                    $this->stopwatch->stop('query.pomm');
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if ($exception instanceof SqlException) {
            $this->data['exception'] = $exception->getMessage();
        }
    }

    /**
     * Return the list of queries sent.
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }

    /**
     * Return the number of queries sent.
     *
     * @return integer
     */
    public function getQuerycount()
    {
        return count($this->data['queries']);
    }

    /**
     * Return queries total time.
     *
     * @return float
     */
    public function getTime()
    {
        return $this->data['time'];
    }

    /**
     * Return sql exception.
     *
     * @return PommProject\Foundation\Exception\SqlException|null
     */
    public function getException()
    {
        return $this->data['exception'];
    }

    /**
     * Return profiler identifier.
     *
     * @return string
     */
    public function getName()
    {
        return 'pomm';
    }
}
