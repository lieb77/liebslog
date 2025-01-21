<?php

declare(strict_types=1);

namespace Drupal\liebslog\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\liebslog\BskyPost;
use Drupal\liebslog\GetPosts;

/**
 * Provides a Liebslog form.
 */
final class BskyPostForm extends FormBase
{

    protected $step = 1;
    protected $post_service;
    protected $bsky_service;
    protected $post;


    /* 
    * Instantiate our form class 
    * and load the services we need
    *
    */
    public function __construct(
        BskyPost $bsky_service,
        GetPosts $post_service 
    ) {
        
        $this->bsky_service = $bsky_service;
        $this->post_service = $post_service;
        
        $node = \Drupal::routeMatch()->getParameter('node');
        if (!empty($node) ) {
             $this->post = $this->post_service->getPost($node->Id());
             $this->step = 2;         
        }
        
        
    }
    
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        // Instantiates this form class.
        return new static(
            $container->get('liebslog.bsky_post'),
            $container->get('liebslog.get_posts')
        );
    }

    /*
     * {@inheritdoc}
     */
    public function getFormId(): string
    {
        return 'liebslog_bsky_post';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state): array
    {
             
        if ($this->step == 2) {
        
            $form['title'] = [
            '#type'     => 'textfield',
            '#title' => $this->t("Post title"),
            '#default_value' => $this->t($this->post['title']),
            ];
            
            $form['text'] =[
            '#type'      => 'textarea',
            '#title' => $this->t("Post summary"),
            '#default_value' => $this->t($this->post['text']),
            ];
            
            $form['link'] = [
            '#type'     => 'textfield',
            '#title' => $this->t("Post link"),
            '#default_value' => $this->t($this->post['link']),
            ];
            
            
            $form['actions'] = [
            '#type' => 'actions',
            'submit' => [
                '#type' => 'submit',
                '#value' => $this->t('Post this to Bluesky!'),
            ],
            ];
        }
        else {
        
            // Get list of the lastest blog posts         
            $posts = $this->post_service->getPosts();

            $form['select'] = [
            '#type' => 'select',
            '#title' => $this->t('Select post to share'),
            '#required' => true,
            '#options' => $posts,
            ];

            $form['actions'] = [
            '#type' => 'actions',
            'submit' => [
            '#type' => 'submit',
            '#value' => $this->t('Select'),
            ],
            ];
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state): void
    {
        
        if ($this->step == 2) {
            $message = $form_state->getValue('title') . "\n" .
               $form_state->getValue('text');
         
            if (mb_strlen($message) > 300) {
                $form_state->setErrorByName(
                    'text',
                    $this->t('Message is too long for Bluesky. Must be lass than 300 things.'),
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state): void
    {
    
        if ($this->step == 1 ) {
            // Get the selected post
            $this->post = $this->post_service->getPost($form_state->getValue('select'));
        
            $this->step = 2;                    
            $form_state->setRebuild();
        }
        else {                
            // Share post to Bluesky        
            
            $message = $form_state->getValue('title') . "\n" .
              $form_state->getValue('text');
            
            $link = $form_state->getValue('link');
            
            $err = $this->bsky_service->post($message, $link);
             
            if (false === $err) {        
                $this->messenger()->addStatus($this->t("The post has been shared."));
                $form_state->setRedirect('liebslog.blog');                         
            }            
            else {
                $this->messenger()->addStatus($this->t($err));
                $form_state->setRebuild();

            }
        }
    }    

}
