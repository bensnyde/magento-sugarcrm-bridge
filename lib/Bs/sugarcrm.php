<?php
/**
 * PHP Library for SugarCRM SOAP API
 *
 * PHP library class to interfacing with your SugarCRM instance allowing for
 * simple integration of SugarCRM functionality into your own applications.
 *
 *   http://support.sugarcrm.com/02_Documentation/04_Sugar_Developer/Sugar_Developer_Guide_6.5/02_Application_Framework/Web_Services/
 *
 * @category   Libraries
 * @package    SugarCRM
 * @author     Benton Snyder <b@bensnyde.me>
 * @link       http://www.bensnyde.me
 *
 */

class SugarCRM {

    private $clientHandle;
    private $session;

    function __construct($host, $user, $pass) {
        $this->init($host, $user, $pass);
    }

    private function init($host, $user, $pass) {
        $options = array(
            "location" => 'http://'.$host.'/service/v4_1/soap.php?wsdl',
            "uri" => 'http://'.$host.'/',
            "trace" => 1
        );

        $user = array(
            "user_name" => $user,
            "password" => md5($pass),
        );

        try {
            $this->clientHandle = new SoapClient(NULL, $options);
            echo $this->clientHandle->__getLastRequest();
            $response = $this->clientHandle->login($user);
            $this->session = $response->id;
        }
        catch(Exception $e) {
            die("SOAP login failed.");
        }
    }

    // Returns server information such as version, flavor, and gmt_time
    public function getServerInfo() {
        return $this->clientHandle->get_server_info();
    }

    // Returns the ID of the user who is logged into the current session
    public function getUserID() {
        return $this->clientHandle->get_user_id(array('session' => $this->session));
    }

    // Retrieves a single SugarBean based on ID
    public function getEntry($module_name, $id, Array $select_fields = array(), Array $link_name_to_fields = array(), $track_view = FALSE) {
        try {
            return $this->clientHandle->get_entry($this->session, $module_name, $id, $select_fields, $link_name_to_fields, $track_view);
        }
        catch(SoapFault $fault) {
            var_dump($this->clientHandle->__getLastRequest());
            var_dump($fault->getMessage());
        }
    }

    // Retrieves a list of SugarBeans based on the specified IDs
    public function getEntries($module_name, Array $ids, Array $select_fields = array(), Array $link_name_to_fields = array()) {
        return $this->clientHandle->get_entries($this->session, $module_name, $ids, $select_fields, $link_name_to_fields);
    }

    // Retrieves a list of SugarBeans
    public function getEntryList($module_name, $query = null, $order_by = null, $offset = null, Array $select_fields = array(), Array $link_name_to_fields = array(), $max_results = '0', $deleted = 0, $Favorites = FALSE) {
        return $this->clientHandle->get_entry_list($this->session, $module_name, $query, $order_by, $offset, $select_fields, $link_name_to_fields, $max_results, $deleted, $Favorites);
    }

    // Retrieves a specific relationship link for a specified record
    public function getRelationship($module_name, $module_id, $link_field_name, Array $related_fields, Array $related_module_link_name_to_fields_array = array(), $deleted=0, $order_by=null, $offset=0, $limit=0) {
        return $this->clientHandle->get_relationships($this->session, $module_name, $module_id, $linke_field_name, $related_fields, $related_module_link_name_to_fields_array, $deleted, $order_by, $offset, $limit);
    }

    // Sets a single relationship between two SugarBeans
    public function setRelationship($module_name, $module_id, $link_field_name, Array $related_ids) {
        return $this->clientHandle->set_relationship($this->session, $module_name, $module_id, $link_field_name, $related_ids);
    }

    // Sets multiple relationships between two SugarBeans
    public function setRelationships(Array $module_names, Array $module_ids, Array $link_field_names, Array $related_id) {
        return $this->clientHandle->set_relationships($this->session, $module_names, $module_ids, $link_field_names, $related_id);
    }

    // Creates or updates a SugarBean
    public function setEntry($module_name, Array $fields) {
        $name_value_list = array();
        foreach($fields as $key=>$val)
            array_push($name_value_list, array('name'=>$key, 'value'=>$val));

        //Create the Lead record
        return $this->clientHandle->set_entry($this->session, $module_name, $name_value_list, FALSE);
    }

    // Creates or updates a list of SugarBeans
    public function setEntries($module_name, Array $fields) {
        $name_value_list = array();
        foreach($fields as $key=>$val)
            array_push($name_value_list, array('name'=>$key, 'value'=>$val));

        return $this->clientHandle->set_entries($this->session, $module_name, $name_value_list);
    }

