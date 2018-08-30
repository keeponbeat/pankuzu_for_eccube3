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

namespace Plugin\Pankuzu\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class PankuzuServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
    	// Repository
        $app['pankuzu.repository.pankuzu_page_retationship'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Pankuzu\Entity\PankuzuPageRerationship');
        });
        $app['pankuzu.repository.pankuzu_item_detail_page_setting'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Pankuzu\Entity\PankuzuItemDetailPageSetting');
        });

        // Route
        $app->match('/' . $app["config"]["admin_route"] . '/content/pankuzu', '\\Plugin\\Pankuzu\\Controller\\PankuzuController::index')
            ->bind('admin_content_pankuzu');
        $app->match('/block/plg_pankuzu', 'Plugin\Pankuzu\Controller\PankuzuController::block')->bind('block_plg_pankuzu');

        // 管理画面のコンテンツ管理にメニューを追加
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $config['nav'][3]['child'][] = array(
                'id' => 'pankuzu',
                'name' => 'ぱんクズ管理',
                'url' => 'admin_content_pankuzu',
            );
            return $config;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}