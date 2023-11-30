<?php
/**
* PW Homecategories
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    Profil Web
* @copyright Copyright 2021 ©profilweb All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @package   pw_homecategories
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

Class Pw_Bandeau extends Module
{
    public function __construct()
    {
        $this->name = 'pw_bandeau';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Profil Web';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PW bandeau');
        $this->description = $this->l('Display running text non header');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() || !$this->registerHook('displayBanner')) {
            return false;
        }
 
        return true;
    }

    public function hookDisplayBanner()
    {

        $text = "mon texte bla bla";

        $this->context->smarty->assign([
            'text' => $text,
        ]);

        return $this->display(__FILE__, 'pw_bandeau.tpl');
    }
}
