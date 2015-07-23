 
 
Magento to SugarCRM Bridge
===============

#### Export Magento customer information to SugarCRM
  <http://magento.com/><br>
  <http://www.sugarcrm.com/>

 * @author     Benton Snyder
 * @website    <http://www.bensnyde.me>
 * @created    7/23/2015
 * @updated    7/23/2015

# Workflow

Whenever an order is placed successfully, the following information is exported to SugarCRM.

1. An account is created from the specified Company or the customer's First and Last name if no company is specified
2. A contact is created under the above account with the customer's information
3. A campaign is created under the above account with the order details

You have the option of specifying a campaign to help track sales.
 
# Usage

1. Install plugin into Magento root directory
2. Enter SugarCRM information at
    *System->Configuration->Bs SugarBridge->SugarCRM*

