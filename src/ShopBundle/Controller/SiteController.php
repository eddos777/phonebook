<?php

namespace ShopBundle\Controller;

use ShopBundle\Entity\Products;
use ShopBundle\Form\ProductForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dql = "SELECT p FROM ShopBundle:Products p";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('ShopBundle:Site:index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $product = new Products();

        $productForm = $this->createForm(
            new ProductForm(),
            $product
        );

        if ($request->isMethod('POST')) {
            $productForm->handleRequest($request);

            if ($productForm->isValid()) {
                $productData = $productForm->getData();

                $em->persist($productData);
                $em->flush();

                return $this->redirectToRoute("index", [], Response::HTTP_CREATED);
            }
        }


        
        return $this->render("ShopBundle:Site:create.html.twig", [
            'form' => $productForm->createView(),
        ]);


    }

    public function deleteAction(Request $request)
    {
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getManager();
        $product = $this->getDoctrine()
            ->getRepository('ShopBundle:Products')
            ->find($id);
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute("index", []);
    }


    public function updateAction(Request $request)
    {
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getManager();
        $product = $em
            ->getRepository('ShopBundle:Products')
            ->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }
        if ($request->isMethod('POST')) {
            $product->setName($request->request->get("name"));
            $product->setNumber($request->request->get("number"));
            $product->setAbout($request->request->get("about"));
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute("index", []);
        }
        return $this->render("ShopBundle:Site:update.html.twig", compact("product"));
    }

    public function viewAction(Request $request)
    {
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getManager();
        $product = $em
            ->getRepository('ShopBundle:Products')
            ->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }
        return $this->render("ShopBundle:Site:view.html.twig", compact("product"));
    }
}