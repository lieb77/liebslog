{{{<?php

declare(strict_types=1);

namespace Drupal\liebslog;
<?php

declare(strict_types=1);

namespace Drupal\liebslog;

use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\ClientInterface;

/**
* @todo Add a description for the middleware.
*/
class GetPosts {

    /**
    * Constructs a LiebsLogReader object.
    */
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly LoggerChannelInterface $logger,
        ) {}

    /**
    *
    */
    public function posts() {
        $request = $this->httpClient->get("https://paullieberman.org/jsonapi/node/blog",
            [
                'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
                'query' => "sort=-created",
            ]
        );

        if ($request->getStatusCode() == 200) {
            return json_decode($request->getBody()->getContents());
        }
        return false;
    }

}


}//End of clss

}}}
