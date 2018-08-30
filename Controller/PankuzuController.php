<?php
/*
  * This file is part of the Pankuzu plugin
  *
  * Copyright (C) 2017 MURAKAMI INTERNATIONAL LLC. All Rights Reserved.
  *
  * Website: http://murakami-international.co.jp
  * E-Mail: info@murakami-international.co.jp
  * 
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Plugin\Pankuzu\Controller;

use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Entity\Master\DeviceType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Plugin\Pankuzu\Entity\PankuzuPageRerationship;
use Plugin\Pankuzu\Entity\PankuzuItemDetailPageSetting;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;


class PankuzuController {

    private $indexTwig = 'Pankuzu/Resource/template/admin/index.twig';

    /**
     * メイン
     */
    public function index(Application $app, Request $request)
    {

        $DeviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_PC);
        $PageLayouts = $app['eccube.repository.page_layout']->getPageList($DeviceType);

        //　新しいページがあれば、ぱんクズデータベースに追加
        foreach($PageLayouts as $PageLayout){
            if( is_null($app['pankuzu.repository.pankuzu_page_retationship']->find($PageLayout->getId()))){
                $NewPankuzuPageRerationship = new PankuzuPageRerationship();
                $NewPankuzuPageRerationship->setId($PageLayout->getId());
                $NewPankuzuPageRerationship->setPageLayout($PageLayout);
                $app['orm.em']->persist($NewPankuzuPageRerationship);
            }
        }
        $app['orm.em']->flush();
        $PankuzuPageRerationships = $app['pankuzu.repository.pankuzu_page_retationship']->findAll();

        if($app['pankuzu.repository.pankuzu_item_detail_page_setting']->find(1)){
            $PankuzuItemDetailPageSetting = $app['pankuzu.repository.pankuzu_item_detail_page_setting']->find(1);
        }else{
            $PankuzuItemDetailPageSetting = new PankuzuItemDetailPageSetting;
            $PankuzuItemDetailPageSetting->setId(1);
            $PankuzuItemDetailPageSetting->setMode('one');
            $app['orm.em']->persist($PankuzuItemDetailPageSetting);
            $app['orm.em']->flush();
        }

        // BuildForm
        $fb = $app['form.factory']->createBuilder();

        // GETの時だけフォームに初期値を与える。
        if ('GET' === $request->getMethod()) {
            $fb->add('item_detail_page_pankuzu_setting', 'choice', array(
                'choices' => array(
                    'one' => 'カテゴリIDが最も大きいもの　　',
                    'all' => '全所属カテゴリ',
                ),
                'multiple' => false,
                'expanded' => true,
                'data' =>  $PankuzuItemDetailPageSetting->getMode(),
            ));
        }else{
            $fb->add('item_detail_page_pankuzu_setting', 'choice', array(
                'choices' => array(
                    'one' => 'カテゴリIDが最も大きいもの　　',
                    'all' => '全所属カテゴリ',
                ),
                'multiple' => false,
                'expanded' => true,
            ));
        }

        foreach($PankuzuPageRerationships as $PankuzuPageRerationship){
        	if($PankuzuPageRerationship->getId()<=3) continue; // page_id 0~3は飛ばす。
        	
        	// GETの時だけフォームに初期値を与える。
        	if ('GET' === $request->getMethod()) {
        	    $fb->add((string)($PankuzuPageRerationship->getId()), 'integer', array(
        			'required' => false,
        			'attr' => array('style' => 'width: 60px'),
        			'constraints' => new GreaterThanOrEqual(1),
        			'data' => $PankuzuPageRerationship->getParentPageId(),
        		));	
        	}else{
        		$fb->add((string)($PankuzuPageRerationship->getId()), 'integer', array(
        			'required' => false,
        			'constraints' => new GreaterThanOrEqual(1),
        			'attr' => array('style' => 'width: 60px'),
        		));
        	}
        }

        $form = $fb->getForm();

  		// 更新ボタン押下時
        if ('POST' === $request->getMethod()) {

        	$form->handleRequest($request);
            
            if ($form->isValid()) {
            	$em = $app['orm.em'];
               	foreach($form as $key => $f ){

                    if($key == 'item_detail_page_pankuzu_setting'){
                        $PankuzuItemDetailPageSetting = new PankuzuItemDetailPageSetting;
                        $PankuzuItemDetailPageSetting->setId(1);
                        $PankuzuItemDetailPageSetting->setMode($f->getData());
                        $em->merge($PankuzuItemDetailPageSetting);
                        continue;
                    }

               		$PankuzuPageRerationship = $app['pankuzu.repository.pankuzu_page_retationship']->find($key);
               		$PankuzuPageRerationship->setParentPageId($f->getData());
			        $em->persist($PankuzuPageRerationship);
               	}
               	$em->flush();
                $app->addSuccess('ぱんクズ設定を保存しました', 'admin');
            }
        }

        return $app->render($this->indexTwig, array(
            'PankuzuPageRerationships' => $PankuzuPageRerationships,
            'form' => $form->createView(),
        ));

    }

    public function block(Application $app, Request $request)
    {
        $DeviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_PC);
        $MasterRequest = $app['request_stack']->getMasterRequest();
        $DisplayMode = $app['pankuzu.repository.pankuzu_item_detail_page_setting']->find(1)->getMode();
        $Category = null;
        $Product = null;
        $PageArray = array();
        
        // product_list pageの場合、テンプレートにCategoryを渡す
        if($MasterRequest->attributes->get('_route') == 'product_list' ){
            $category_id = $MasterRequest->query->get('category_id');
            if($category_id){
                $Category = $app['eccube.repository.category']->find($category_id);
            }else{
            	$Category = null;
            }

        // product_detailの場合、テンプレートにProductを渡す
        }elseif($MasterRequest->attributes->get('_route') == 'product_detail' ){

            $product_id = $MasterRequest->attributes->get('id');
            $Product = $app['eccube.repository.product']->find($product_id);

            if($app['pankuzu.repository.pankuzu_item_detail_page_setting']->find(1)->getMode()=='one'){
                $PrevCategoryId = 0;
                $CategoryIdMax = 0;

                foreach($Product->getProductCategories() as $ProductCategory){
                    if( $ProductCategory->getCategoryId() > $PrevCategoryId ) $CategoryIdMax = $ProductCategory->getCategoryId();
                    $PrevCategoryId = $ProductCategory->getCategoryId();
                }
                $Category = $app['eccube.repository.category']->find($CategoryIdMax);
            }

        // 通常ページの場合、テンプレートにPageArrayを渡す
        }else{
        	$CurrentPageLayout = $app['eccube.repository.page_layout']->getByUrl($DeviceType, $MasterRequest->attributes->get('_route'));
        	
        	do{
        		$PageArray[] = $CurrentPageLayout;
        		if( $id = $app['pankuzu.repository.pankuzu_page_retationship']->find($CurrentPageLayout->getId())->getParentPageId() ){
        			$CurrentPageLayout = $app['eccube.repository.page_layout']->find($id);
        		}else{
        			$CurrentPageLayout = null;
        		}
        	}while($CurrentPageLayout);

        	$PageArray = array_reverse($PageArray);
        }

        return $app->render('Block/plg_pankuzu.twig', array(
            'Category' => $Category,
            'Product' => $Product,
            'PageArray' => $PageArray,
            'DisplayMode' => $DisplayMode,
        ));
    }

}
