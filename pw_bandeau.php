<?php
/**
* PW bandeau
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    Profil Web
* @copyright Copyright 2025 ©profilweb All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @package   pw_homecategories
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class PW_Bandeau extends Module
{
    public function __construct()
    {
        $this->name = 'pw_bandeau';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Profil Web';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PW Bandeau');
        $this->description = $this->l('Module pour afficher un bandeau multilangue.');
    }

    // --- Installation ---
    public function install()
    {
        return parent::install() &&
               $this->registerHook(['displayHome']) &&
               $this->installConfig();
    }

    // --- Désinstallation ---
    public function uninstall()
    {
        return parent::uninstall() &&
               $this->uninstallConfig();
    }

    // --- Installation de la configuration ---
    protected function installConfig()
    {
        // 1. Crée l'entrée dans ps_configuration si elle n'existe pas
        if (!Configuration::get('PW_BANDEAU_TXT')) {
            Configuration::updateValue('PW_BANDEAU_TXT', ''); // Valeur vide, on utilise ps_configuration_lang
        }

        $id_config = (int)Configuration::getIdByName('PW_BANDEAU_TXT');

        // 2. Initialise les valeurs pour toutes les langues activées
        $languages = Language::getLanguages(true); // true = langues activées seulement
        foreach ($languages as $lang) {
            $lang_id = (int)$lang['id_lang'];

            // Vérifie si l'entrée existe déjà pour cette langue
            $exists = Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM `'._DB_PREFIX_.'configuration_lang`
                WHERE `id_configuration` = '. $id_config .'
                AND `id_lang` = '. $lang_id
            );

            if (!$exists) {
                // Insère une valeur par défaut (vide) pour chaque langue
                Db::getInstance()->insert('configuration_lang', [
                    'id_configuration' => $id_config,
                    'id_lang' => $lang_id,
                    'value' => '',
                ]);
            }
        }

        return true;
    }

    // --- Désinstallation de la configuration ---
    protected function uninstallConfig()
    {
        $id_config = (int)Configuration::getIdByName('PW_BANDEAU_TXT');

        if ($id_config) {
            // Supprime les entrées dans ps_configuration_lang
            Db::getInstance()->delete('configuration_lang', 'id_configuration = '. $id_config);

            // Supprime l'entrée dans ps_configuration
            return Configuration::deleteByName('PW_BANDEAU_TXT');
        }

        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPWBandeau')) {
            $this->postProcess();
            $output = $this->displayConfirmation($this->l('Settings updated successfully.'));
        }

        // 3. Affichage du formulaire
        return $output . $this->renderForm();
    }

    protected function postProcess()
    {
        $id_config = Configuration::getIdByName('PW_BANDEAU_TXT');
        if (!$id_config) {
            return false;
        }

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $lang_id = (int)$lang['id_lang'];
            $value = Tools::getValue('PW_BANDEAU_TXT_' . $lang_id);

            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'configuration_lang`
                (`id_configuration`, `id_lang`, `value`)
                VALUES (' . (int)$id_config . ', ' . (int)$lang_id . ', \'' . pSQL($value) . '\')
                ON DUPLICATE KEY UPDATE `value` = \'' . pSQL($value) . '\''
            );
        }

        return true;
    }

    protected function renderForm()
    {
        // 1. Définition des champs avec votre structure
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Bandeau Text Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Text'),
                        'name' => 'PW_BANDEAU_TXT',
                        'lang' => true,
                        'class' => 'lg',
                        'autoload_rte' => false, // Désactive l'éditeur riche si non nécessaire
                        'cols' => 60,
                        'rows' => 10,
                        'desc' => $this->l('Enter the text to display in the banner for each language.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitPWBandeau',
                ],
            ],
        ];

        // 2. Configuration de HelperForm (basée sur votre version)
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;

        // 3. Configuration des langues
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        // 4. Récupération des valeurs (version corrigée)
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(), // Méthode corrigée ci-dessous
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    protected function getConfigFieldsValues()
    {
        $values = [];
        $id_config = (int)Configuration::getIdByName('PW_BANDEAU_TXT');

        if ($id_config) {
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $lang_id = (int)$lang['id_lang'];
                $value = Db::getInstance()->getValue(
                    'SELECT `value` FROM `'._DB_PREFIX_.'configuration_lang`
                    WHERE `id_configuration` = '. $id_config .'
                    AND `id_lang` = '. $lang_id
                );

                // Format attendu par HelperForm pour les champs multilangues
                $values['PW_BANDEAU_TXT'][$lang_id] = $value;
            }
        }

        return $values;
    }

    public function hookDisplayHome($params)
    {
        $id_config = Configuration::getIdByName('PW_BANDEAU_TXT');
        if (!$id_config) {
            return '';
        }

        $id_lang = $this->context->language->id;
        $text = Db::getInstance()->getValue(
            'SELECT `value` FROM `'._DB_PREFIX_.'configuration_lang`
            WHERE `id_configuration` = ' . (int)$id_config . '
            AND `id_lang` = ' . (int)$id_lang
        );

        $this->context->smarty->assign([
            'bandeau_text' => $text,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/pw_bandeau.tpl');
    }
}
