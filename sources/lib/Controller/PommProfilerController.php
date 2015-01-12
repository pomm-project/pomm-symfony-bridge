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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use PommProject\Foundation\Pomm;

/**
 * PommProfilerController
 *
 * Controllers for the Pomm profiler extension.
 *
 * @package PommSymfonyBridge
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class PommProfilerController
{
    private $generator;
    private $profiler;
    private $twig;
    private $pomm;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $generator       The URL Generator
     * @param Profiler              $profiler        The profiler
     * @param \Twig_Environment     $twig            The twig environment
     * @param Pomm                  $pomm            The Pomm service
     */
    public function __construct(UrlGeneratorInterface $generator, Profiler $profiler, \Twig_Environment $twig, Pomm $pomm)
    {
        $this->generator = $generator;
        $this->profiler  = $profiler;
        $this->twig      = $twig;
        $this->pomm      = $pomm;
    }

    /**
     * explainAction
     *
     * Controller to explain a SQL query.
     *
     * @access public
     * @param  string   $token
     * @param  int      $index_query
     * @return Response
     */
    public function explainAction(Request $request, $token, $index_query)
    {
        $panel = 'pomm';
        $page  = 'home';

        if (!($profile = $this->profiler->loadProfile($token))) {
            return new Response($this->twig->render('@WebProfiler/Profiler/info.html.twig', array('about' => 'no_token', 'token' => $token)), 200, array('Content-Type' => 'text/html'));
        }

        $this->profiler->disable();

        if (!$profile->hasCollector($panel)) {
            throw new NotFoundHttpException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
        }

        if (!array_key_exists($index_query, $profile->getCollector($panel)->getQueries())) {
            throw new \InvalidArgumentException(sprintf("No such query index '%s'.", $index_query));
        }

        $query_data = $profile->getCollector($panel)->getQueries()[$index_query];

        $explain = $this->pomm[$query_data['session_stamp']]
            ->getClientUsingPooler('query_manager', null)
            ->query(sprintf("explain %s", $query_data['sql']), $query_data['parameters']);

        return new Response($this->twig->render('@Pomm/Profiler/explain.html.twig', array(
            'token' => $token,
            'profile' => $profile,
            'collector' => $profile->getCollector($panel),
            'panel' => $panel,
            'page' => $page,
            'request' => $request,
            'query_index' => $index_query,
            'explain' => $explain,
        )), 200, array('Content-Type' => 'text/html'));
    }
}
