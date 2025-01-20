<?php

declare(strict_types=1);

namespace Drupal\liebslog;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Link;


class GetPosts {
	protected $posts;
	protected $entityTypeManager;
		
	public function __construct(EntityTypeManagerInterface $entityTypeManager ) {
	
		$this->entityTypeManager = $entityTypeManager;

	
		$storage = $entityTypeManager->getStorage('node');
		$ids = $storage->getQuery()
			->condition('type', 'blog')
			->accessCheck('TRUE')
			->sort('created', 'DESC')
			->range(0, 10)
			->execute();

		foreach ($ids as $bid) {
		  $bname = Node::load($bid)->getTitle();
		  $this->posts[$bid] = $bname;
	
		}
	}
	
	/**
 	* {@inheritdoc}
   	*/
  	public static function create(ContainerInterface $container) {
    	return new static(      		
      		 $container->get('entity_type.manager')
    	);
  	}
	
	public function getPosts() {
		return $this->posts;
	}
	
	public function getPost($nid){
		$node =  Node::load($nid);		
		
		// Get the title
		$title = $node->getTitle();
		
		// Get the post summary
		// $view_builder = $this->entityTypeManager->getViewBuilder('node');
		// $teaser = $view_builder->view($node, 'teaser');
		//$teaser = $node->body->view('teaser');
		$teaser = $node->get('body')->summary;
		// Get the link
		$link  = Link::fromTextAndUrl("Read on Lieb's Log",
				$node->toUrl()->setAbsolute()				
				)->toString();

		$link = $node->toUrl()->setAbsolute()->toString();
		
		return [
			'title' => $title,
			'text'	=> $teaser,
			'link'  => $link,
			];

	}
	
}//End of clss
	
