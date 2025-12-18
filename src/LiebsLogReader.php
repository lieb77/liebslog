<?php

declare(strict_types=1);

namespace Drupal\liebs_log;

use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\ClientInterface;

/**
 * @todo Add a description for the middleware.
 */
class LiebsLogReader {

  /**
   * Constructs a LiebsLogReader object.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly LoggerChannelInterface $loggerChannelDefault,
  ) {}

   /**
    *
    */
    public function readLog() {
        $request = $this->httpClient->get("https://paullieberman.org/jsonapi/node/ride",
            [
            'headers' => [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            ],
            'query' => "sort=-created&include=field_bike",
            ]
        );

        if ($request->getStatusCode() == 200) {
            return json_decode($request->getBody()->getContents());
        }
        return false;
    }

}
