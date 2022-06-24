<?php
/*
 * This file is part of Pomm's SymfonyBidge package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\SymfonyBridge\Controller;

use PommProject\Foundation\QueryManager\QueryManagerClient;
use PommProject\SymfonyBridge\DatabaseDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use PommProject\Foundation\Pomm;
use Twig\Environment;

/**
 * Controllers for the Pomm profiler extension.
 *
 * @package PommSymfonyBridge
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class PommProfilerController
{
    public function __construct(private readonly Profiler $profiler, private readonly Environment $twig, private readonly Pomm $pomm)
    {
    }

    /**
     * Controller to explain a SQL query.
     *
     * @param Request $request
     * @param string $token
     * @param int $index_query
     *
     * @return Response
     */
    public function explainAction(Request $request, string $token, int $index_query): Response
    {
        $panel = 'pomm';
        $page  = 'home';

        if (!($profile = $this->profiler->loadProfile($token))) {
            return new Response(
                $this->twig->render(
                    '@WebProfiler/Profiler/info.html.twig',
                    array('about' => 'no_token', 'token' => $token)
                ),
                200,
                array('Content-Type' => 'text/html')
            );
        }

        $this->profiler->disable();

        if (!$profile->hasCollector($panel)) {
            throw new NotFoundHttpException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
        }

        /** @var DatabaseDataCollector $databaseDataCollector */
        $databaseDataCollector = $profile->getCollector($panel);

        if (!array_key_exists($index_query, $databaseDataCollector->getQueries())) {
            throw new \InvalidArgumentException(sprintf("No such query index '%s'.", $index_query));
        }

        $query_data = $databaseDataCollector->getQueries()[$index_query];

        /** @var QueryManagerClient $queryManager */
        $queryManager = $this->pomm[$query_data['session_stamp']]
            ->getClientUsingPooler('query_manager', null);

        $explain = $queryManager->query(sprintf("explain %s", $query_data['sql']), $query_data['parameters']);

        return new Response($this->twig->render('@Pomm/Profiler/explain.html.twig', array(
            'token' => $token,
            'profile' => $profile,
            'collector' => $databaseDataCollector,
            'panel' => $panel,
            'page' => $page,
            'request' => $request,
            'query_index' => $index_query,
            'explain' => $explain,
        )), 200, array('Content-Type' => 'text/html'));
    }
}
