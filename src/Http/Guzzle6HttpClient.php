<?php

namespace Algolia\AlgoliaSearch\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

final class Guzzle6HttpClient implements HttpClientInterface
{
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * Guzzle6HttpClient constructor.
     *
     * @param GuzzleClient|null $client
     */
    public function __construct(GuzzleClient $client = null)
    {
        $this->client = $client ?: static::buildClient();
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        try {
            $response = $this->client->send($request, array(
                'timeout' => $timeout,
                'connect_timeout' => $connectTimeout,
            ));
        } catch (GuzzleRequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse();
            }

            throw $e;
        }

        return $response;
    }

    /**
     * @param array $config
     *
     * @return GuzzleClient
     */
    private static function buildClient(array $config = array())
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(array('handler' => $handlerStack), $config);

        return new GuzzleClient($config);
    }
}
