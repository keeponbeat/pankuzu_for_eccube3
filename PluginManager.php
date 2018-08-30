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
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{

    public function install($config, $app)
    {   
        // ブロックファイルをコピー
        $file = new Filesystem();
        $file->copy(__DIR__.'/Resource/template/default/Block/plg_pankuzu.twig', $app['config']['block_realdir'] . '/' . 'plg_pankuzu.twig');
        
        // dtb_blockに追加
        $DeviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_PC);
        $Block = $app['eccube.repository.block']->findOrCreate(null, $DeviceType);

        $Block->setName('ぱんクズリスト')
                ->setFileName('plg_pankuzu')
                ->setLogicFlg(Constant::ENABLED)
                ->setDeletableFlg(Constant::DISABLED);
        $em=$app['orm.em'];
        $em->persist($Block);
        $em->flush($Block);
    }

    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);

        // dtb_blobkからBlock削除
        $query = $app['orm.em']->createQueryBuilder()
                ->select("b")
                ->from("Eccube\\Entity\\Block","b")
                ->where('b.file_name = :filename')
                ->setParameter('filename', 'plg_pankuzu')
                ->getQuery();
        $Block = $query->getSingleResult();
        $app['orm.em']->remove($Block);
    }

    public function enable($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }

    public function disable($config, $app)
    {
    }

    public function update($config, $app)
    {
    }

}
