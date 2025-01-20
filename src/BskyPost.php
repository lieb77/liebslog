<?php

declare(strict_types=1);

namespace Drupal\liebslog;

use Symfony\Component\DependencyInjection\ContainerInterface;
use potibm\Bluesky\Exception\HttpStatusCodeException;
use Drupal\bsky\PostServiceInterface;

class BskyPost {
	protected $bsky_connector;
		
	public function __construct(PostServiceInterface $bsky ) {
    	$this->bsky_connector = $bsky;
	}
	
	/**
 	* {@inheritdoc}
   	*/
  	public static function create(ContainerInterface $container) {
    	return new static(      		
      		$container->get('bsky.post_service')
    	);
  	}
	
	public function post($message, $link) {
		$post = $this->bsky_connector->createPost($message);
//		$this->bsky_connector->addFacets($post);
		$post = $this->bsky_connector->addCard($post, $link, "Lieb's Log", "Read the full post.");
				
		
		try {
    		$this->bsky_connector->sendPost($post);
    	} 
    	catch (HttpStatusCodeException $e) {
	 		return $e->getMessage();
	 	}
    	return FALSE;
	}	
	
}