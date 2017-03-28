<?php

namespace Targetbay\Tracking\Block;

class Tracking extends \Magento\Framework\View\Element\Template 
{
     public function __construct(\Magento\Framework\View\Element\Template\Context $context)
     {
	parent::__construct($context);
	$this->_isScopePrivate = true;
     }

    /**
     * Get block cache life time
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        return 0;
    }
}
