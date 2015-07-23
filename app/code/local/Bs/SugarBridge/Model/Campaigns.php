<?php

require_once(Mage::getBaseDir('lib') . '/Bs/sugarcrm.php');

class Bs_SugarBridge_Model_Campaigns
{
    public function toOptionArray()
    {
        $result = array(array('value'=>null, 'label'=>Mage::helper('sugarbridge')->__('None')));

        $config['enabled'] = Mage::getStoreConfig('bs/general/enabled',Mage::app()->getStore());

	if($config['enabled']) {
            $config['url'] = Mage::getStoreConfig('bs/general/url',Mage::app()->getStore());
            $config['username'] = Mage::getStoreConfig('bs/general/username',Mage::app()->getStore());
            $config['password'] = Mage::getStoreConfig('bs/general/password',Mage::app()->getStore());

            $sugar = new SugarCRM($config['url'], $config['username'], $config['password']);

            $campaigns = $sugar->getEntryList('Campaigns');

            for($x=0; $x<count($campaigns); $x++) {
                foreach($campaigns->entry_list[$x]->name_value_list as $value) {
                    if($value->name == 'name') {
                        $campaign_name = $value->value;
                    }
                    elseif($value->name == 'id') {
                        $campaign_id = $value->value;
                    }
                }

                array_push($result, array('value'=>$campaign_id, 'label'=>Mage::helper('sugarbridge')->__($campaign_name . ' (' . $campaign_id . ')')));
            }
        }

        return $result;
    }
}
