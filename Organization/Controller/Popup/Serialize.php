<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/
namespace Aha\Organization\Controller\Popup;

use Aha\Organization\Controller\Organization;

class Serialize extends Organization
{
    
    public function execute() {
        if($this->getRequest()->isPost()){
            $orgData = $this->customerSession->getOrgData();
            $data = $this->getCleanTag($this->getRequest()->getParams());
            if($orgData){
                $this->customerSession->setOrgData(array_merge($orgData, $data));
            } else {
                $this->customerSession->setOrgData($data);
            }
        }
        return;
    }

    /*
    @input Array
    Adding strip tags for removing HTML code in Textbox entry
    */
    public function getCleanTag($tag){
        if (!array_key_exists("org_name",$tag)){
            $tag['billing_to'] = strip_tags($tag['billing_to']);
            $tag['billing_dept'] = strip_tags($tag['billing_dept']);
            $tag['billing_email'] = strip_tags($tag['billing_email']);
            $tag['billing_email_cc'] = strip_tags($tag['billing_email_cc']);
            $tag['telephone'] = strip_tags($tag['telephone']);
            $tag['street'][0] = strip_tags($tag['street'][0]);
            $tag['street'][1] = strip_tags($tag['street'][1]);
            $tag['city'] = strip_tags($tag['city']);
            $tag['region_id'] = strip_tags($tag['region_id']);
            $tag['region'] = strip_tags($tag['region']);
            $tag['postcode'] = strip_tags($tag['postcode']);
            $tag['country_id'] = strip_tags($tag['country_id']);
        }
        
        return $tag;
    }

}
