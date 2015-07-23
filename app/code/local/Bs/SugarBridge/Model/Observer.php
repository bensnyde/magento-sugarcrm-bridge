<?php

require_once(Mage::getBaseDir('lib') . '/Bs/sugarcrm.php');

class Bs_SugarBridge_Model_Observer extends Varien_Event_Observer
{
    public function export(Varien_Event_Observer $observer)
    {
        $config['enabled'] = Mage::getStoreConfig('bs/general/enabled',Mage::app()->getStore());
        $config['email'] = Mage::getStoreConfig('bs/general/email',Mage::app()->getStore());
        $config['url'] = Mage::getStoreConfig('bs/general/url',Mage::app()->getStore());
        $config['username'] = Mage::getStoreConfig('bs/general/username',Mage::app()->getStore());
        $config['password'] = Mage::getStoreConfig('bs/general/password',Mage::app()->getStore());
        $config['campaigns'] = Mage::getStoreConfig('bs/general/campaigns',Mage::app()->getStore());

        if(!$config['enabled']) {
            return true;
        }

        $order = $observer->getEvent()->getOrder();

        $order_data = $order->getData();
        $billing_data = $order->getBillingAddress()->getData();
        $shipping_data = $order->getShippingAddress()->getData();


        $sugar = new SugarCRM($config['url'], $config['username'], $config['password']);
        $user_id = $sugar->getUserID();

        $company_name = ($order_data['company'] ? $order_data['company'] : $order_data['customer_firstname'] . ' ' . $order_data['customer_lastname']);
        $account = array(
            'name' => $company_name,
            'campaign_id' => $config['campaigns'],
            'created_by' => $user_id,
            'account_type' => 'Customer',
        );
        $account_id = $sugar->setEntry('Accounts', $account);

        $contact = array(
            'first_name' => $order_data['customer_firstname'],
            'last_name' => $order_data['customer_lastname'],
            'email' => $order_data['customer_email'],
            'phone_office' => $order_data['telephone'],
            'phone_fax' => $order_data['fax'],
            'primary_address_street' => $shipping_data['street'],
            'primary_address_city' => $shipping_data['city'],
            'primary_address_state' => $shipping_data['region'],
            'primary_address_postalcode' => $shipping_data['postcode'],
            'primary_address_country' => $shipping_data['country_id'],
            'alt_address_street' => $billing_data['street'],
            'alt_address_city' => $billing_data['city'],
            'alt_address_state' => $billing_data['region'],
            'alt_address_postalcode' => $billing_data['postcode'],
            'alt_address_country' => $billing_data['country_id'],
            'created_by' => $user_id,
            'lead_source' => $order_data['store_name'],
            'campaign_id' => $config['campaigns'],
            'account_id' => $account_id
        );
        $contact_id = $sugar->setEntry('Contacts', $contact);

        $opportunity = array(
            'name' => $company_name . 'Online Order #' . $order_data['quote_id'],
            'amount' => $order_data['grand_total'],
            'sales_stage' => "Closed Won",
            'account_id' => $account_id,
            'lead_source' => $order_data['store_name'],
            'created_by' => $user_id,
            'date_closed' => $order_data['created_at']
        );
        $opportunity_id = $sugar->setEntry('Opportunities', $opportunity);

        if(isset($config['email']) && filter_var($config['email'], FILTER_VALIDATE_EMAIL))
        {
            $message = 'Order:' . json_encode($order_data) . 'Billing Address:' . json_encode($billing_data) . 'Shipping Address:' . json_encode($shipping_data);
            $to = $config['email'];
            $subject = "Magento - New Order!";
            $header = "From:sugarbridge@bensnyde.me \r\n";
            mail($to,$subject,$message,$header);
        }

        return true;
    }
}
