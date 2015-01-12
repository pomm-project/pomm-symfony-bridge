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
    private $queries;

    public function __construct(Pomm $pomm)
    {
        $this->queries = [];
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
        if (!in_array($name, array('query:pre', 'query:post'))) {
            return;
        }

        if ('query:post' === $name) {
            end($this->queries);
            $key = key($this->queries);
            reset($this->queries);

            $this->queries[$key] += $data;

            return;
        }

        $this->queries[] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $time = 0;
        $querycount = 0;
        $queries = $this->queries;

        foreach ($queries as $query) {
            ++$querycount;
            if (isset($query['time_ms'])) {
                $time += $query['time_ms'];
            }
        }

        $this->data = compact('queries', 'querycount', 'time');

        if ($exception instanceof SqlException) {
            $this->data['exception'] = $exception->getMessage();
        }
        else {
            $this->data['exception'] = null;
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
        return $this->data['querycount'];
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
