<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends Controller {

	public function add(Request $request) {
		$post = new Post;
		$slugify = new Slugify;
		//===============================================================================
		$form = $this->createForm(PostType::class, $post);
		$form->handleRequest($request);
		//===============================================================================
		$validator = $this->get('validator');
		$errors = $validator->validate($post);
		//===============================================================================
		if ($form->isSubmitted() && $form->isValid()) {
			//$post->setPublishedAt($post->getPublishedAt());
			$post->setSlug($slugify->slugify($post->getTitle()));
			//===============================================================================
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($post);
			$entityManager->flush();

			return $this->redirectToRoute('blog');
		}

		return $this->render('admin/blog/add.html.twig', array('form' => $form->createView()));
	}

	public function edit(Request $request, $id) {

		$slugify = new Slugify;

		$post = $this->getDoctrine()->getRepository(Post::class)->find($id);

		$form = $this->createForm(PostType::class, $post);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$post->setSlug($slugify->slugify($post->getTitle()));
			$entityManager = $this->getDoctrine()->getManager();

			$entityManager->flush();
		}

		return $this->render('admin/blog/edit.html.twig', ['post' => $post, 'form' => $form->createView()]);
	}

	public function list(Request $request) {
		$repository = $this->getDoctrine()->getRepository(Post::class);
		$query = $repository->createQueryBuilder('p')
			->select(['p.title', 'p.slug', 'p.id', 'p.shortcontent', 'p.publishedAt'])
			->orderBy('p.id', 'ASC');

		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
			$query, /* query NOT result */
			$request->query->getInt('page', 1) /*page number*/,
			10/*limit per page*/
		);
		return $this->render('admin/blog/list.html.twig', array('pagination' => $pagination));

	}

}
