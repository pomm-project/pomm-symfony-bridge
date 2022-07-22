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
use PommProject\Foundation\Session\Session;

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
    public function __construct(private readonly ?Stopwatch $stopwatch = null)
    {
        $this->resetData();
    }

    /**
     * @param string $name
     * @param array $data
     * @param Session $session
     *
     * @return void
     */
    public function execute(string $name, array $data, Session $session): void
    {
        switch ($name) {
            case 'query:post':
                $this->data['time'] += (float) $data['time_ms'];
                $data += array_pop($this->data['queries']);
                /* fall-through */
            case 'query:pre':
                $this->data['queries'][] = $data;
                break;
        }

        $this->watch($name);
    }

    private function watch(string $name): void
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
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
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
    public function getQueries(): array
    {
        return $this->data['queries'];
    }

    /**
     * Return the number of queries sent.
     *
     * @return integer
     */
    public function getQuerycount(): int
    {
        return is_countable($this->data['queries']) ? count($this->data['queries']) : 0;
    }

    /**
     * Return queries total time.
     *
     * @return float
     */
    public function getTime(): float
    {
        return $this->data['time'];
    }

    /**
     * Return sql exception.
     *
     * @return SqlException|null
     */
    public function getException(): ?SqlException
    {
        return $this->data['exception'];
    }

    /**
     * Return profiler identifier.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'pomm';
    }

    private function resetData(): void
    {
        $this->data = [
            'time' => 0,
            'queries' => [],
            'exception' => null,
        ];
    }

    public function reset(): void
    {
        $this->stopwatch?->reset();
        $this->resetData();
    }
}