    // Add or replace a note's attachment.
    public function setNoteAttachment($filename, $file, $related_module_id = NULL, $related_module_name = NULL) {
        $note = array(
            'filename' => $filename,
            'file' => base64_encode($file),
            'related_module_id' => $related_module_id,
            'related_module_name' => $related_module_name
        );

        return $this->clientHandle->set_note_attachment($this->session, $note);
    }

    // Sets a new revision to the document
    public function setDocumentRevision($document_id, $filename, $file, $revision) {
        $document_revision = array(
            'id' => $document_id,
            'file' => base64_encode($file),
            'filename' => $filename,
            'revision' => $revision
        );

        return $this->clientHandle->set_document_revision($this->session, $document_revision);
    }

    // Allows authenticated user with appropriate permission to download a document
    public function getDocumentRevision($document_id) {
        return $this->clientHandle->get_document_revision($this->session, $document_id);
    }

    // Returns the ID, module_name and fields for the specified modules
    public function searchByModule($search_string, $modules, $offset = 0, $max_results = 100, $assigned_user_id = NULL, Array $select_fields = array(), $favorites = FALSE) {
        $validModules = array("Accounts", "Bugs", "Calls", "Cases", "Contacts", "Leads", "Opportunities", "Projects", "Project Tasks", "Quotes");
        // TO DO: Validate $modules (can be array or string)

        $this->clientHandle->search_by_module($this->session, $search_string, $modules, $offset, $max_results, $assigned_user_id, $select_fields, $favorites);
    }

    // Retrieves the list of modules available to the current user logged into the system
    public function getAvailableModules($filter = "default") {
        if($filter != "all" && $filter != "default" && $filter != "mobile")
            return false;

        return $this->clientHandle->get_available_modules($this->session, $filter);
    }

    // Retrieves the ID of the default team of the user who is logged into the current session
    public function getUserTeamID() {
        return $this->clientHandle->get_user_team_id($this->session);
    }

    // Performs a mail merge for the specified campaign
    public function setCampaignMerge(Array $targets, $campaign_id) {
        return $this->clientHandle->set_campaign_merge($this->session, $targets, $campaign_id);
    }

    // Retrieves the specified number of records in a module
    public function getEntriesCount($module_name, $query = NULL, $deleted = 0) {
        return $this->clientHandle->get_entries_count($this->session, $module_name, $query, $deleted);
    }

    // Retrieve a quote pdf in base64 encoding
    public function getQuotesPDF($quote_id, $pdf_format = 'Standard') {
        if($pdf_format != "Standard" && $pdf_format != "Invoice")
            return false;

        return $this->clientHandle->get_quotes_pdf($this->session, $quote_id, $pdf_format);
    }

    // Retrieve a report pdf in base64 encoding
    public function getReportPDF($report_id) {
        return $this->clientHandle->get_report_pdf($this->session, $report_id);
    }

    // Retrieves the layout metadata for a given module with a particular type and view
    public function getModuleLayout(Array $module_names, Array $type = array("default"), Array $view = array("list"), $acl_check = FALSE) {
        if($type != "default" && $type != "mobile")
            return false;

        $validViews = array("list", "detail", "edit", "subpanel");
        if(!in_array($view, $validViews))
            return false;

        return $this->clientHandle->get_module_layout($this->session, $module_names, $type, $view, $acl_check);
    }

    // Retrieves the md5 hash of the layout metadata for a given module with a particular type and view
    public function getModuleLayoutMD5(Array $module_names, Array $type = array("default"), Array $view = array("list"), $acl_check = FALSE) {
        if($type != "default" && $type != "mobile")
            return false;

        $validViews = array("list", "detail", "edit", "subpanel");
        if(!in_array($view, $validViews))
            return false;

        return $this->clientHandle->get_module_layout_md5($this->session, $module_names, $type, $view, $acl_check);
    }

    // Retrieves variable definitions for fields of the specified SugarBean
    public function getModuleFields($module_name, Array $fields = array()) {
        return $this->clientHandle->get_module_fields($this->session, $module_name, $fields);
    }

    // Retrieves the md5 hash of the variable definitions (vardefs) for the specified SugarBean
    public function getModuleFieldsMD5($module_name) {
        return $this->clientHandle->get_module_fields_md5($this->session, $module_name);
    }

    // Retrieve a list of recently viewed records by module
    public function getLastViewed(Array $module_names) {
        return $this->clientHandle->get_last_viewed($this->session, $module_names);
    }

    // Retrieve a list of upcoming activities including Calls, Meetings, Tasks, and Opportunities
    public function getUpcomingActivities() {
        return $this->clientHandle->get_upcoming_activities($this->session);
    }
}
