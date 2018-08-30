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

namespace Plugin\Pankuzu\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * Class PankuzuPageRerationship.
 */
class PankuzuPageRerationship extends AbstractEntity
{
    /**
     * @var integer
     */
	private $id;

    /**
     * @var integer
     */
	private $parent_page_id;

    /**
     * @var \Eccube\Entity\PageLayout
     */
	private $PageLayout;


	public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getPageLayout()
    {
        return $this->PageLayout;
    }

    public function setPageLayout($page_layout)
    {
        $this->PageLayout = $page_layout;

        return $this;
    }
    public function getParentPageId()
    {
        return $this->parent_page_id;
    }

    public function setParentPageId($parent_page_id)
    {
        $this->parent_page_id = $parent_page_id;

        return $this;
    }
}
