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

namespace Plugin\Pankuzu;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\Category;
use Eccube\Event\EventArgs;
use Plugin\KaitekiProductDetailExtension\Entity\Pankuzu;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class Event
{
	/** @var \Eccube\Application $app */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onAdminContentPageDeleteComplete(EventArgs $event)
    {
        $Request = $event->getRequest();
        $id = $Request->attributes->get('id');

        // データベースからデータを削除
        $PankuzuPageRerationship = $this->app['pankuzu.repository.pankuzu_page_retationship']->find($id);
        $em = $this->app['orm.em'];
        $em->remove($PankuzuPageRerationship);
        $em->flush();
    }
}