<?php
 
    if (!defined('_PS_VERSION_')) {
        exit;
    }

class CodePromoFooter extends Module {
    public function __construct()
    {
        $this->name = 'codepromofooter';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Clément Duhin';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;
 
        parent::__construct();
 
        $this->displayName = $this->l("Code promo en bas de page");
        $this->description = $this->l("Ajoute un texte de code promo personnalisable et le code promo saisi créé dans votre back office");
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ?');
 
        if (!Configuration::get('TESTCREATE_PAGENAME')) {
            $this->warning = $this->l('Aucun nom fourni');
        }
    }

        public function install()
    {
        if(Shop::isFeatureActive()) {
            return Shop::setContext(Shop::CONTEXT_ALL);
        }
        return parent::install();
        $this->registerHook('displayHome')&&
        Configuration::updateValue('TEXTPROMO')&&
        Configuration::updateValue('CODEPROMO');
    }
 
    public function uninstall()
    {
        return parent::uninstall()&&
        Configuration::deleteByName('TEXTPROMO')&&
        Configuration::deleteByName('CODEPROMO');
    }

    public function getContent()
    {
        $output=null;
        if(Tools::isSubmit('submit' . $this->name)){
            $textpromo = Tools::getValue('TEXTPROMO');
            $codepromo = Tools::getValue('CODEPROMO');
            if(!$textpromo || empty($textpromo) || !Validate::isGenericName($textpromo) || !$codepromo || empty($codepromo) || !Validate::isGenericName($codepromo)) {
                $output.=$this->displayError($this->l("configuration failed"));
            } else {
                Configuration::updateValue('TEXTPROMO', $textpromo);
                Configuration::updateValue('CODEPROMO', $codepromo);
                $output.=$this->displayConfirmation($this->l("Update successful"));
            }
        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        $fields_form = array();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Code promo settings'),
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Text of code promo'),
                    'name' => 'TEXTPROMO',
                    'size' => 20,
                    'required' => true
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Your code promo'),
                    'name' => 'CODEPROMO',
                    'size' => 10,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default'
            )
        );
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
        'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
        'back' => array(
            'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
        );
        $helper->tpl_vars = array(
            'fields_value' => array(
                'TEXTPROMO' => Configuration::get('TEXTPROMO'),
                'CODEPROMO' => Configuration::get('CODEPROMO'),
            ),
        );
        return $helper->generateForm($fields_form);
    }
}