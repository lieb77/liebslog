<?php

declare(strict_types=1);

namespace Drupal\liebs_log\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\liebslog\GetPosts;
use Drupal\liebslog\GetRides;


/**
 * Returns responses for Liebs log routes.
 */
final class LiebsLogController extends ControllerBase {

 /**
   * The controller constructor.
   */
  public function __construct(
    private LoggerChannelInterface $logger,
    private GetRides $getRides,
    private GetPosts $getPosts  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
          $container->get('logger.channel.liebslog'),
          $container->get('liebslog.getrides'),
          $container->get('liebslog.getblog')
      );
  }


  /**
   * Display rides as a table
   */
  public function ridelog(): array {
    $response = $this->getRides->rides();
    $rides    = $this->parse($response->data);

    return [
      '#type' => 'component',
      '#component' => 'liebslog:ridelog',
      '#props' => ['rides' => $rides],
    ];
  }

 /**
   * Display rides as blocks
   */
  public function rides(): array {
    $response = $this->getRides->rides();
    $rides    = $this->parse($response->data, true);

    return [
      '#type' => 'component',
      '#component' => 'liebslog:rides',
      '#props' => ['rides' => $rides],
    ];
  }


	/**
	 * Parse the results
	 */
	private function parse($data, $body = false) {

		foreach($data as $ride) {
			$result = [
				"title" => $ride->title,
				"bike"  => $ride->field_bike->title,
				"date"  => $ride->field_ridedate,
				"miles" => $ride->field_miles,
			];
			if($body) {
				$result["body"]  = $this->t($ride->body->value);
			}


			$rides[] = $result;
		}
		return $rides;
	}


}
